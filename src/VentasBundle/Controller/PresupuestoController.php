<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Controller\MonedaController;
use ConfigBundle\Controller\FormaPagoController;
use VentasBundle\Entity\Presupuesto;
use VentasBundle\Entity\PresupuestoDetalle;
use VentasBundle\Form\PresupuestoType;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;

/**
 * @Route("/presupuestoVentas")
 */
class PresupuestoController extends Controller {

    /**
     * @Route("/", name="ventas_presupuesto")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_venta');
        $em = $this->getDoctrine()->getManager();
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        // $desde = $request->get('desde');
        // $hasta = $request->get('hasta');
        $printpdf = null;
        $cliId = $request->get('cliId');
        $cliente = null;
        if ($cliId) {
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($cliId);
        }
        $entities = $em->getRepository('VentasBundle:Presupuesto')->findByCriteria($unidneg, $cliId, $periodo['ini'], $periodo['fin']);
        if ($this->getUser()->getAccess($unidneg, 'ventas_presupuesto_print')) {
            $printpdf = $request->get('printpdf');
        }
        return $this->render('VentasBundle:Presupuesto:index.html.twig', array(
                'entities' => $entities,
                'cliId' => $cliId,
                'cliente' => $cliente,
                'desde' => $periodo['ini'],
                'hasta' => $periodo['fin'],
                'printpdf' => $printpdf
        ));
    }

    /**
     * @Route("/new", name="ventas_presupuesto_new")
     * @Method("GET")
     * @Template("VentasBundle:Presupuesto:new.html.twig")
     */
    public function newAction(Request $request) {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta');

        $em = $this->getDoctrine()->getManager();
        $referer = $request->headers->get('referer');

        $entity = new Presupuesto();

        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if ($param) {
            // cargar datos parametrizados por defecto
            $entity->setNroPresupuesto($param->getUltimoNroPresupuesto() + 1);

            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            $entity->setCliente($cliente);
            $entity->setCategoriaIva($cliente->getCondicionIva()->getCodigo());
            $entity->setPercepcionRentas($cliente->getPercepcionRentas() ? $cliente->getPercepcionRentas() : 0);

            $entity->setFormaPago($cliente->getFormaPago());
            $entity->setDescuentoRecargo($cliente->getFormaPago()->getPorcentajeRecargo());
            $entity->setPrecioLista($cliente->getPrecioLista());
            $entity->setValidez($param->getValidezPresupuesto());
        }
        else {
            $this->addFlash('error', 'No se ha podido acceder a la parametrización. Intente nuevamente o contacte a servicio técnico.');
            return $this->redirect($referer);
        }

        $form = $this->createCreateForm($entity);
        return $this->render(
                'VentasBundle:Presupuesto:new.html.twig',
                $this->arrayParameters($entity, $form->createView())
        );
    }

    /**
     * Creates a form to create a Presupuesto entity.
     * @param Presupuesto $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Presupuesto $entity) {
        $form = $this->createForm(new PresupuestoType(), $entity, array(
            'action' => $this->generateUrl('ventas_presupuesto_create'),
            'method' => 'POST',
//            'attr' => array('type' => $type),
        ));
        return $form;
    }

    /**
     * @Route("/", name="ventas_presupuesto_create")
     * @Method("POST")
     * @Template("VentasBundle:Presupuesto:new.html.twig")
     */
    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta');

        $entity = new Presupuesto();
        $entity->setFechaPresupuesto(new \DateTime());
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // cargar referencias faltantes
                $cliente = $em->getRepository('VentasBundle:Cliente')->find($request->get('ventasbundle_cliente'));
                $entity->setCliente($cliente);
                $entity->setNombreCliente($request->get('ventasbundle_nombreCliente'));
                if(!$entity->getCategoriaIva()){
                    $entity->setCategoriaIva($cliente->getCondicionIva()->getCodigo());
                }
                $formapago = $em->getRepository('ConfigBundle:FormaPago')->find($request->get('select_formapago'));
                $entity->setFormaPago($formapago);

