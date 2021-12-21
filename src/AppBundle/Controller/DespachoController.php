<?php
namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;

use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\Pedido;
use AppBundle\Entity\PedidoDetalle;
use AppBundle\Entity\Despacho;
use AppBundle\Entity\DespachoDetalle;
use AppBundle\Form\DespachoType;
use AppBundle\Form\DespachoDetalleType;
use AppBundle\Form\RecepcionType;
use AppBundle\Form\RecepcionDetalleType;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;
/**
 * @Route("/stock_despacho")
 */
class DespachoController extends Controller
{
    /**
     * @Route("/", name="stock_despacho")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho');
        $depId = $request->get('depId');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $this->get('session')->get('unidneg_id');
        $depositos = $this->getUser()->getDepositos($unidneg);   
        if(count($depositos)>0){
            if(!$depId){ 
               $depId = $depositos[0]->getId();
            }          
        }else{
             $this->addFlash('error', 'No posee depósitos asignados');        
        }
        $hoy = new \DateTime();        
        $inicio = date("d-m-Y",strtotime($hoy->format('d-m-Y')."- 30 days")); 
        $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
        $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y');                
        $periodo = array('desde'=>$desde, 'hasta'=>$hasta );
        
        $entities = $em->getRepository('AppBundle:Despacho')->getDespachosByCriteria($depId,$periodo);
        return $this->render('AppBundle:Despacho:index.html.twig', array(
            'entities' => $entities,'depositos'=>$depositos, 'depId' => $depId, 'periodo' => $periodo  ));
    }
    
        
    /**
     * @Route("/new", name="stock_despacho_new")
     * @Method("GET")
     * @Template("AppBundle:Despacho:new.html.twig")
     */
    public function newAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho_new');
        $entity = new Despacho();
        $id = $request->get('id');        
        $em = $this->getDoctrine()->getManager();
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $entity->setUnidadNegocio($unidneg); 
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        $entity->setPrefijoNro( sprintf("%03d", $equipo->getPrefijo()) );
        $nroDespacho = $em->getRepository('AppBundle:Despacho')->getLastNroDespacho($equipo->getPrefijo()) + 1;
        $entity->setDespachoNro( sprintf("%06d", $nroDespacho) );

