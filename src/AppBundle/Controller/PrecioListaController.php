<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\PrecioLista;
use AppBundle\Form\PrecioListaType;
use AppBundle\Entity\Precio;
use AppBundle\Form\PrecioType;
use AppBundle\Entity\PrecioActualizacion;
use AppBundle\Form\PrecioActualizacionType;

/**
 * @Route("/precio")
 */
class PrecioListaController extends Controller
{
    /**
     * @Route("/lista", name="stock_precio_lista")
     * @Method("GET")
     * @Template("AppBundle:Precio:lista-index.html.twig")
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_lista');
        $em = $this->getDoctrine()->getManager();      
        $entities = $em->getRepository('AppBundle:PrecioLista')->findAll();
        return $this->render('AppBundle:Precio:lista-index.html.twig', array(
            'entities' => $entities,
        ));
    }    
    
    /**
     * @Route("/lista/new", name="stock_precio_lista_new")
     * @Method("GET")
     * @Template("AppBundle:Precio:lista-edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_lista');
        $entity = new PrecioLista();
        $form   = $this->createCreateForm($entity);
        $em = $this->getDoctrine()->getManager();
        $listas = $em->getRepository('AppBundle:PrecioLista')->findAll();
        return $this->render('AppBundle:Precio:lista-edit.html.twig', array(
            'entity' => $entity,
            'listas' => $listas,
            'form'   => $form->createView(),
        ));
    }
    
    /**
    * Creates a form to create a Producto entity.
    * @param Producto $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(PrecioLista $entity)
    {
        $form = $this->createForm(new PrecioListaType(), $entity, array(
            'action' => $this->generateUrl('stock_precio_lista_create'),
            'method' => 'POST',
        ));
        return $form;
    }    
    
    /**
     * @Route("/lista", name="stock_precio_lista_create")
     * @Method("POST")
     * @Template("AppBundle:Precio:lista-edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_lista');
        $entity = new PrecioLista();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $listaOrigen = $request->get('lista_origen');
            if($listaOrigen){
                $precios = $em->getRepository('AppBundle:Precio')->findByPrecioLista($listaOrigen);
                foreach ($precios as $precio) {
                    $new = clone $precio;
                    $entity->addPrecio($new);
                }
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('stock_precio_lista'));
        }
        $listas = $em->getRepository('AppBundle:PrecioLista')->findAll();
        return $this->render('AppBundle:Precio:lista-edit.html.twig', array(
            'entity' => $entity,
            'listas' => $listas,
            'form'   => $form->createView(),
        ));
    }     

    /**
     * @Route("/lista/{id}/edit", name="stock_precio_lista_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_lista');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:PrecioLista')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Lista entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);
        $listas = $em->getRepository('AppBundle:PrecioLista')->findAll();
        return $this->render('AppBundle:Precio:lista-edit.html.twig', array(
            'entity'      => $entity,
            'listas' => $listas,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }    
    
    /**
    * Creates a form to edit a Producto entity.  
    * @param Producto $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PrecioLista $entity)
    {
        $form = $this->createForm(new PrecioListaType(), $entity, array(
            'action' => $this->generateUrl('stock_precio_lista_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }     
    
    /**
     * @Route("/lista/{id}", name="stock_precio_lista_update")
     * @Method("PUT")
     * @Template("AppBundle:Precio:lista-edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_lista');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:PrecioLista')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Lista entity.');
        }

       // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            if($entity->getPrincipal()) {   
                $em->getRepository('AppBundle:PrecioLista')->setPrincipalOff();    
                $entity->setPrincipal(1);
            }
            $em->flush();
            return $this->redirect($this->generateUrl('stock_precio_lista'));
        }
        $listas = $em->getRepository('AppBundle:PrecioLista')->findAll();
        return $this->render('AppBundle:Precio:lista-edit.html.twig', array(
            'entity'      => $entity,
            'listas' => $listas,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }   

    /**
     * @Route("/lista/delete/{id}", name="stock_precio_lista_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {   
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_lista');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:PrecioLista')->find($id);
        if($entity->getPrincipal())
        { return new Response(json_encode('No puede eliminar la lista indicada como Principal')); }
        try{
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }    
    
    /**
     * Finds and displays a PrecioLista entity.     
     */
    public function showAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_lista');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AdminBundle:PrecioLista')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PrecioLista entity.');
        }
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('AdminBundle:PrecioLista:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),   
            ));
    }
    
    
    
    /*
     * ACTUALIZACION DE PRECIOS
     */
    /**
     * @Route("/actualizacion", name="stock_precio_actualizacion")
     * @Method("GET")
     * @Template("AppBundle:Precio:actualizacion-index.html.twig")
     */    
    
    public function actualizacionListAction(){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_actualizacion');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppBundle:PrecioActualizacion')->findAll();
        return $this->render('AppBundle:Precio:actualizacion-index.html.twig', array(
            'entities' => $entities,
        ));
    }
    
    /**
     * @Route("/actualizacion/new", name="stock_precio_actualizacion_new")
     * @Method("GET")
     * @Template("AppBundle:Precio:actualizacion-new.html.twig")
     */    
    public function actualizacionNewAction(){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_actualizacion');
        $em = $this->getDoctrine()->getManager();
        $ppal = $em->getRepository('AppBundle:PrecioLista')->findOneByPrincipal(1);
        $entity = new PrecioActualizacion();
        $entity->setCriteria('T');
        $entity->setTipoActualizacion('P');
        if($ppal) { $entity->setPrecioLista($ppal); }
        $form = $this->createForm(new PrecioActualizacionType(), $entity, array(
            'action' => $this->generateUrl('stock_precio_actualizacion_create'),
            'method' => 'POST',
        ));
        return $this->render('AppBundle:Precio:actualizacion-new.html.twig', array(
            'entity' => $entity,
            'form'  => $form->createView(),
        ));
    }
    
    /**
     * @Route("/actualizacion", name="stock_precio_actualizacion_create")
     * @Method("POST")
     * @Template()
     */           
    public function actualizacionCreateAction(Request $request){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_actualizacion');
        $entity = new PrecioActualizacion();
        $form = $this->createForm(new PrecioActualizacionType(), $entity, array(
            'action' => $this->generateUrl('stock_precio_actualizacion_create'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // Proceso de valores
            $valores = explode( ',', $request->get('valtxt') );
            $txtvalores = '';
            if($entity->getCriteria()<>'T') {
                $repo = ($entity->getCriteria()=='P') ? 'ComprasBundle:Proveedor' : 'ConfigBundle:Parametro' ;
                $txtvalores = ($entity->getCriteria()=='P') ? '<strong>Proveedor:</strong> ' : '<strong>Rubro:</strong> ';
                foreach ($valores as $value) {
                    // armar cadena para guardar
                    $dato = $em->getRepository($repo)->find($value);
                    $txtvalores = $txtvalores.'  <strong>[</strong>'.$dato->getText().'<strong>]</strong>  ';
                }
            }else{ $txtvalores = '<strong>Todos</strong>'; }
            $entity->setValores($txtvalores);
            try{
               //Proceso de ajuste de precios
                $user = $this->getUser();
                $actualiz = $em->getRepository('AppBundle:PrecioActualizacion')
                               ->setPreciosActualizados($entity,$request->get('valtxt'),$user);               
                $em->persist($entity);
                $em->flush();
            } catch (\Exception $ex) {    
                //$this->addFlash('danger',$ex->getTraceAsString());
                $this->addFlash('error', $ex->getMessage());
                return $this->render('AppBundle:Precio:actualizacion-new.html.twig', array(
                        'entity' => $entity,
                        'form'  => $form->createView(),
                ));
            }
            return $this->redirect($this->generateUrl('stock_precio_actualizacion'));
        }
        return $this->render('AppBundle:Precio:actualizacion-new.html.twig', array(
            'entity' => $entity,
            'form'  => $form->createView(),
        ));
    }    
    
        
    
    /*
     * PRECIOS
     */
    
    /**
     * @Route("/listado", name="stock_precio_listado")
     * @Method("GET")
     * @Template("AppBundle:Precio:listado.html.twig")
     */    
    public function listadoAction(Request $request){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_listado');
        $id = $request->get('listaId');
        $provid = $request->get('provId');
        $rubroid = $request->get('rubroId');
        $em = $this->getDoctrine()->getManager();
        $listas = $em->getRepository('AppBundle:PrecioLista')->findAll();
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(
             array('activo'=> '1'), 
             array('nombre' => 'ASC')
           );   
        $rubros = $em->getRepository('ConfigBundle:Parametro')->findSubRubros();
       // $session = $this->get('session') ;
        if( !$id ){
            $ppal = $em->getRepository('AppBundle:PrecioLista')->findOneByPrincipal(1);
            $id = $ppal ? $ppal->getId() : 1 ;
        //    $session->set('listado_listaId', $ppal->getId() );
        }
        $listado = $em->getRepository('AppBundle:PrecioLista')
                      ->findByRubroProductoyLista($rubroid,$provid,$id);
        return $this->render('AppBundle:Precio:listado.html.twig', array(
            'listas' => $listas, 
            'listado' => $listado, 
            'listaId' => $id,
            'proveedores' => $proveedores,
            'provId' => $provid,
            'rubros' => $rubros,
            'rubroId' => $rubroid
        ));
    }
    
    /**
     * @Route("/printListaPrecios.{_format}", 
     * defaults = { "_format" = "pdf" },
     * name="print_lista_precios")
     * @Method("POST")
     */    
    public function printListaPreciosAction(Request $request){
        $em = $this->getDoctrine()->getManager();    
        $items = $request->get('datalist');     
        $lista = $em->getRepository('AppBundle:PrecioLista')->find($request->get('listaid'));        
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($request->get('proveedorid'));  
        $rubro = $em->getRepository('ConfigBundle:Parametro')->find($request->get('rubroid'));
        $textoFiltro = array(
            $lista->getNombre(),
            $proveedor?$proveedor->getNombre():'Todos', 
            $rubro?$rubro->getNombre():'Todos', 
        ) ;
        
    //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
    //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';
        
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:Precio:pdf-lista-precios.pdf.twig',
                      array('items'=>json_decode($items), 'filtro'=>$textoFiltro, 'search' => $request->get('searchterm') ), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);       
        $hoy = new \DateTime(); 
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition'=>'filename=precios'.$lista->getNombre().$hoy->format('dmY_Hi').'.pdf'));
    }    
    
    
    
    /**
     * @Route("/listadoPdf", name="stock_precio_listado_pdf")
     * @Method("GET")
     * @Template("AppBundle:Precio:listado.html.twig")
     */ 
    public function listadoPreciosPdfAction(Request $request){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_listado');
        $id = $request->get('lista');
        $em = $this->getDoctrine()->getManager();
        $listado = $em->getRepository('AppBundle:PrecioLista')->listOrderByRubro($id);
        $rubros = $em->getRepository('ConfigBundle:Parametro')->findRubros();
        $html = $this->renderView('AppBundle:Precio:listadoPdf.html.twig', 
                array('listado' => $listado, 'rubros'=>$rubros));
        return new Response($html);
    }    
    /**
     * @Route("/exportPrecios", name="stock_precio_export")
     * @Method("POST")
     */  
    public function exportPreciosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $search =  $request->get('searchterm');
        $listaId = $request->get('listaid');        
        $lista = $em->getRepository('AppBundle:PrecioLista')->find($listaId);
        $proveedorId = $request->get('proveedorid');        
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $rubroId = $request->get('rubroid');        
        $rubro = $em->getRepository('ConfigBundle:Parametro')->find($rubroId);

        $items = $em->getRepository('AppBundle:Precio')->getPreciosForExportXls($listaId,$rubroId,$proveedorId,$search);

        $textoFiltro = array(
            $lista->getNombre(),
            ($proveedorId) ? $proveedor->getNombre() : 'Todos',
            ($rubroId) ? $rubro->getNombre() : 'Todos'
             );

        $partial = $this->renderView('AppBundle:Precio:export-xls.html.twig',
                array('items' => $items, 'filtro' => $textoFiltro, 'search' => $search));
        $hoy = new \DateTime();
        $fileName = 'Precios_'.$lista->getNombre(). $hoy->format('dmY_Hi');
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'filename="' . $fileName . '.xls"');
        $response->setContent($partial);
        return $response;
    }
    
    /**
     * @Route("/new", name="stock_precio_new")
     * @Method("POST")
     * @Template("AppBundle:Precio:precio-edit.html.twig")
     */
    public function precioNewAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_new');
        $id = $request->get('listaId');
       // $id = $reqform['listaId'];
        $em = $this->getDoctrine()->getManager();
        $lista = $em->getRepository('AppBundle:PrecioLista')->find($id);
        $entity = new Precio();
        $entity->setPrecioLista($lista);
        $form = $this->createForm(new PrecioType(), $entity, array(
            'action' => $this->generateUrl('stock_precio_create'),
            'method' => 'POST',
        ));
        return $this->render('AppBundle:Precio:precio-edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }    
    
    /**
     * @Route("/", name="stock_precio_create")
     * @Method("POST")
     * @Template("AppBundle:Precio:precio-edit.html.twig")
     */    
    public function precioCreateAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_new');
        $reqform = $request->get('appbundle_precio');
        $id = $reqform['precioLista'];
        $em = $this->getDoctrine()->getManager();
        $lista = $em->getRepository('AppBundle:PrecioLista')->find($id);
        $entity = new Precio();
        $entity->setPrecioLista($lista);
        $form = $this->createForm(new PrecioType(), $entity, array(
            'action' => $this->generateUrl('stock_precio_create'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success','El precio fue agregado! Ingresar nuevo precio...');
        }
        return $this->redirectToRoute('stock_precio_new', [ 'listaId' => $id ], 307);
        //return $this->redirect($this->generateUrl('stock_precio_new', array('precioLista' => $id) ));
    }      
    
    /**
     * @Route("/{id}/edit", name="stock_precio_edit")
     * @Method("GET")
     * @Template()
     */    
    public function precioEditAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Precio')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Precio entity.');
        }
        $form = $this->createForm(new PrecioType(), $entity, array(
            'action' => $this->generateUrl('stock_precio_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));        

        return $this->render('AppBundle:Precio:precio-edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),            
        ));
    }    
    
    /**
     * @Route("/delete/{id}", name="stock_precio_delete")
     * @Method("POST")
     */
    public function precioDeleteAction($id)
    {   
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Precio')->find($id);        
        try{
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  
            $msg= $ex->getTraceAsString();     
            $this->addFlash('danger',$msg);
        }
        $this->addFlash('success','El precio fue eliminado!');
        return new Response(json_encode($msg));
    }   

    /**
     * @Route("/update/{id}", name="stock_precio_update")
     * @Method("POST")
     * @Template("AppBundle:Precio:precio-edit.html.twig")
     */
    public function precioUpdateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_precio_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Precio')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Precio entity.');
        }
        $form = $this->createForm(new PrecioType(), $entity, array(
            'action' => $this->generateUrl('stock_precio_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->flush();
        }
        return $this->redirect($this->generateUrl('stock_precio_listado'));
    }    

    
/*    public function listadoViewAction(){
        $id = $this->getRequest()->get('listaId');
        $em = $this->getDoctrine()->getManager();
        $listas = $em->getRepository('AdminBundle:PrecioLista')->findAll();
       // $session = $this->get('session') ;
        if( !$id ){
            $ppal = $em->getRepository('AdminBundle:PrecioLista')->findOneByPrincipal(1);
            $id = $ppal->getId();
        //    $session->set('listado_listaId', $ppal->getId() );
        }
        $listado = $em->getRepository('AdminBundle:Precio')
                      ->findByPrecioLista($id);
        return $this->render('AdminBundle:PrecioLista:listadoView.html.twig', array(
            'listas' => $listas, 'listado' => $listado, 'listaId' => $id
        ));
    }*/
    
   /* public function getListaAction(Request $request){
        $id = $request->get('id');
        $session = $this->get('session') ;
        $session->set('listado_listaId', $id );
        $em = $this->getDoctrine()->getManager();
        $listado = $em->getRepository('AdminBundle:Precio')->findByPrecioLista($id);
        $partial = $this->renderView('AdminBundle:PrecioLista:listado-row.html.twig',
                                array('listado'=>$listado)); 
        return new Response($partial); 
    }
    public function getListaViewAction(Request $request){
        $id = $request->get('id');
        $session = $this->get('session') ;
        $session->set('listado_listaId', $id );
        $em = $this->getDoctrine()->getManager();
        $listado = $em->getRepository('AdminBundle:Precio')->findByPrecioLista($id);
        $datos = array();
        foreach ($listado as $precio) {
            $dato = array($precio->getProducto()->getCodigo(),
                $precio->getProducto()->getNombre(),$precio->getProducto()->getRubro()->getNombre(),
                    $precio->getPrecioxMayor(), $precio->getPrecioxMenor() );
                array_push($datos, $dato);
        }
        
        $partial = $this->renderView('AdminBundle:PrecioLista:listado-view-row.html.twig',
                                array('listado'=>$listado)); 
        return new Response(json_encode($datos)); 
    }*/
    


    
     /**
     * @Route("/getRubroProveedor", name="get_rubro_proveedor")
     * @Method("GET")
     */    
    public function getRubroProveedorAction(Request $request){
        $tipo = $request->get('tipo');
        $em = $this->getDoctrine()->getManager();
        if($tipo=='R'){         $tipo_res = 'Rubros';
        }elseif ($tipo=='P') {  $tipo_res = 'Proveedores';}
        $datos = $em->getRepository('AppBundle:PrecioActualizacion')->getDatosActPrecio($tipo);
        $partial = $this->renderView('AppBundle:Precio:rubro-proveedor-row.html.twig',
                                array('tipo'=>$tipo_res,'datos'=>$datos)); 
        return new Response($partial); 
    }
    
    public function getPrecioProductoAction(Request $request){
        $prod = $request->get('prod');
        $lista = $request->get('lista');
        $tipo = $request->get('tipo');
        $em = $this->getDoctrine()->getManager();
        $precio = $em->getRepository('AdminBundle:PrecioLista')->findByProductoyLista($prod,$lista); 
        if($precio){
            $valor= ($tipo) ? $precio->getPrecioxMenor() : $precio->getPrecioxMayor();
            /*if($tipo=='costo'){      $valor=$precio->getCosto();}
            elseif($tipo=='menor'){  $valor=$precio->getPrecioxMenor();}
            else{                    $valor=$precio->getPrecioxMayor();}*/
        }else{ $valor='0.00';}
        $session = $this->get('session');
        $producto = $em->getRepository('AdminBundle:Producto')->find($prod); 
        $resul = array('precio'=>$valor,'m3'=>$producto->getDensidad(), 'iva' => $session->get('iva') );
        return new Response( json_encode( $resul ) );
    }



 /*   public function listModvtasAction(){
        $em = $this->getDoctrine()->getManager();
        $listas = $em->getRepository('AdminBundle:PrecioLista')->findAll();
        $session = $this->get('session') ;
        if( !$session->has('listado_listaId')){
            $ppal = $em->getRepository('AdminBundle:PrecioLista')->findOneByPrincipal(1);
            $session->set('listado_listaId', $ppal->getId() );
        }
        $listado = $em->getRepository('AdminBundle:Precio')
                      ->findByPrecioLista($session->get('listado_listaId'));
        return $this->render('AdminBundle:PrecioLista:listadoModvtas.html.twig', array(
            'listas' => $listas, 'listado' => $listado
        ));
    }*/
    
}
