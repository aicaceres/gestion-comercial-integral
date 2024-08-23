<?php

namespace ComprasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use ComprasBundle\Entity\NotaDebCred;
use ComprasBundle\Form\NotaDebCredType;
//use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;

/**
 * @Route("/notadebcred")
 */
class NotaDebCredController extends Controller {

    /**
     * @Route("/", name="compras_notadebcred")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $provId = $request->get('provId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(array('activo' => 1), array('nombre' => 'ASC'));
        $entities = $em->getRepository('ComprasBundle:NotaDebCred')->findByCriteria($unidneg, $provId, $desde, $hasta);
        return $this->render('ComprasBundle:NotaDebCred:index.html.twig', array(
                'entities' => $entities,
                'proveedores' => $proveedores,
                'provId' => $provId,
                'desde' => $desde,
                'hasta' => $hasta
        ));
    }

    /**
     * @Route("/new", name="compras_notadebcred_new")
     * @Method("GET")
     * @Template("ComprasBundle:NotaDebCred:edit.html.twig")
     */
    public function newAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_notadebcred');
        $entity = new NotaDebCred();
        $em = $this->getDoctrine()->getManager();
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
        $entity->setNotaDebCredNro(sprintf("%08d", $equipo->getNroNotaDebCredCompra() + 1));
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $entity->setUnidadNegocio($unidneg);
        $form = $this->createCreateForm($entity);
        return $this->render('ComprasBundle:NotaDebCred:edit.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a NotaCredito entity.
     * @param NotaCredito $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(NotaDebCred $entity) {
        $form = $this->createForm(new NotaDebCredType(), $entity, array(
            'action' => $this->generateUrl('compras_notadebcred_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/", name="compras_notadebcred_create")
     * @Method("POST")
     * @Template("ComprasBundle:NotaDebCred:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_notadebcred');
        // credito - (descuenta de la deuda con proveedor)
        // debito + (aumenta la deuda con el proveedor)
        $entity = new NotaDebCred();
        $data = $request->get('comprasbundle_notadebcred');
        $form = $this->createCreateForm($entity);
        //$modificaStock = ($request->get('modificaStock') == 'SI') ? true : false;
        $form->handleRequest($request);
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                // set numeracion
                $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                $entity->setNotaDebCredNro(sprintf("%08d", $equipo->getNroNotaDebCredCompra() + 1));
                /* Guardar ultimo nro */
                $equipo->setNroNotaDebCredCompra($equipo->getNroNotaDebCredCompra() + 1);

                // cargar productos y verificar que no haya items sin producto.
                $productos = $request->get('comprasbundle_producto');
                foreach ($entity->getDetalles() as $key => $detalle) {
                    $producto = $em->getRepository('AppBundle:Producto')->find($productos[$key]);
                    if ($producto) {
                        $detalle->setProducto($producto);
                    }
                    else {
                        $entity->removeDetalle($detalle);
                    }
                }

                // set Unidad de negocio
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
                $entity->setUnidadNegocio($unidneg);

                $existe = $em->getRepository('ComprasBundle:NotaDebCred')->isDuplicado($entity);
                if ($existe) {
                    $this->addFlash('error', 'Ya existe este nro de comprobante para este proveedor!');
                    $em->getConnection()->rollback();
                    return $this->render('ComprasBundle:NotaDebCred:edit.html.twig', array(
                            'entity' => $entity,
                            'form' => $form->createView(),
                    ));
                }
                if (intval($entity->getAfipPuntoVenta()) == 0 || intval($entity->getAfipNroComprobante()) == 0) {
                    $this->addFlash('error', 'Debe ingresar punto de venta y número de comprobante!');
                    $em->getConnection()->rollback();
                    return $this->render('ComprasBundle:NotaDebCred:edit.html.twig', array(
                            'entity' => $entity,
                            'form' => $form->createView(),
                    ));
                }

                $entity->setSaldo($entity->getTotal());
                $entity->setEstado('PENDIENTE');
                if ($entity->getSigno() == '-') {
                    //NOTA DE CREDITO
                    $saldoNC = $entity->getSaldo();

                    // saldar facturas asociadas..
                    if (count($entity->getFacturas()) > 0) {
                        foreach ($entity->getFacturas() as $fact) {
                            if ($saldoNC > 0) {

                                if ($saldoNC >= $fact->getSaldo()) {
                                    //alcanza para cubrir el saldo
                                    $saldoNC = round($saldoNC - $fact->getSaldo(), 2);
                                    $fact->setSaldo(0);
                                    $fact->setEstado('PAGADO');
                                }
                                else {
                                    //no alcanza, impacta el total
                                    $fact->setSaldo(round(($fact->getSaldo() - $saldoNC), 2));
                                    $saldoNC = 0;
                                    $saldoFc = ( $fact->getSaldo() > 0 ) ? 'PAGO PARCIAL' : 'PAGADO';
                                    $fact->setEstado($saldoFc);
                                }
                                $em->persist($fact);
                            }
                            else {
                                break;
                            }
                        }

                        $entity->setSaldo($saldoNC);
                        $entity->setEstado(($saldoNC > 0) ? 'PENDIENTE' : 'ACREDITADO' );
                    }
                }
                $em->persist($entity);
                $em->persist($equipo);
                $em->flush();
                //registrar devolucion
                if ($entity->getModificaStock()) {
                    $this->registrarDevolucion($entity);
                }
                $em->getConnection()->commit();
                return $this->redirectToRoute('compras_notadebcred');
            }
            catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }

//$this->addFlash('error', ' not valid!');
        $errors = array();

        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = (String) $form[$child->getName()]->getErrors();
                }
            }
        }
//        var_dump($entity->getNroComprobante());
//        var_dump($errors);
//        die;
        return $this->render('ComprasBundle:NotaDebCred:edit.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/show", name="compras_notadebcred_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota de Débito/Crédito.');
        }
        return $this->render('ComprasBundle:NotaDebCred:show.html.twig', array(
                'entity' => $entity));
    }

    /**
     * @Route("/{id}/edit", name="compras_notadebcred_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:NotaDebCred')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota.');
        }
        if (!$entity->getAfipPuntoVenta()) {
            if (strpos($entity->getNroComprobante(), '-') === false) {
                $entity->setAfipNroComprobante($entity->getNroComprobante());
            }
            else {
                $data = split('-', $entity->getNroComprobante());
                $entity->setAfipPuntoVenta(str_pad($data[0], 5, "0", STR_PAD_LEFT));
                $entity->setAfipNroComprobante($data[1]);
            }
        }
        if (!$entity->getAfipComprobante()) {
            $pre = ($entity->getSigno() == '+' ) ? 'DEB-' : 'CRE-';
            $afipComp = $em->getRepository('ConfigBundle:AfipComprobante')->getIdByTipo($pre . $entity->getTipoNota());
            $entity->setAfipComprobante($afipComp);
        }
        $editForm = $this->createEditForm($entity);
        // if($entity->getEstado()=='NUEVO')
        //     $this->get('session')->getFlashBag()->add('alert','Los productos serán reingresados al stock cuando se marque el envío al Proveedor' );
        return $this->render('ComprasBundle:NotaDebCred:edit.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a NotaCredito entity.
     * @param NotaCredito $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(NotaDebCred $entity) {
        $form = $this->createForm(new NotaDebCredType(), $entity, array(
            'action' => $this->generateUrl('compras_notadebcred_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="compras_notadebcred_update")
     * @Method("PUT")
     * @Template("ComprasBundle:NotaDebCred:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_notadebcred');
        //$modificaStock = ($request->get('modificaStock') == 'SI') ? true : false;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota.');
        }
        $original = new ArrayCollection();
        // Create an ArrayCollection of the current objects in the database
        foreach ($entity->getDetalles() as $item) {
            $original->add($item);
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // remove the relationship between the item and the pedido
                foreach ($original as $item) {
                    if (false === $entity->getDetalles()->contains($item)) {
                        $em->remove($item);
                    }
                }
                $em->flush();


                $em->getConnection()->commit();
            }
            catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('ComprasBundle:NotaCredito:edit.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView(),
        ));
    }

    /**
     * @Route("/deleteAjax/{id}", name="compras_notadebcred_delete_ajax")
     * @Method("POST")
     */
    public function deleteAjaxAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_notadebcred');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:NotaDebCred')->find($id);
        try {
            // si es debito y ya tuvo pagos ver que hacer con los pagos
            if ($entity->getSigno() == '-') {
                if (COUNT($entity->getFacturas()) > 0) {
                    // corregir saldos de facturas
                    foreach ($entity->getFacturas() as $fact) {
                        $nvosaldo = $fact->getSaldo() + $entity->getTotal();
                        $fact->setSaldo($nvosaldo);
                        if ($fact->getTotal() == $fact->getSaldo()) {
                            $fact->setEstado('PENDIENTE');
                        }
                        $em->persist($fact);
                        $em->flush();
                    }
                }
                if ($entity->getModificaStock()) {
                    // si modifico stock volver a reingresar
                    /*        if ($entity->getModificaStock()) {
                      $this->registrarDevolucion($entity,'+');
                      } */
                }
            }
            $em->remove($entity);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Exception $ex) {
            $msg = $ex->getTraceAsString();
        }
        return new Response(json_encode($msg));
    }

    /**
     *  Reingreso al stock y registro de movimiento
     */
    private function registrarDevolucion($nota, $signo = '-') {
        $em = $this->getDoctrine()->getManager();
        $deposito = $em->getRepository('AppBundle:Deposito')->findOneBy(array("central" => "1", "pordefecto" => "1", "unidadNegocio" => $this->get('session')->get('unidneg_id')));
        foreach ($nota->getDetalles() as $item) {
            $producto = $item->getProducto();

            $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(), $deposito->getId());
            if ($stock) {
                $cantidad = ( $signo == '-' ) ?
                    ($stock->getCantidad() - $item->getCantidadTotal()) :
                    ($stock->getCantidad() + $item->getCantidadTotal());
                $stock->setCantidad($cantidad);
                $em->persist($stock);
            }
            // Cargar movimiento
            $movim = new StockMovimiento();
            $movim->setFecha($nota->getFecha());
            $movim->setTipo('COMPRAS_NOTADEBCRED');
            $movim->setSigno($signo);
            $movim->setMovimiento($nota->getId());
            $movim->setProducto($producto);
            $movim->setCantidad($item->getCantidad());
            $movim->setDeposito($deposito);
            $em->persist($movim);
            $em->flush();
        }
        return true;
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printComprasNotaDebCred.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_compras_notadebcred")
     * @Method("POST")
     */
    public function printComprasNotaDebCredAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $proveedorId = $request->get('proveedorid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $textoFiltro = array($proveedor ? $proveedor->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('ComprasBundle:NotaDebCred:pdf-notadebcred.pdf.twig',
            array('items' => json_decode($items), 'filtro' => $textoFiltro,
                'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_compras_notadebcred' . $hoy->format('dmY_Hi') . '.pdf'));
    }

}