        if($id){
            $em->getConnection()->beginTransaction();
            try {
                // despacho de un pedido 
                 $pedido = $em->getRepository('AppBundle:Pedido')->find($id);
                 $entity->setDepositoOrigen( $pedido->getDepositoDestino() );
                 $entity->setDepositoDestino( $pedido->getDepositoOrigen() );
                 foreach ( $pedido->getDetalles() as $det ) {
                     $detalle = new DespachoDetalle();
                     $detalle->setProducto( $det->getProducto());
                     // cantidad en stock
                     $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($det->getProducto()->getId(), $entity->getDepositoOrigen()->getId());
                     $detalle->setStock( ($stock) ? $stock->getCantidad() : 0 );
                     $detalle->setCantidad( $det->getCantidad());
                     $detalle->setBulto( $det->getBulto());
                     $detalle->setCantidadxBulto( $det->getCantidadxBulto() );
                     $detalle->setPedidoDetalle($det);
                     $entity->addDetalle($detalle);
                 }
                 $em->persist($entity);
                 $em->flush();
                 $em->getConnection()->commit();
            } catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
            }     
            return $this->redirect($this->generateUrl('stock_despacho_edit', array('id' => $entity->getId())));            
            //$pendientes = $em->getRepository('AppBundle:Pedido')->getPendientesByDeposito( $pedido->getDepositoOrigen()->getId(), $pedido->getDepositoDestino()->getId() );            
        }else{                        
            // nuevo despacho                 
            $deposito = $em->getRepository('AppBundle:Deposito')->findOneBy(array("central" => "1","pordefecto" => "1", "unidadNegocio"=> $this->get('session')->get('unidneg_id') ));
            $entity->setDepositoOrigen($deposito);   
            $form   = $this->createCreateForm($entity);
            $formview = $form->createView();
            return $this->render('AppBundle:Despacho:new.html.twig', array(
                'entity' => $entity,
                'form'   => $formview
            ));
        }        
    }  
        
    /**
    * Creates a form to create a Despacho entity.
    * @param Despacho $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Despacho $entity)
    {
        $form = $this->createForm(new DespachoType(), $entity, array(
            'action' => $this->generateUrl('stock_despacho_create'),
            'method' => 'POST',
        ));
        return $form;
    }     
    
    /**
     * @Route("/", name="stock_despacho_create")
     * @Method("POST")
     * @Template("AppBundle:Despacho:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho_new');
        $entity = new Despacho();
        $em = $this->getDoctrine()->getManager();
        // set Unidad de negocio
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $entity->setUnidadNegocio($unidneg);

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // Nro despacho
                $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                $nroDespacho = $em->getRepository('AppBundle:Despacho')->getLastNroDespacho($equipo->getPrefijo()) + 1;
                $entity->setDespachoNro(sprintf("%06d", $nroDespacho));
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('stock_despacho_edit', array('id' => $entity->getId())));
            } catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
            }
        }
        return $this->render('AppBundle:Despacho:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/edit", name="stock_despacho_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho_new');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Despacho entity.');
        }
       // $cargados = array();   
        foreach ( $entity->getDetalles() as $det ) {     
            /*if($det->getPedidoDetalle())
                $cargados[] = $det->getPedidoDetalle()->getId();*/
            // cantidad en stock
            $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($det->getProducto()->getId(), $entity->getDepositoOrigen()->getId());
            $det->setStock( ($stock) ? $stock->getCantidad() : 0 );
        }        
        
     //   $pendientes = $em->getRepository('AppBundle:Pedido')->getPendientesByDeposito( $entity->getDepositoDestino()->getId(), $entity->getDepositoOrigen()->getId() );            
        
        $editForm = $this->createEditForm($entity);
        return $this->render('AppBundle:Despacho:edit.html.twig', array(
           // 'pendientes' => $pendientes,
           // 'cargados' => $cargados ,
            'entity' => $entity,
            'form'   => $editForm->createView(),
        ));  
    }

    /**
    * Creates a form to edit a Despacho entity.
    * @param Despacho $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Despacho $entity)
    {
        $form = $this->createForm(new DespachoType(), $entity, array(
            'action' => $this->generateUrl('stock_despacho_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    
    /**
     * @Route("/{id}", name="stock_despacho_update")
     * @Method("PUT")
     * @Template("AppBundle:Despacho:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho_new');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Despacho entity.');
        }
        
        $original = new ArrayCollection();
        // Create an ArrayCollection of the current objects in the database
        foreach ($entity->getDetalles() as $item) {
            $original->add($item);
        }
        
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        $datos = $request->get('appbundle_despacho');
        $despachoEnviado = isset($datos['despachoEnviado']);
        
        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            foreach ($original as $item) {
                if (false === $entity->getDetalles()->contains($item)) {
                     $em->remove($item);
                }
            } 
            
            try {
                $arrayPedidos = new ArrayCollection();
                // registrar el pedido como procesado
                foreach ($entity->getDetalles() as $key => $detalle) {
                    $detalle->setOrden($key);
                    if ($despachoEnviado) {
                        // detalle de pedido asociado
                        if ($detalle->getPedidoDetalle()) {
                            $detpedido = $detalle->getPedidoDetalle();
                            // marcar como entregado
                            //$detpedido->setEntregado($detpedido->getEntregado() + $detalle->getCantidadTotal());
                            //$em->persist($detpedido);
                            // marcar el pedido como DESPACHADO
                            $pedido = $detpedido->getPedido();
                            $pedido->setEstado('DESPACHADO');                            
                            $em->persist($pedido);
                            // agregar al array para generar el nuevo pedido de los items no entregados
                            if( !$arrayPedidos->contains($pedido) )
                               $arrayPedidos->add($pedido);
                        }
                    }
                }
                // registrar el despacho como DESPACHADO - descontar el stock y registrar el movimiento
                if ($despachoEnviado) {
                    $this->registrarEnStock($entity,'-');
                    $entity->setEstado('DESPACHADO');
                    //$entity->setFechaDespacho(new \DateTime);
                    // GENERAR PEDIDO NUEVO PARA LOS ITEMS QUE NO SE AGREGARON.!!!!!
                    //------------------------------------------------------
                    // usar $arrayPedidos 
                    // ver diferencia entre despachado y cantidad en pedido!!!
                    
                    $this->armarPedidoPorFaltante( $entity, $arrayPedidos);

                }
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('stock_despacho'));
            } catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                return $this->render('AppBundle:Despacho:edit.html.twig', array(
                            'pendientes' => null,
                            'entity' => $entity,
                            'form' => $editForm->createView(),
                ));
            }            
        }
    }    
    
    
    /**
     * @Route("/{id}/show", name="stock_despacho_show")
     * @Method("GET")
     * @Template()
     */    
    public function showAction($id){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException($this->notfound);
        }
                
        // ARMAR EL TEMPLATE !!!!
       // $this->addFlash('info', 'La Vista del despacho está en desarrollo');     
       // return $this->redirect($this->generateUrl('stock_despacho'));
        
        return $this->render('AppBundle:Despacho:show.html.twig', array(
            'entity' => $entity
        ));
    }       
    
    /**
     * @Route("/{id}/imprimir.{_format}", 
     * defaults = { "_format" = "pdf" },
     * name="stock_despacho_print")
     * @Method("GET")
     */    
    public function printAction($id){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:Despacho:pdf-despacho.pdf.twig',
                      array('entity'=>$entity ), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);   
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition'=>'filename=despacho'.$entity->getDespachoNro().'.pdf'));
    }    
    
     /**
     * @Route("/delete/{id}", name="stock_despacho_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho_delete');
        $id = $this->getRequest()->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);
        try{
            if( $entity->getEstado() === 'NUEVO' ){
                $em->remove($entity);
                $em->flush();
                $msg ='OK';
            }else{
                $msg = "El registro de despacho no puede ser eliminado una vez enviado.";
            }
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }   
    
    
// Modal item detalle  
    /**
     * @Route("/ajax/renderAddItemDespacho", name="stock_render_item_despacho")
     * @Method("GET")
     */    
    public function renderAddItemDespachoAction()
    {
        $entity = new DespachoDetalle();
        $form = $this->createForm(new DespachoDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_despacho_create'),
            'method' => 'PUT', 
        ));        
        $html = $this->renderView('AppBundle:Despacho:partial-item-despacho.html.twig', 
                array('entity' => $entity, 'form' => $form->createView() )
        );
        return new Response($html);       
    }          
    /**
     * @Route("/ajax/renderEditItemDespacho/{id}", name="stock_render_edit_item_despacho")
     * @Method("GET")
     */    
    public function renderEditItemDespachoAction($id)
    {
       // $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:DespachoDetalle')->find($id);
        $form = $this->createForm(new DespachoDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_despacho_update', array('id' => $entity->getId())),
            'method' => 'PUT', 
        ));        
        $html = $this->renderView('AppBundle:Despacho:partial-item-despacho.html.twig', 
                array('entity' => $entity, 'form' => $form->createView() )
        );
        return new Response($html);       
    }          
    /**
     * @Route("/ajax/createItemDespacho", name="stock_item_despacho_create")
     * @Method("PUT")
     * @Template()
     */    
    public function createItemDespacho(Request $request){
        $em = $this->getDoctrine()->getManager();
        $data = $request->get('appbundle_despachodetalle');
        $recepcion = $request->get('recepcion');
        $producto = $em->getRepository('AppBundle:Producto')->find($data['producto']);        
        $entity = new DespachoDetalle();
        $entity->setProducto($producto);
        $form = $this->createForm(new DespachoDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_despacho_create'),
            'method' => 'PUT', 
        ));
        $id = $request->get('despachoid');
        $despacho = $em->getRepository('AppBundle:Despacho')->find($id);
        $form->handleRequest($request);
       $partial = '';
        $msg = 'No se pudo realizar esta operación.'.chr(13).' Verifique que no exista el valor que está intentando ingresar.';
        if ($form->isValid()) {
            try{
                $view = 'AppBundle:Despacho:tr_item_despacho.html.twig';
                if($recepcion==1){
                    $entity->setEntregado( $entity->getCantidadTotal());
                    $entity->setCantidad( 0 );
                    $entity->setBulto( 0 );
                    $entity->setCantidadxBulto( NULL );
                    $view = 'AppBundle:Despacho:tr_item_recepcion.html.twig';
                }
                $entity->setDespacho($despacho);
                $em->persist($entity);
                $em->flush();
                $msg = 'OK';
                $partial = $this->renderView($view, array('item'=>$entity)); 
            } catch (\Exception $ex) {
                $msg = UtilsController::errorMessage($ex->getErrorCode());
            }
        }else{
           $msg = 'Dato inválido en '; 
        }        
        $result = array('msg'=>$msg, 'tr'=>$partial);
        return new JsonResponse ( $result);  
    }           

    /**
     * @Route("/ajax/updateItemDespacho/{id}", name="stock_item_despacho_update")
     * @Method("PUT")
     * @Template()
     */    
    public function updateItemDespacho(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:DespachoDetalle')->find($id);
        $form = $this->createForm(new DespachoDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_despacho_update', array('id' => $entity->getId())),
            'method' => 'PUT', 
        ));
        $form->handleRequest($request);
        $msg = 'No se pudo realizar esta operación.'.chr(13).' Intente nuevamente.';
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try{
                 $em->persist($entity);
                $em->flush();
                $msg = 'OK';
            } catch (\Exception $ex) {
                $msg = UtilsController::errorMessage($ex->getErrorCode());
            }
        }else{ $msg="Formulario inválido"; }
        $partial = $this->renderView('AppBundle:Despacho:tr_item_despacho.html.twig',
                    array('item'=>$entity)); 
        $result = array('msg'=>$msg, 'tr'=>$partial);
        return new JsonResponse ( $result);  
    }             
    
    /**
     * @Route("/ajax/renderPedidosPendientes", name="stock_render_pedidos_pendientes")
     * @Method("GET")
     */    
    public function renderPedidosPendientesAction(Request $request)
    {  
        $origen = $request->get('origen');
        $destino = $request->get('destino');
        $em = $this->getDoctrine()->getManager();
        $deposito = $em->getRepository('AppBundle:Deposito')->find($destino);
       // $porPedido = $em->getRepository('AppBundle:Pedido')->getPedidosPendientesByDeposito( $destino, $origen );          
        $porProducto = $em->getRepository('AppBundle:Pedido')->getProductosPendientesByDeposito( $destino, $origen );          
               
        $html = $this->renderView('AppBundle:Despacho:_partial-pendientes.html.twig', 
                array('porproducto'=>$porProducto, 'deposito' => $deposito )
        );
        return new Response($html);       
    }       
    /**
     * @Route("/ajax/addItemsFromPedidos", name="stock_item_despacho_addpedidos")
     * @Method("POST")
     * @Template()
     */       
    public function addItemsFromPedidos(Request $request){
        $despachoId = $request->get('despachoid');
        $em = $this->getDoctrine()->getManager();
        $despacho = $em->getRepository('AppBundle:Despacho')->find($despachoId);
        $items = $request->get('items');
        $em->getConnection()->beginTransaction();
        $msg = 'No se pudo realizar esta operación.'.chr(13).' Intente nuevamente.';
        $partial = '';
        try {
            foreach ($items as $item) {                
                $itemPedido = $em->getRepository('AppBundle:PedidoDetalle')->find($item);
                $entity = new DespachoDetalle();
                $entity->setProducto($itemPedido->getProducto());
                $entity->setBulto($itemPedido->getBulto());
                $entity->setCantidad($itemPedido->getCantidad());
                $entity->setCantidadxBulto($itemPedido->getCantidadxBulto());
                $entity->setDespacho($despacho);
                $entity->setPedidoDetalle($itemPedido);
                $em->persist($entity);
                $em->flush();
                $tr = $this->renderView('AppBundle:Despacho:tr_item_despacho.html.twig',
                    array('item'=>$entity));
                $partial = $partial.$tr;
            }
            $em->getConnection()->commit();
            $msg = 'OK';
        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $msg = UtilsController::errorMessage($ex->getErrorCode());
        }

       /* $partial = $this->renderView('AppBundle:Despacho:tr_item_despacho.html.twig',
                    array('item'=>$entity)); */
       // $partial = null;
        $result = array('msg'=>$msg, 'tr'=>$partial);
        return new JsonResponse ( $result);  
    }     
    
    
    /**
     * @Route("/ajax/deleteItemDespacho", name="stock_item_despacho_delete")
     * @Method("POST")
     */    
    public function deleteItemDespachoAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('AppBundle:DespachoDetalle')->find($id);
         try{
            $em->remove($item);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {
            $msg = UtilsController::errorMessage($ex->getErrorCode());
        }
        return new Response($msg);        
    }     
    
    
    
    
    /**
     * GENERAR NUEVO PEDIDO POR FALTANTE
     */
    private function armarPedidoPorFaltante($despacho, $pedidos){
        $cx = $this->getDoctrine()->getManager();
        $equipo = $cx->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo')); 
        $nuevo = new Pedido();
        $nuevo->setPrefijoNro( sprintf("%03d", $equipo->getPrefijo()) );
        $nuevo->setPedidoNro( sprintf("%08d", $equipo->getNroPedidoInterno()+1) );
        $equipo->setNroPedidoInterno($equipo->getNroPedidoInterno()+1);
        $cx->persist($equipo);
               
        $unidneg = $cx->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $nuevo->setUnidadNegocio($unidneg);  
        
        $nuevo->setDepositoDestino($despacho->getDepositoOrigen());
        $nuevo->setDepositoOrigen($despacho->getDepositoDestino());
        $nuevo->setEstado('PENDIENTE');
        $nuevo->setFechaPedido( new \DateTime() );        
        foreach ($pedidos as $ped){
            foreach($ped->getDetalles() as $det){
                if( $det->getDespachoDetalle() ){
                    $dif = $det->getCantidadTotal() - $det->getDespachoDetalle()->getCantidadTotal();
                }else{
                    $dif = $det->getCantidadTotal();
                }                
                if( $dif > 0 ){                        
                        // hay faltante.. generar item
                        $nvodet = new PedidoDetalle();
                        $nvodet->setProducto( $det->getProducto() );
                        $nvodet->setBulto(0);
                        $nvodet->setCantidadxBulto(NULL);
                        $nvodet->setCantidad( $dif );
                        $nuevo->addDetalle($nvodet);
                    }                  
            }
        }
        if( count($nuevo->getDetalles())>0 ){
            $cx->persist($nuevo);
            $cx->flush();
        }
    }

    

    /**
     * REGISTRAR EN STOCK - CARGA O DESCARGA
     */
    
    public function registrarEnStock($despacho,$signo='-'){
       $em = $this->getDoctrine()->getManager();        
      try{  
        if($signo=='-'){
           $fecha = $despacho->getFechaDespacho();
           $deposito = $despacho->getDepositoOrigen();
        }else{
           $fecha = $despacho->getFechaEntrega();
           $deposito = $despacho->getDepositoDestino();
        }
        foreach ($despacho->getDetalles() as $detalle) {
           $producto = $em->getRepository('AppBundle:Producto')->find($detalle->getProducto()->getId());
            // calcular cantidad en unidades y costo en unidades si es por bulto
           if($signo==='-'){ $cantidad = $detalle->getCantidadTotal();  }                          
           else            { $cantidad = $detalle->getEntregado();  }
           
            // Descontar al stock
            $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(),$deposito->getId());
            // VERIFICAR BULTO
                if( $stock ){
                    if($signo==='-'){ $stock->setCantidad( $stock->getCantidad() - $cantidad ); }                          
                    else            { $stock->setCantidad( $stock->getCantidad() + $cantidad ); }
                }else{
                    $stock = new Stock();
                    $stock->setProducto($producto);
                    $stock->setDeposito($deposito);
                    $stock->setCantidad($cantidad);
                }
                $stock->setCosto(0);
                $em->persist($stock);
                // Cargar movimiento
                $movim = new StockMovimiento();
                $movim->setTipo('Despacho');
                $movim->setSigno($signo);
                $movim->setMovimiento($despacho->getId());
                $movim->setProducto($producto);
                $movim->setCantidad($cantidad);
                $movim->setFecha( $fecha );
                $movim->setDeposito($deposito);                
                $em->persist($movim);
                $em->flush();
        }
       } catch (\Exception $ex) {  
            $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
       } 
    }    
    
    
    /** ??????????????? 
    
    public function reimprimirDespachoAction($id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);
        $detalle = $em->getRepository('AppBundle:Despacho')->getResumenDespacho($id);
        $pedidosId = $em->getRepository('AppBundle:Despacho')->getPedidosDespacho($id);
        $pedidos = new ArrayCollection();
        foreach ($pedidosId as $pedId) {
            $pedido = $em->getRepository('AppBundle:Pedido')->find($pedId);
            $pedidos->add($pedido);
        }
        return $this->render('AppBundle:Despacho:print.html.twig', array(
            'entity' => $entity, 'detalle' => $detalle, 'pedidos'=>$pedidos));
        
    }
**/
/*    public function datosItemDespachoAction(){
        $id = $this->getRequest()->get('id');
        $cant = $this->getRequest()->get('cant');
        $em = $this->getDoctrine()->getManager();
        $itemPedido = $em->getRepository('AppBundle:PedidoDetalle')->find($id);
        $itemDespacho = new DespachoDetalle();
        $itemDespacho->setProducto( $itemPedido->getProducto() );
        $itemDespacho->setCantidad( $cant );
        $itemDespacho->setPedidoDetalle($itemPedido);
        $partial = $this->renderView('AppBundle:Despacho:itemRow.html.twig',
                    array('item'=>$itemDespacho)); 
        return new Response($partial);  
    }
 */   
    
    
    /**
     * RECEPCION DE LA MERCADERIA DESPACHADA
     */
    
     /**
     * @Route("/recepcion", name="stock_despacho_recepcion")
     * @Method("GET")
     * @Template()
     */
    public function recepcionDespachoAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho_recepcion_new');
        $depId = $request->get('depId');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $this->get('session')->get('unidneg_id');
        $depositos = $this->getUser()->getDepositos($unidneg);   
        if(count($depositos)>0){
            if(!$depId){ 
               $depId = $depositos[0]->getId();
            }          
        }else{
             $this->addFlash('error', 'No posee depósitos asignados');        
        }        
        $hoy = new \DateTime();        
        $inicio = date("d-m-Y",strtotime($hoy->format('d-m-Y')."- 30 days")); 
        $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
        $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y');                
        $periodo = array('desde'=>$desde, 'hasta'=>$hasta );
        
        $entities = $em->getRepository('AppBundle:Despacho')->getRecepcionesByCriteria($depId,$periodo);
        return $this->render('AppBundle:Despacho:recepcion-index.html.twig', array(
            'entities' => $entities,'depositos'=>$depositos, 'depId' => $depId, 'periodo' => $periodo  ));        
    }    
    
    /**
     * @Route("/recepcion/new/{id}", name="stock_despacho_recepcion_new")
     * @Method("GET")
     * @Template("AppBundle:Despacho:recepcion-new.html.twig")
     */
    public function recepcionDespachoNewAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho_recepcion_new');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pedido entity.');
        }
        foreach ($entity->getDetalles() as $detalle) {
            if( is_null($detalle->getEntregado() ) ){
                $detalle->setOrden( $detalle->getId());
                $detalle->setEntregado( $detalle->getCantidadTotal() );   
                $em->persist($detalle);
                $em->flush();
            }
        }                
        $editForm = $this->createRecepcionForm($entity);
        
        $aux = $editForm->createView();
       
        return $this->render('AppBundle:Despacho:recepcion-new.html.twig', array(
            'entity' => $entity,
            'form'   => $aux
        ));        
    }     
    
    /**
    * Creates a form to edit a Pedido entity.
    * @param Pedido $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createRecepcionForm(Despacho $entity)
    {
        $form = $this->createForm(new RecepcionType(), $entity, array(
            'action' => $this->generateUrl('stock_despacho_recepcion_create', array('id' => $entity->getId())),
            'method' => 'POST',
        ));
        return $form;
    }        
    
    /**
     * @Route("/{id}/recepcion", name="stock_despacho_recepcion_create")
     * @Method("POST")
     * @Template("AppBundle:Despacho:recepcion-new.html.twig")
     */
    public function recepcionCreateAction(Request $request,$id)
    {   
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_despacho_recepcion_new');
        $em = $this->getDoctrine()->getManager();
        $datos = $request->get('appbundle_recepcion');
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);        
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el pedido.');
        }
        if ($entity->getEstado()=='ENTREGADO') {
            throw $this->createNotFoundException('Este pedido ya fue recepcionado');
        }
        $despachoRecibido = isset($datos['despachoRecibido']);
        $em->getConnection()->beginTransaction();
        try {
            $fecha = new \DateTime($datos['fechaEntrega']);
            $entity->setFechaEntrega( $fecha  );
            $entity->setObservRecepcion( $datos['observRecepcion'] );
            if($despachoRecibido){
                // recepcion de mercaderías
                $entity->setEstado('ENTREGADO');
                $signo = '+'; 
                $fecha = $entity->getFechaEntrega();
                $deposito = $entity->getDepositoDestino();
                foreach ($entity->getDetalles() as $detalle) {                    
                    if ($detalle->getPedidoDetalle()) {
                        $detpedido = $detalle->getPedidoDetalle();
                        // marcar el pedido como entregado
                        $pedido = $detpedido->getPedido();
                        $pedido->setEstado('ENTREGADO');
                        $pedido->setFechaEntrega($fecha);
                        $em->persist($pedido);
                    }
                    $producto = $detalle->getProducto();
                    $cantidad = $detalle->getEntregado();
                    // Descontar al stock
                    $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(),$deposito->getId());
                    if( $stock ){
                        $stock->setCantidad( $stock->getCantidad() + $cantidad );
                    }else{
                        $stock = new Stock();
                        $stock->setProducto($producto);
                        $stock->setDeposito($deposito);
                        $stock->setCantidad($cantidad);
                    }
                    $stock->setCosto(0);
                    $em->persist($stock);
                    // Cargar movimiento
                    $movim = new StockMovimiento();
                    $movim->setTipo('Despacho');
                    $movim->setSigno($signo);
                    $movim->setMovimiento($entity->getId());
                    $movim->setProducto($producto);
                    $movim->setCantidad($cantidad);
                    $movim->setFecha( $fecha );
                    $movim->setDeposito($deposito);                
                    $em->persist($movim);
                    $em->flush();                    
                }
                                
            }
            $em->persist($entity);                  
            $em->flush();                           
            
            $em->getConnection()->commit();            
            return $this->redirect($this->generateUrl('stock_despacho_recepcion'));
        } catch (\Exception $ex) {            
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('error', $ex->getTraceAsString());
            $editForm = $this->createRecepcionForm($entity);
            return $this->render('AppBundle:Despacho:recepcion-new.html.twig', array(
                'entity'      => $entity,
                'form'   => $editForm->createView()
            )); 
           
        }                   
    }         
    
    /**
     * @Route("/recepcion/show/{id}", name="stock_despacho_recepcion_show")
     * @Method("GET")
     * @Template("AppBundle:Despacho:recepcion-show.html.twig")
     */
    public function recepcionDespachoShowAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Despacho')->find($id);
        return $this->render('AppBundle:Despacho:show.html.twig', array(
            'entity' => $entity
        ));
    }  
    
