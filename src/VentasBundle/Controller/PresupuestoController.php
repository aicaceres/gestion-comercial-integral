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
use VentasBundle\Entity\Presupuesto;
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
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $printpdf = null;
        $cliId = $request->get('cliId');
        $cliente = null;
        if($cliId){
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($cliId);
        }
        $entities = $em->getRepository('VentasBundle:Presupuesto')->findByCriteria($unidneg, $cliId, $desde, $hasta);
        if( $this->getUser()->getAccess($unidneg, 'ventas_presupuesto_print') ){
            $printpdf = $request->get('printpdf');
        }
        return $this->render('VentasBundle:Presupuesto:index.html.twig', array(
                    'entities' => $entities,
                    'cliId' => $cliId,
                    'cliente' => $cliente,
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'printpdf' => $printpdf
        ));
    }

    /**
     * @Route("/new", name="ventas_presupuesto_new")
     * @Method("GET")
     * @Template("VentasBundle:Presupuesto:new.html.twig")
     */
    public function newAction() {
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta');
        $em = $this->getDoctrine()->getManager();
        $entity = new Presupuesto();
        $entity->setFechaPresupuesto( new \DateTime() );

        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if($param){
            // cargar datos parametrizados por defecto
            $entity->setNroPresupuesto( $param->getUltimoNroPresupuesto() + 1 );
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            $entity->setCliente($cliente);
            $entity->setFormaPago( $cliente->getFormaPago() );
            $entity->setDescuentoRecargo( $cliente->getFormaPago()->getPorcentajeRecargo() );
            $entity->setPrecioLista( $cliente->getPrecioLista() );
            $entity->setValidez( $param->getValidezPresupuesto());
        }

        $form = $this->createCreateForm($entity,'new');
        return $this->render('VentasBundle:Presupuesto:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Presupuesto entity.
     * @param Presupuesto $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Presupuesto $entity,$type) {
        $form = $this->createForm(new PresupuestoType(), $entity, array(
            'action' => $this->generateUrl('ventas_presupuesto_create'),
            'method' => 'POST',
            'attr' => array('type'=>$type ) ,
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
        $form = $this->createCreateForm($entity,'create');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $entity->setEstado('EMITIDO');
                // set Unidad de negocio
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($unidneg_id);
                $entity->setUnidadNegocio($unidneg);
                // set numeracion
                $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
                if($param){
                    // cargar datos parametrizados por defecto
                    $entity->setNroPresupuesto( $param->getUltimoNroPresupuesto() + 1 );
                    $param->setUltimoNroPresupuesto( $entity->getNroPresupuesto() );
                    $em->persist($param);
                }
                $em->persist($entity);
                $em->flush();
                if( $entity->getDescuentaStock() ){
                    // Descuento de stock
                    $deposito = $entity->getDeposito();
                    foreach ($entity->getDetalles() as $detalle){
                        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                        if ($stock) {
                            $stock->setCantidad($stock->getCantidad() - $detalle->getCantidad());
                        }else {
                            $stock = new Stock();
                            $stock->setProducto($detalle->getProducto());
                            $stock->setDeposito($deposito);
                            $stock->setCantidad( 0 - $detalle->getCantidad());
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
                return $this->redirect($this->generateUrl('ventas_presupuesto', array('printpdf' => $entity->getId())));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('VentasBundle:Presupuesto:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
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
        $editForm = $this->createEditForm($entity,'new');

        return $this->render('VentasBundle:Presupuesto:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
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
            'attr' => array('type'=>$type) ,
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
        $editForm = $this->createEditForm($entity,'create');
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try{

                $em->flush();

                if( !$descuentaStock && $entity->getDescuentaStock() ){
                    // Descuento de stock
                    $deposito = $entity->getDeposito();
                    foreach ($entity->getDetalles() as $detalle){
                        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                        if ($stock) {
                            $stock->setCantidad($stock->getCantidad() - $detalle->getCantidad());
                        }else {
                            $stock = new Stock();
                            $stock->setProducto($detalle->getProducto());
                            $stock->setDeposito($deposito);
                            $stock->setCantidad( 0 - $detalle->getCantidad());
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

        return $this->render('VentasBundle:Presupuesto:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
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
    public function printPresupuestoAction(Request $request,$id){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_presupuesto_print');
        $em = $this->getDoctrine()->getManager();
        $presupuesto = $em->getRepository('VentasBundle:Presupuesto')->find($id);
        $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);

        $logo = __DIR__.'/../../../web/assets/images/logo_comprobante.png';
    //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('VentasBundle:Presupuesto:presupuesto.pdf.twig',
                array( 'presupuesto' => $presupuesto, 'empresa'=>$empresa, 'logo' => $logo ), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition'=>'filename=presupuesto_'.$presupuesto->getNroPresupuesto().'_'.$hoy->format('dmY_Hi').'.pdf'));
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
            if( $entity->getDescuentaStock() ){
                // Revertir descuento de stock
                $deposito = $entity->getDeposito();
                foreach ($entity->getDetalles() as $detalle){
                    $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                    if ($stock) {
                        $stock->setCantidad($stock->getCantidad() + $detalle->getCantidad());
                    }else{
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
            $this->addFlash('success', 'Se ha anulado el Presupuesto #'.$entity->getNroPresupuesto());

        }catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $this->addFlash('error', 'No se ha podido anular el presupuesto. '.$ex->getMessage());
            return $this->redirect($this->generateUrl('ventas_presupuesto_show', array('id' => $entity->getId())));
        }
        return $this->redirect($this->generateUrl('ventas_presupuesto'));
    }


}