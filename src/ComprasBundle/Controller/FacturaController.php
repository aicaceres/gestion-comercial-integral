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
use ComprasBundle\Entity\Factura;
use ComprasBundle\Entity\FacturaDetalle;
use ComprasBundle\Form\FacturaType;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;
use ComprasBundle\Entity\PagoProveedor;
use VentasBundle\Entity\CobroDetalle;
/**
 * @Route("/factura")
 */
class FacturaController extends Controller {

    /**
     * @Route("/", name="compras_factura")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_factura');
        $em = $this->getDoctrine()->getManager();
        $provId = $request->get('provId');
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(array('activo' => 1), array('nombre' => 'ASC'));
        $entities = $em->getRepository('ComprasBundle:Factura')->findByCriteria($unidneg, $provId, $periodo['ini'], $periodo['fin']);
        return $this->render('ComprasBundle:Factura:index.html.twig', array(
                    'entities' => $entities,
                    'proveedores' => $proveedores,
                    'provId' => $provId,
                    'desde' => $periodo['ini'],
                    'hasta' => $periodo['fin']
        ));
    }

    /**
     * @Route("/new", name="compras_factura_new")
     * @Method("GET")
     * @Template("ComprasBundle:Factura:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura_new');
        $entity = new Factura();
        $entity->setTipoFactura('A');
        $em = $this->getDoctrine()->getManager();
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
        $entity->setFacturaNro(sprintf("%08d", $equipo->getNroFacturaCompra() + 1));
        $centros = $em->getRepository('ConfigBundle:CentroCosto')->findByActivo(true);
        $form = $this->createCreateForm($entity);
        return $this->render('ComprasBundle:Factura:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'centros' => $centros
        ));
    }

    /**
     * Creates a form to create a Factura entity.
     * @param Factura $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Factura $entity) {
        $form = $this->createForm(new FacturaType(), $entity, array(
            'action' => $this->generateUrl('compras_factura_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/", name="compras_factura_create")
     * @Method("POST")
     * @Template("ComprasBundle:Factura:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura_new');
        $entity = new Factura();
        $data = $request->get('comprasbundle_factura');
        $em = $this->getDoctrine()->getManager();
        $centros = $em->getRepository('ConfigBundle:CentroCosto')->findByActivo(true);
        $form = $this->createCreateForm($entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $entity->setEstado('PENDIENTE');
                // $entity->setTotal( $entity->getMontoTotal() );
                $entity->setSaldo($entity->getTotal());
                // set Unidad de negocio
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
                $entity->setUnidadNegocio($unidneg);
                $existe = $em->getRepository('ComprasBundle:Factura')->isDuplicado($unidneg->getId(), $entity);
                if ($existe) {
                    $this->addFlash('error', 'Ya existe este nro de comprobante para este proveedor!');
                    $em->getConnection()->rollback();
                    return $this->render('ComprasBundle:Factura:edit.html.twig', array(
                                'entity' => $entity,
                                'centros' => $centros,
                                'form' => $form->createView(),
                    ));
                }
                if (intval($entity->getAfipPuntoVenta()) == 0 || intval($entity->getAfipNroComprobante()) == 0) {
                    $this->addFlash('error', 'Debe ingresar punto de venta y número de comprobante!');
                    $em->getConnection()->rollback();
                    return $this->render('ComprasBundle:Factura:edit.html.twig', array(
                                'entity' => $entity,
                                'centros' => $centros,
                                'form' => $form->createView(),
                    ));
                }

                // set numeracion
                $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                $entity->setFacturaNro(sprintf("%08d", $equipo->getNroFacturaCompra() + 1));
                /* Guardar ultimo nro */
                $equipo->setNroFacturaCompra($equipo->getNroFacturaCompra() + 1);
                $em->persist($entity);
                $em->persist($equipo);
                $em->flush();
                if ($data['pedidoId']) {
                    $pedido = $em->getRepository('ComprasBundle:Pedido')->find($data['pedidoId']);
                    $pedido->setEstado('FACTURADO');
                    $em->persist($pedido);
                    $em->flush();
                    $this->actualizarCostoyStock($em, $entity, false, true);
                }
                else {
                    $actcostos = $request->get('actualizarCosto') === 'SI' ? true : false;
                    $actstock = $entity->getModificaStock();
                    if ($actcostos || $actstock) {
                        $this->actualizarCostoyStock($em, $entity, $actstock, $actcostos);
                    }
                }