// MODAL PARA RECEPCION
    /**
     * @Route("/ajax/setItemDespachoNoEntregado", name="stock_item_despacho_noentregado")
     * @Method("POST")
     */    
    public function setItemDespachoNoEntregadoAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('AppBundle:DespachoDetalle')->find($id);
         try{
            $item->setEntregado(0);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {
            $msg = UtilsController::errorMessage($ex->getErrorCode());
        }
        return new Response($msg);        
    }      
    /**
     * @Route("/ajax/renderEditItemRecepcion/{id}", name="stock_render_edit_item_recepcion")
     * @Method("GET")
     */    
    public function renderEditItemRecepcionAction($id)
    {
       // $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:DespachoDetalle')->find($id);
        $form = $this->createForm(new RecepcionDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_recepcion_update', array('id' => $entity->getId())),
            'method' => 'PUT', 
        ));        
        $html = $this->renderView('AppBundle:Despacho:partial-item-recepcion.html.twig', 
                array('entity' => $entity, 'form' => $form->createView() )
        );
        return new Response($html);       
    }             
    /**
     * @Route("/ajax/updateItemRecepcion/{id}", name="stock_item_recepcion_update")
     * @Method("PUT")
     * @Template()
     */    
    public function updateItemRecepcion(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:DespachoDetalle')->find($id);
        $form = $this->createForm(new RecepcionDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_recepcion_update', array('id' => $entity->getId())),
            'method' => 'PUT', 
        ));
        $cant_original = array('cantidad'=>$entity->getCantidad(), 'bulto'=>$entity->getBulto(), 'cantxbulto'=>$entity->getCantidadxBulto());
        $form->handleRequest($request);
        $msg = 'No se pudo realizar esta operación.'.chr(13).' Intente nuevamente.';
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try{
                $entity->setEntregado( $entity->getCantidadTotal());
                $entity->setCantidad( $cant_original['cantidad'] );
                $entity->setBulto( $cant_original['bulto'] );
                $entity->setCantidadxBulto( $cant_original['cantxbulto'] );
                
                 $em->persist($entity);
                $em->flush();
                $msg = 'OK';
            } catch (\Exception $ex) {
                $msg = UtilsController::errorMessage($ex->getErrorCode());
            }
        }else{ $msg="Formulario inválido"; }
        $partial = $this->renderView('AppBundle:Despacho:tr_item_recepcion.html.twig',
                    array('item'=>$entity)); 
        $result = array('msg'=>$msg, 'tr'=>$partial);
        return new JsonResponse ( $result);  
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