                $entity->setEstado('EMITIDO');
                // set Unidad de negocio
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($unidneg_id);
                $entity->setUnidadNegocio($unidneg);
                // set numeracion
                $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
                if ($param) {
                    // cargar datos parametrizados por defecto
                    $entity->setNroPresupuesto($param->getUltimoNroPresupuesto() + 1);
                    $param->setUltimoNroPresupuesto($entity->getNroPresupuesto());
                    $em->persist($param);
                }
                // cargar productos y verificar que no haya items sin producto.
                $productos = $request->get('ventasbundle_producto');
                foreach ($entity->getDetalles() as $key => $detalle) {
                    $producto = $em->getRepository('AppBundle:Producto')->find($productos[$key]);

                    if ($producto) {
                        $detalle->setProducto($producto);
                    }
                    else {
                        $entity->removeDetalle($detalle);
                    }
                }
                $em->persist($entity);
                $em->flush();
                if ($entity->getDescuentaStock()) {
                    // Descuento de stock
                    $deposito = $entity->getDeposito();
                    foreach ($entity->getDetalles() as $detalle) {
                        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                        if ($stock) {
                            $stock->setCantidad($stock->getCantidad() - $detalle->getCantidad());
                        }
                        else {
                            $stock = new Stock();
                            $stock->setProducto($detalle->getProducto());
                            $stock->setDeposito($deposito);
                            $stock->setCantidad(0 - $detalle->getCantidad());
                        }
                        $em->persist($stock);

                        // Cargar movimiento
                        $movim = new StockMovimiento();
                        $movim->setFecha($entity->getFechaPresupuesto());
                        $movim->setTipo('ventas_presupuesto');
                        $movim->setSigno('-');
                        $movim->setMovimiento($entity->getId());
                        $movim->setProducto($detalle->getProducto());
                        $movim->setCantidad($detalle->getCantidad());
                        $movim->setDeposito($deposito);
                        $em->persist($movim);
                        $em->flush();
                    }
                }

                $em->getConnection()->commit();