                // REGISTRAR EL PAGO DE CONTADO
                if (isset($data['pagadoContado'])) {
                    $res = $this->registrarPagoContado($em, $entity);
                    if(!$res){
                        throw $this->createNotFoundException('No se pudo registrar el pago.');
                    }
                    $entity->setSaldo(0);
                    $entity->setEstado('PAGADO');
                    $em->persist($entity);
                    $em->flush();
                }

                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('compras_factura'));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('ComprasBundle:Factura:edit.html.twig', array(
                    'entity' => $entity,
                    'centros' => $centros,
                    'form' => $form->createView(),
        ));
    }

    public function actualizarCostoyStock($em, $factura, $actstock, $actcostos) {
        // $em = $this->getDoctrine()->getManager();
        $deposito = $em->getRepository('AppBundle:Deposito')->findOneBy(array("central" => "1", "pordefecto" => "1", "unidadNegocio" => $this->get('session')->get('unidneg_id')));
        foreach ($factura->getDetalles() as $detalle) {
            $producto = $em->getRepository('AppBundle:Producto')->find($detalle->getProducto()->getId());
            // calcular cantidad en unidades y costo en unidades si es por bulto
            $cantidad = $detalle->getCantidad();
            $costo = $detalle->getPrecio();
            if ($detalle->getBulto()) {
                $cantidad = $detalle->getCantidad() * $detalle->getCantidadxBulto();
                $costo = $detalle->getPrecio() / $detalle->getCantidadxBulto();
            }
            if ($actcostos) {
                $producto->setCosto($costo);
                $em->persist($producto);
                $em->flush();
            }
            if ($actstock) {
                // Ingresar al stock
                $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(), $deposito->getId());
                // VERIFICAR BULTO
                if ($stock) {
                    $stock->setCantidad($stock->getCantidad() + $cantidad);
                }
                else {
                    $stock = new Stock();
                    $stock->setProducto($producto);
                    $stock->setDeposito($deposito);
                    $stock->setCantidad($cantidad);
                }
                $em->persist($stock);
                // Cargar movimiento
                $movim = new StockMovimiento();
                $movim->setFecha($factura->getFechaFactura());
                $movim->setTipo('compras_factura');
                $movim->setSigno('+');
                $movim->setMovimiento($factura->getId());
                $movim->setProducto($producto);
                $movim->setCantidad($cantidad);
                $movim->setDeposito($deposito);
                $em->persist($movim);
                $em->flush();
            }
        }
    }

    /**
     * @Route("/{id}/show", name="compras_factura_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Factura')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Factura entity.');
        }
        $arrayPagos = array();
        $pagos = $em->getRepository('ComprasBundle:PagoProveedor')->getPagosByFactura($entity->getProveedor()->getId(), $id);
        foreach ($pagos as $pago) {
            $concepto = json_decode($pago['concepto']);
            foreach ($concepto as $item) {
                if ($item->clave == 'FAC-' . $id) {
                    array_push($arrayPagos, array(
                        'id' => $pago['id'],
                        'tipo' => 'PAGO',
                        'fecha' => $pago['fecha'],
                        'comprobante' => $pago['nroComprobante'],
                        'monto' => $item->monto
                    ));
                }
            }
        }
        $proveedor = $entity->getProveedor();
        foreach ($proveedor->getNotasDebCred() as $nota) {
            foreach ($nota->getFacturas() as $nf) {
                if ($nf->getId() == $entity->getId()) {
                    array_push($arrayPagos, array(
                        'id' => $nota->getId(),
                        'tipo' => 'NC',
                        'fecha' => $nota->getFecha(),
                        'comprobante' => $nota->getNroNotaDebCred(),
                        'monto' => $nota->getTotal()
                    ));
                }
            }
        }

        return $this->render('ComprasBundle:Factura:show.html.twig', array(
                    'entity' => $entity, 'pagos' => $arrayPagos));
    }

    /**
     * @Route("/{id}/edit", name="compras_factura_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Factura')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Factura entity.');
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
            $afipComp = $em->getRepository('ConfigBundle:AfipComprobante')->getIdByTipo('FAC-' . $entity->getTipoFactura());
            $entity->setAfipComprobante($afipComp);
        }
        $editForm = $this->createEditForm($entity);
        $centros = $em->getRepository('ConfigBundle:CentroCosto')->findByActivo(true);
        return $this->render('ComprasBundle:Factura:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'centros' => $centros
        ));
    }

    /**
     * Creates a form to edit a Factura entity.
     *
     * @param Factura $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Factura $entity) {
        $form = $this->createForm(new FacturaType(), $entity, array(
            'action' => $this->generateUrl('compras_factura_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="compras_factura_update")
     * @Method("PUT")
     * @Template("AppBundle:Factura:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Factura')->find($id);
        $data = $request->get('comprasbundle_factura');
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Factura entity.');
        }
        $original = new ArrayCollection();
        // Create an ArrayCollection of the current objects in the database
        foreach ($entity->getDetalles() as $item) {
            $original->add($item);
        }
        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            foreach ($original as $item) {
                if (false === $entity->getDetalles()->contains($item)) {
                    $em->remove($item);
                }
            }
            // $entity->setTotal( $entity->getMontoTotal() );
            // REGISTRAR EL PAGO DE CONTADO
            if (isset($data['pagadoContado'])) {
                $res = $this->registrarPagoContado($em, $entity);
                    if(!$res){
                        throw $this->createNotFoundException('No se pudo registrar el pago.');
                    }
                $entity->setSaldo(0);
                $entity->setEstado('PAGADO');
            }
            else {
                $entity->setSaldo($entity->getTotal());
            }
            $em->flush();

            return $this->redirect($this->generateUrl('compras_factura'));
        }
        $centros = $em->getRepository('ConfigBundle:CentroCosto')->findByActivo(true);
        return $this->render('ComprasBundle:Factura:edit.html.twig', array(
                    'entity' => $entity,
                    'centros' => $centros,
                    'form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Factura entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura_delete');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ComprasBundle:Factura')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Factura entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('factura'));
    }

    /**
     * Creates a form to delete a Factura entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('factura_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    /**
     * @Route("/{id}/facturar", name="compras_factura_facturarpedido")
     * @Method("GET")
     * @Template()
     */
    public function facturarPedidoAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura_new');
        $em = $this->getDoctrine()->getManager();
        $pedido = $em->getRepository('ComprasBundle:Pedido')->find($id);
        $centros = $em->getRepository('ConfigBundle:CentroCosto')->findByActivo(true);
        $factura = new Factura();
        $factura->setTipoFactura('A');
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        $factura->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
        $factura->setFacturaNro(sprintf("%08d", $equipo->getNroFacturaCompra() + 1));
        $factura->setProveedor($pedido->getProveedor());
        foreach ($pedido->getDetalles() as $detalle) {
            if ($detalle->getEntregado() > 0) {
                $item = new FacturaDetalle();
                if ($detalle->getBulto() && $detalle->getCantidadxBulto()) {
                    $cant = $detalle->getEntregado() / $detalle->getCantidadxBulto();
                }
                else {
                    $cant = $detalle->getEntregado();
                }
                $item->setCantidad(number_format((float) ($cant), 3, '.', ''));
                $item->setBulto($detalle->getBulto());
                $item->setCantidadxBulto($detalle->getCantidadxBulto());
                $item->setProducto($detalle->getProducto());
                $item->setPrecio($detalle->getPrecio());
                $factura->addDetalle($item);
            }
        }
        $form = $this->createCreateForm($factura);
        $form->get('pedidoId')->setData($id);
        return $this->render('ComprasBundle:Factura:edit.html.twig', array(
                    //'pedidoId' => $pedido->getId(),
                    'entity' => $factura,
                    'centros' => $centros,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/cancelar", name="compras_factura_cancel")
     * @Method("GET")
     * @Template()
     */
    public function cancelarFacturaAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        try {
            $em->getConnection()->beginTransaction();
            $entity = $em->getRepository('ComprasBundle:Factura')->find($id);
            $entity->setEstado('CANCELADO');
            $em->persist($entity);
            $em->flush();
            if( $entity->getModificaStock()){
                // Reingresar al stock
                $deposito = $em->getRepository('AppBundle:Deposito')->findOneBy(array("central" => "1", "pordefecto" => "1", "unidadNegocio" => $this->get('session')->get('unidneg_id')));
                foreach ($entity->getDetalles() as $detalle) {
                    $producto = $detalle->getProducto();
                    $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(), $deposito->getId());
                    if ($stock) {
                        $stock->setCantidad($stock->getCantidad() - $detalle->getCantidad());
                    }else {
                        throw $this->createNotFoundException('Unable to find Stock entity.');
                    }
                    $em->persist($stock);
                    // Cargar movimiento
                    $movim = new StockMovimiento();
                    $movim->setFecha($entity->getFechaFactura());
                    $movim->setTipo('compras_factura');
                    $movim->setSigno('-');
                    $movim->setMovimiento($entity->getId());
                    $movim->setProducto($producto);
                    $movim->setCantidad($detalle->getCantidad());
                    $movim->setDeposito($deposito);
                    $em->persist($movim);
                    $em->flush();
                }
            }

            $em->getConnection()->commit();
            return new Response('OK');
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            return new Response($ex);
        }
    }


    public function getFormaPagoProveedorAction() {
        $id = $this->getRequest()->get('id');
        $em = $this->getDoctrine()->getManager();
        $proveedor = $em->getRepository('AdminBundle:Proveedor')->find($id);
        return new Response($proveedor->getCondicionVenta()->getId());
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printComprasfactura.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_compras_factura")
     * @Method("POST")
     */
    public function printComprasFacturaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $tipo = $request->get('tipo');
        $proveedorId = $request->get('proveedorid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $textoFiltro = array($proveedor ? $proveedor->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('ComprasBundle:Factura:pdf-facturas.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro, 'tipo' => $tipo,
                    'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_compras_facturas_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /**
     * @Route("/informe/comprados", name="compras_informe_comprado")
     * @Method("GET")
     * @Template()
     */
    public function compradosAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_informe_comprado');
        $em = $this->getDoctrine()->getManager();
        $provId = $request->get('provId');
        $prodId = $request->get('prodId');
        $hoy = new \DateTime();
        $inicio = date("d-m-Y", strtotime($hoy->format('d-m-Y') . "- 30 days"));
        $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
        $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y');
        $periodo = array('desde' => $desde, 'hasta' => $hasta);
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(array('activo'=>'1'),array('nombre'=>'ASC'));
        $productos = $em->getRepository('AppBundle:Producto')->findBy(array(),array('nombre'=>'ASC'));
        /* if (!$provId) {
          $provId = $proveedores[0]->getId();
          } */
        $entities = $em->getRepository('ComprasBundle:Factura')->findCompradoByCriteria($unidneg, $provId, $prodId, $periodo);

        return $this->render('ComprasBundle:Informe:comprados.html.twig', array(
                    'entities' => $entities,
                    'proveedores' => $proveedores,
                    'productos' => $productos,
                    'provId' => $provId,
                    'prodId' => $prodId,
                    'desde' => $desde,
                    'hasta' => $hasta
        ));
    }

    /**
     * @Route("/informe/centrocosto", name="compras_informe_centrocosto")
     * @Method("GET")
     * @Template()
     */
    public function centroCostoAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_informe_centrocosto');
        $em = $this->getDoctrine()->getManager();
        $ccId = $request->get('ccId');
        $hoy = new \DateTime();
        $inicio = date("d-m-Y", strtotime($hoy->format('d-m-Y') . "- 30 days"));
        $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
        $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y');
        $periodo = array('desde' => $desde, 'hasta' => $hasta);
        $centros = $em->getRepository('ConfigBundle:CentroCosto')->findAll();
        $entities = $em->getRepository('ComprasBundle:Factura')->detalleCentroCostoByCriteria($ccId, $periodo);

        return $this->render('ComprasBundle:Informe:centrocosto.html.twig', array(
                    'entities' => $entities,
                    'centros' => $centros,
                    'ccId' => $ccId,
                    'desde' => $desde,
                    'hasta' => $hasta
        ));
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printInformeComprado.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_informe_comprado")
     * @Method("POST")
     */
    public function printInformeCompradoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $tipo = $request->get('tipo');
        $proveedorId = $request->get('proveedorid');
        $productoId = $request->get('productoid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $producto = $em->getRepository('AppBundle:Producto')->find($productoId);
        $textoFiltro = array($proveedor ? $proveedor->getNombre() : 'TODOS', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '',
            $producto ? $producto->getCodigoNombre() : 'TODOS');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('ComprasBundle:Informe:pdf-comprados.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro, 'tipo' => $tipo,
                    'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=informe_productos_comprados_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    private function registrarPagoContado($em, $factura) {
        try {
            $pago = new PagoProveedor();
            $pago->setFecha(new \DateTime());
            $pago->setProveedor($factura->getProveedor());
            $pago->setImporte($factura->getTotal());
            $pago->setNroComprobante($factura->getNuevoNroComprobante());
            $concepto = array();
            array_push($concepto, array('clave' => 'FAC-' . $factura->getId(), 'monto' => $factura->getTotal()));
            $pago->setConcepto(json_encode($concepto));
            $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
            $pago->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
            $pago->setPagoNro(sprintf("%06d", $equipo->getNroPagoCompra() + 1));
            /* Guardar ultimo nro */
            $equipo->setNroPagoCompra($equipo->getNroPagoCompra() + 1);
            $em->persist($equipo);
            $em->persist($pago);
            // cobroDetalle
            $cobro = new CobroDetalle();
            $cobro->setTipoPago('EFECTIVO');
            $cobro->setImporte( $pago->getImporte() );
            $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneByCodigoAfip('PES');
            $cobro->setMoneda($moneda);
            $apertura = $em->getRepository('VentasBundle:CajaApertura')->findOneBy(array('caja'=>1,'fechaCierre'=>null));
            if( !$apertura ){
                $this->addFlash('error', 'La caja está cerrada. Debe realizar la apertura para registrar pagos');
                return false;
            }
            $cobro->setCajaApertura($apertura);
            $cobro->setPagoProveedor( $pago );
            $em->persist($cobro);
            $em->flush();
            return true;
        }
        catch (\Exception $ex) {
            return $ex;
        }

    }

}