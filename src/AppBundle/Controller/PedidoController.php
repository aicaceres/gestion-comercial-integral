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
use AppBundle\Form\PedidoType;
use AppBundle\Form\PedidoDetalleType;

/**
 * @Route("/stock_pedido")
 */
class PedidoController extends Controller
{
    /**
     * @Route("/", name="stock_pedido")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido');
        $depId = $request->get('depId');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $this->get('session')->get('unidneg_id');
        $depositos = $this->getUser()->getDepositos($unidneg);   
        $central = $pordefecto = 0;
        if(count($depositos)>0){
            if(!$depId){ 
               $depId = $depositos[0]->getId();
            }          
            $deposito = $em->getRepository('AppBundle:Deposito')->find( $depId );
            $central = $deposito->getCentral();
            $pordefecto = $deposito->getPordefecto();
        }else{
             $this->addFlash('error', 'No posee depósitos asignados');
             // si es deposito central de la unidad de negocio            
        }
        $hoy = new \DateTime();        
        $inicio = date("d-m-Y",strtotime($hoy->format('d-m-Y')."- 30 days")); 
        $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
        $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y');                
        $periodo = array('desde'=>$desde, 'hasta'=>$hasta );
        
        //solicitados
        $enviados = $em->getRepository('AppBundle:Pedido')->getPedidosByCriteria('O',$depId,$periodo);
        //demandados
        $recibidos = $em->getRepository('AppBundle:Pedido')->getPedidosByCriteria('D',$depId,$periodo);
        //$recibidos = $em->getRepository('AppBundle:Pedido')->findByUnidadNegocio($this->get('session')->get('unidneg_id'));
        return $this->render('AppBundle:Pedido:index.html.twig', array(
            'periodo' => $periodo,
            'enviados' => $enviados, 'recibidos' => $recibidos, 
            'depositos'=>$depositos, 'depId' => $depId, 'central' => $central, 'pordefecto'=>$pordefecto
        ));
    }
    
    /**
     * @Route("/new", name="stock_pedido_new")
     * @Method("GET")
     * @Template("AppBundle:Pedido:new.html.twig")
     */
    public function newAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido_new');        
        $entity = new Pedido();
        $em = $this->getDoctrine()->getManager();
        // set numeracion
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo')); 
        $entity->setPrefijoNro( sprintf("%03d", $equipo->getPrefijo()) );
        $entity->setPedidoNro( sprintf("%08d", $equipo->getNroPedidoInterno()+1) );
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $entity->setUnidadNegocio($unidneg);  
        $depId = $request->get('depId');
        if($depId){
            // setear deposito origen segun combo del listado
            $depOrig = $em->getRepository('AppBundle:Deposito')->find($depId);
            $entity->setDepositoOrigen($depOrig);
        }
        // Depósito central de la unidad de negocio
        $deposito = $em->getRepository('AppBundle:Deposito')->findOneBy(array("central" => "1","pordefecto" => "1", "activo"=> true , "unidadNegocio"=> $this->get('session')->get('unidneg_id') ));            
        $entity->setDepositoDestino($deposito);
        $form   = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }    
 
    /**
    * Creates a form to create a Pedido entity.
    * @param Pedido $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Pedido $entity)
    {
        $form = $this->createForm(new PedidoType(), $entity, array(
            'action' => $this->generateUrl('stock_pedido_create'),
            'method' => 'POST', 
        ));
        return $form;
    } 
    
    /**
     * @Route("/", name="stock_pedido_create")
     * @Method("POST")
     * @Template("AppBundle:Pedido:new.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido_new');
        $entity = new Pedido();
        $em = $this->getDoctrine()->getManager();
        // set Unidad de negocio
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $entity->setUnidadNegocio($unidneg);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try{
                // set numeracion
                $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo')); 
                $entity->setPrefijoNro( sprintf("%03d", $equipo->getPrefijo()) );
                $entity->setPedidoNro( sprintf("%08d", $equipo->getNroPedidoInterno()+1) );
                /* Guardar ultimo nro */
                $equipo->setNroPedidoInterno($equipo->getNroPedidoInterno()+1);
                $em->persist($entity);
                $em->persist($equipo);
                $em->flush();    
                $em->getConnection()->commit();
                return $this->redirectToRoute('stock_pedido_edit', array('id' => $entity->getId()) );
            } catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }           
        }
        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * @Route("/{id}/edit", name="stock_pedido_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Pedido')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el pedido.');
        }
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('AppBundle:Pedido:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Pedido entity.
    * @param Pedido $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Pedido $entity)
    {
        $form = $this->createForm(new PedidoType(), $entity, array(
            'action' => $this->generateUrl('stock_pedido_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    
   
    /**
     * @Route("/{id}", name="stock_pedido_update")
     * @Method("PUT")
     * @Template("AppBundle:Pedido:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido_edit');
        $datos = $request->get('appbundle_pedido');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Pedido')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pedido entity.');
        }
        $original = new ArrayCollection();
        // Create an ArrayCollection of the current objects in the database
        foreach ($entity->getDetalles() as $item) {
            $original->add($item);
        }
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try{            
                if( isset($datos['cerrado']) ){
                    $entity->setEstado('PENDIENTE');
                    //$entity->setFechaPedido(new \DateTime);
                }
                // remove the relationship between the item and the pedido
                foreach ($original as $item) {
                    if (false === $entity->getDetalles()->contains($item)) {
                         $em->remove($item);
                    }
                } 
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('stock_pedido'));
            } catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('AppBundle:Pedido:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
   /**
     * @Route("/{id}/show", name="stock_pedido_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Pedido')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pedido entity.');
        }
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('AppBundle:Pedido:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }  
    
    
    /**
     * @Route("/delete/{id}", name="stock_pedido_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido_delete');
        //$id = $this->getRequest()->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Pedido')->find($id);
        try{
            if( $entity->getEstado()=='NUEVO' )
                 $em->remove($entity);
            else $entity->setEstado('CANCELADO');
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }

    /**
     * Creates a form to delete a Pedido entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('stock_pedido_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    /**
     * @Route("/{id}/imprimir.{_format}", 
     * defaults = { "_format" = "pdf" },
     * name="stock_pedido_print")
     * @Method("GET")
     */    
    public function printAction($id){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Pedido')->find($id);
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:Pedido:pdf-pedido.pdf.twig',
                      array('entity'=>$entity ), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);   
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition'=>'filename=pedido'.$entity->getNroPedido().'.pdf'));
      /*  return $this->render('AppBundle:Pedido:print.html.twig', array(
            'entity' => $entity));*/
    }
    
     /**
     * @Route("/listado", name="stock_pedido_listado")
     * @Method("GET")
     * @Template()
     */
    public function printPdfAction(){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_pedido');
        $em = $this->getDoctrine()->getManager();
        $listado = $em->getRepository('AppBundle:Pedido')->findByUnidadNegocio($this->get('session')->get('unidneg_id'));
        $html = $this->renderView('AppBundle:Pedido:listadoPdf.html.twig', 
                array('listado' => $listado));
        return new Response($html);
        
    }    
        
    /*public function pedidosPendientesAction(){
        $em = $this->getDoctrine()->getManager();
        $pedidos = $em->getRepository('ComprasBundle:Pedido')->getPendientes();
        $partial = $this->renderView('ComprasBundle:Pedido:_partial-pendientes.html.twig',
                    array('pedidos'=>$pedidos)); 
        return new Response($partial); 
    }*/
    
    
      /**
     * @Route("/getPedidoData", name="get_pedido_data")
     * @Method("GET")
     * @Template()
     */   
 public function getPedidoData(Request $request){
     $id = $this->getRequest()->get('id');
     $em = $this->getDoctrine()->getManager();
     $item = $em->getRepository('AppBundle:PedidoDetalle')->find($id);
     $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($item->getProducto()->getId(),$item->getPedido()->getDepositoDestino()->getId());
     $cantStock = ($stock) ? $stock->getCantidad() : 0;     
     // lotes
        $lotes = $em->getRepository('ComprasBundle:LoteProducto')->getByProductoId($item->getProducto()->getId());
        $salida = array();
        foreach ($lotes as $lote) {
           $salida[] = array('id'=>$lote->getId(),'name'=>$lote->__toString() ) ;
        }        
     $data = array('nombre'=>$item->getProducto()->getCodigoNombre(), 'productoId'=>$item->getProducto()->getId(), 
         'cantidad'=>$item->getCantidad(), 'unidmed' => $item->getProducto()->getUnidadMedida()->getNombre(),
         'bulto'=> $item->getBulto(), 'cantidadxBulto'=> $item->getCantidadxBulto(), 'stock' => $cantStock,
         'lotes'=>$salida);

     return new Response(json_encode($data));
 }
    
 
      /**
     * @Route("/solicitados", name="stock_pedidos_solicitados")
     * @Method("POST")
     * @Template()
     */
    public function pedidosSolicitadosAction(){
        $unidneg = $this->get('session')->get('unidneg_id');
        $pedidos = $this->getUser()->getPedidosSolicitados($unidneg);
        $partial = $this->renderView('AppBundle:Pedido:_partial-solicitados.html.twig',
                    array('pedidos'=>$pedidos )); 
        return new Response($partial); 
    }
      /**
     * @Route("/demandados", name="stock_pedidos_demandados")
     * @Method("POST")
     * @Template()
     */
    public function pedidosDemandadosAction(){
        $unidneg = $this->get('session')->get('unidneg_id');
        $pedidos = $this->getUser()->getPedidosDemandados($unidneg);
        $partial = $this->renderView('AppBundle:Pedido:_partial-demandados.html.twig',
                    array('pedidos'=>$pedidos)); 
        return new Response($partial); 
    }
    
    
// Modal item pedido  
    /**
     * @Route("/ajax/renderEditItemPedido/{id}", name="stock_render_edit_item_pedido")
     * @Method("GET")
     */    
    public function renderEditItemPedidoAction($id)
    {       
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:PedidoDetalle')->find($id);
        $form = $this->createForm(new PedidoDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_pedido_update', array('id' => $entity->getId())),
            'method' => 'PUT', 
        ));        
        $html = $this->renderView('AppBundle:Pedido:partial-item-pedido.html.twig', 
                array('entity' => $entity, 'form' => $form->createView() )
        );
        return new Response($html);       
    } 
    /**
     * @Route("/ajax/updateItemPedido/{id}", name="stock_item_pedido_update")
     * @Method("PUT")
     * @Template()
     */    
    public function updateItemPedido(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:PedidoDetalle')->find($id);
        $form = $this->createForm(new PedidoDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_pedido_update', array('id' => $entity->getId())),
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
        $partial = $this->renderView('AppBundle:Pedido:tr_item_pedido.html.twig',
                    array('item'=>$entity)); 
        $result = array('msg'=>$msg, 'tr'=>$partial);
        return new JsonResponse ( $result);  
    }          
    

    
    
    
    
    /**
     * @Route("/ajax/renderAddItemPedido", name="stock_render_item_pedido")
     * @Method("GET")
     */    
    public function renderAddItemPedidoAction()
    {
        $entity = new PedidoDetalle();
        $form = $this->createForm(new PedidoDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_pedido_create'),
            'method' => 'PUT', 
        ));        
        $html = $this->renderView('AppBundle:Pedido:partial-item-pedido.html.twig', 
                array('entity' => $entity, 'form' => $form->createView() )
        );
        return new Response($html);       
    }      
    /**
     * @Route("/ajax/createItemPedido", name="stock_item_pedido_create")
     * @Method("PUT")
     * @Template()
     */    
    public function createItemPedido(Request $request){
        $entity = new PedidoDetalle();
        $form = $this->createForm(new PedidoDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_pedido_create'),
            'method' => 'PUT', 
        ));
        $id = $request->get('pedidoid');
        $em = $this->getDoctrine()->getManager();
        $pedido = $em->getRepository('AppBundle:Pedido')->find($id);
        $form->handleRequest($request);
        $msg = 'No se pudo realizar esta operación.'.chr(13).' Verifique que no exista el valor que está intentando ingresar.';
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try{
                $entity->setPedido($pedido);
                $em->persist($entity);
                $em->flush();
                $msg = 'OK';
                $partial = $this->renderView('AppBundle:Pedido:tr_item_pedido.html.twig', array('item'=>$entity)); 
            } catch (\Exception $ex) {
                $msg = UtilsController::errorMessage($ex->getErrorCode());
            }
        }
        $result = array('msg'=>$msg, 'tr'=>$partial);
        return new JsonResponse ( $result);  
        /*$result = array('msg'=>$msg, 'id'=>$entity->getId(), 'prod'=>$entity->getProducto()->getCodigoNombre(), 'cant'=> $entity->getCantidadItemTxt(),
            'total'=>$entity->getCantidadItemTotal().' '.$entity->getProducto()->getUnidadMedida()->getNombre());
        return new JsonResponse ( $result); */ 
    }             
    /**
     * @Route("/ajax/deleteItemPedido", name="stock_item_pedido_delete")
     * @Method("POST")
     */    
    public function deleteItemPedidoAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('AppBundle:PedidoDetalle')->find($id);
         try{
            $em->remove($item);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {
            $msg = UtilsController::errorMessage($ex->getErrorCode());
        }
        return new Response($msg);        
    }           

}