                // return $this->redirect($this->generateUrl('ventas_presupuesto', array('printpdf' => $entity->getId())));
                return $this->redirect($this->generateUrl('ventas_presupuesto'));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }

        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            }
            else {
                $errors[] = $error->getMessage();
            }
        }
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
        var_dump($errors);
        echo 'invalid';
        die;
        return $this->render('VentasBundle:Presupuesto:new.html.twig',
                $this->arrayParameters($entity, $form->createView()));
    }

    protected function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();
        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = (String) $form[$child->getName()]->getErrors();
                }
            }
        }
        return $errors;
    }

    /**
     * @Route("/{id}/edit", name="ventas_presupuesto_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_venta_new');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Presupuesto')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Presupuesto.');
        }
        if ($entity->getEstado() == 'IMPRESO') {
            $this->addFlash('error', 'El presupuesto ya no puede ser editado una vez impreso.');
            return $this->redirectToRoute('ventas_presupuesto');
        }
        $editForm = $this->createEditForm($entity, 'new');
        return $this->render('VentasBundle:Presupuesto:new.html.twig',
                $this->arrayParameters($entity, $editForm->createView()));
    }

    /**
     * Creates a form to edit a Presupuesto entity.
     * @param Presupuesto $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Presupuesto $entity, $type) {
        $form = $this->createForm(new PresupuestoType(), $entity, array(
            'action' => $this->generateUrl('ventas_presupuesto_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr' => array('type' => $type),
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="ventas_presupuesto_update")
     * @Method("PUT")
     * @Template("VentasBundle:Presupuesto:new.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_venta_new');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Presupuesto')->find($id);
        $descuentaStock = $entity->getDescuentaStock();
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra Presupuesto.');
        }
        $editForm = $this->createEditForm($entity, 'create');
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {

                // cargar referencias faltantes
                $cliente = $em->getRepository('VentasBundle:Cliente')->find($request->get('ventasbundle_cliente'));
                $entity->setCliente($cliente);
                $entity->setNombreCliente($request->get('ventasbundle_nombreCliente'));
                $formapago = $em->getRepository('ConfigBundle:FormaPago')->find($request->get('select_formapago'));
                $entity->setFormaPago($formapago);

                // actualizar los productos
                $productos = $request->get('ventasbundle_producto');
                $i = 0;
                foreach ($entity->getDetalles() as $detalle) {
                    $producto = $em->getRepository('AppBundle:Producto')->find($productos[$i]);
                    $detalle->setProducto($producto);
                    $i++;
                }

                $em->persist($entity);
                $em->flush();

                if (!$descuentaStock && $entity->getDescuentaStock()) {
                    // Descuento de stock
                    $deposito = $entity->getDeposito();
                    foreach ($entity->getDetalles() as $detalle) {
                        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                        if ($stock) {
                            $stock->setCantidad($stock->getCantidad() - $detalle->getCantidad());
                        }
                        else {
                            $stock = new Stock();
                            $stock->setProducto($detalle->getProducto());
                            $stock->setDeposito($deposito);
                            $stock->setCantidad(0 - $detalle->getCantidad());
                        }
                        $em->persist($stock);
                        // Cargar movimiento
                        $movim = new StockMovimiento();
                        $movim->setFecha(new \DateTime());
                        $movim->setTipo('ventas_presupuesto');
                        $movim->setSigno('-');
                        $movim->setMovimiento($entity->getId());
                        $movim->setProducto($detalle->getProducto());
                        $movim->setCantidad($detalle->getCantidad());
                        $movim->setDeposito($deposito);
                        $em->persist($movim);
                        $em->flush();
                    }
                }

                $em->getConnection()->commit();
                $this->addFlash('success', 'Los datos fueron modificados con éxito!');
                return $this->redirect($this->generateUrl('ventas_presupuesto'));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        $errors = array();
        if ($editForm->count() > 0) {
            foreach ($editForm->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = (String) $editForm[$child->getName()]->getErrors();
                }
            }
        }
        return $this->render('VentasBundle:Presupuesto:new.html.twig',
                $this->arrayParameters($entity, $editForm->createView()));
    }

    /**
     * @Route("/{id}/show", name="ventas_presupuesto_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_venta');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Presupuesto')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Presupuesto entity.');
        }
        return $this->render('VentasBundle:Presupuesto:show.html.twig', array(
                'entity' => $entity));
    }

    /**
     * @Route("/{id}/printPresupuesto.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="ventas_presupuesto_print")
     * @Method("GET")
     */
    public function printPresupuestoAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_presupuesto_print');
        $em = $this->getDoctrine()->getManager();
        $presupuesto = $em->getRepository('VentasBundle:Presupuesto')->find($id);
        $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);

        $logo = __DIR__ . '/../../../web/assets/images/logo_comprobante.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        if ($request->get('valorizado')) {
            $this->render('VentasBundle:Presupuesto:presupuesto-valorizado-detalle.pdf.twig',
                array('presupuesto' => $presupuesto, 'empresa' => $empresa, 'logo' => $logo), $response);
        }
        else {
            $this->render('VentasBundle:Presupuesto:presupuesto.pdf.twig',
                array('presupuesto' => $presupuesto, 'empresa' => $empresa, 'logo' => $logo), $response);
        }

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();

        $presupuesto->setEstado('IMPRESO');
        $em->persist($presupuesto);
        $em->flush();

        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=presupuesto_' . $presupuesto->getNroPresupuesto() . '_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /**
     * @Route("/anularPresupuesto", name="ventas_presupuesto_anular")
     * @Method("GET")
     */
    public function anularPresupuestoAction(Request $request) {
        $id = $request->get('id');
        $em = $this->get('doctrine')->getEntityManager();
        $entity = $em->getRepository('VentasBundle:Presupuesto')->find($id);
        try {
            $em->getConnection()->beginTransaction();
            $entity->setEstado('ANULADO');
            $em->persist($entity);
            $em->flush();
            if ($entity->getDescuentaStock()) {
                // Revertir descuento de stock
                $deposito = $entity->getDeposito();
                foreach ($entity->getDetalles() as $detalle) {
                    $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                    if ($stock) {
                        $stock->setCantidad($stock->getCantidad() + $detalle->getCantidad());
                    }
                    else {
                        $stock = new Stock();
                        $stock->setProducto($detalle->getProducto());
                        $stock->setDeposito($deposito);
                        $stock->setCantidad($detalle->getCantidad());
                        //$em->getConnection()->rollback();
                        //$this->addFlash('error', 'No se ha podido anular el presupuesto');
                        //return $this->redirect($this->generateUrl('ventas_presupuesto_show', array('id' => $entity->getId())));
                    }
                    $em->persist($stock);

                    // Cargar movimiento
                    $movim = new StockMovimiento();
                    $movim->setFecha($entity->getUpdated());
                    $movim->setTipo('ventas_presupuesto');
                    $movim->setSigno('+');
                    $movim->setMovimiento($entity->getId());
                    $movim->setProducto($detalle->getProducto());
                    $movim->setCantidad($detalle->getCantidad());
                    $movim->setDeposito($deposito);
                    $em->persist($movim);
                    $em->flush();
                }
            }

            $em->getConnection()->commit();
            $this->addFlash('success', 'Se ha anulado el Presupuesto #' . $entity->getNroPresupuesto());
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $this->addFlash('error', 'No se ha podido anular el presupuesto. ' . $ex->getMessage());
            return $this->redirect($this->generateUrl('ventas_presupuesto_show', array('id' => $entity->getId())));
        }
        return $this->redirect($this->generateUrl('ventas_presupuesto'));
    }

    /**
     * @Route("/{id}/repeat", name="ventas_presupuesto_repeat")
     * @Method("GET")
     * @Template()
     */
    public function repeatAction($id) {
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta_new');
        $em = $this->getDoctrine()->getManager();
        $presupuesto = $em->getRepository('VentasBundle:Presupuesto')->find($id);
        if (!$presupuesto) {
            throw $this->createNotFoundException('No se encuentra el presupuesto.');
        }

        $entity = clone $presupuesto;
        $entity->setFechaPresupuesto(new \DateTime());
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if ($param) {
            // cargar datos parametrizados por defecto
            $entity->setNroPresupuesto($param->getUltimoNroPresupuesto() + 1);
        }
        $entity->setEstado('EMITIDO');
        // actualizar los precios
        foreach ($entity->getDetalles() as $det) {
            $det->setPrecio($det->getProducto()->getPrecioByLista($entity->getPrecioLista()->getId()));
        }
        $form = $this->createCreateForm($entity, 'new');

        return $this->render('VentasBundle:Presupuesto:new.html.twig', $this->arrayParameters($entity, $form->createView()));
    }

    /**
     * @Route("/fromVenta/{id}", name="ventas_presupuesto_venta")
     * @Method("GET")
     * @Template()
     */
    public function fromVentaAction($id) {
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta');
        $em = $this->getDoctrine()->getManager();
        $venta = $em->getRepository('VentasBundle:Venta')->find($id);
        if (!$venta) {
            throw $this->createNotFoundException('No se encuentra la Venta.');
        }
        $entity = new Presupuesto();
        $entity->setFechaPresupuesto(new \DateTime());
        // setear datos del presupuesto
        $cliente = $venta->getCliente();
        $entity->setCliente($cliente);
        $entity->setNombreCliente($venta->getNombreCliente());
        $entity->setTipo('P');
        $entity->setDeposito($venta->getDeposito());
        $entity->setPrecioLista($venta->getPrecioLista());
        $entity->setFormaPago($venta->getFormaPago());
        $entity->setDescuentoRecargo($venta->getDescuentoRecargo());
        $entity->setCategoriaIva($cliente->getCondicionIva()->getCodigo());
        $entity->setPercepcionRentas($cliente->getPercepcionRentas() ? $cliente->getPercepcionRentas() : 0);

        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if ($param) {
            // ultimo nro de operacion
            $entity->setNroPresupuesto($param->getUltimoNroPresupuesto() + 1);
        }

        // cargar detalle
        foreach ($venta->getDetalles() as $det) {
            $new = new PresupuestoDetalle();
            $producto = $det->getProducto();
            $new->setProducto($producto);
            $new->setCantidad($det->getCantidad());
            $new->setTextoComodin($det->getTextoComodin());
            $precio = $producto->getPrecioByLista($venta->getPrecioLista()->getId());
            $new->setPrecio($precio);
            $new->setAlicuota($producto->getIva());
            $entity->addDetalle($new);
        }

        $form = $this->createCreateForm($entity);

        return $this->render('VentasBundle:Presupuesto:new.html.twig',
                $this->arrayParameters($entity, $form->createView())
        );
    }

    private function arrayParameters($entity, $view) {
        $em = $this->getDoctrine()->getManager();
        return array(
            'entity' => $entity,
            'moneda' => MonedaController::getMonedaByDefault($em),
            'descuentoContado' => FormaPagoController::getDescuentoContado($em),
            'form' => $view,
        );
    }

}