// REGISTRAR EN STOCK       
/*$signo = '+';        
if($signo=='-'){
    $fecha = $entity->getFechaDespacho();
    $deposito = $entity->getDepositoOrigen();
 }else{
    $fecha = $entity->getFechaEntrega();
    $deposito = $entity->getDepositoDestino();
 }
 foreach ($entity->getDetalles() as $detalle) {
    $producto = $detalle->getProducto();
     // calcular cantidad en unidades y costo en unidades si es por bulto
    if($signo==='-'){ $cantidad = $detalle->getCantidadTotal();  }                          
    else            { $cantidad = $detalle->getEntregado();  }

     // Descontar al stock
     $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(),$deposito->getId());
     // VERIFICAR BULTO
         if( $stock ){
             if($signo==='-'){ $stock->setCantidad( $stock->getCantidad() - $cantidad ); }                          
             else            { $stock->setCantidad( $stock->getCantidad() + $cantidad ); }
         }else{
             $stock = new Stock();
             $stock->setProducto($producto);
             $stock->setDeposito($deposito);
             $stock->setCantidad($cantidad);
         }
         $stock->setCosto(0);
         $em->persist($stock);
         // Cargar movimiento
         $movim = new StockMovimiento();
         $movim->setTipo('Despacho');
         $movim->setSigno($signo);
         $movim->setMovimiento($entity->getId());
         $movim->setProducto($producto);
         $movim->setCantidad($cantidad);
         $movim->setFecha( $fecha );
         $movim->setDeposito($deposito);                
         $em->persist($movim);
         $em->flush();
 }  */              
                    
    
}