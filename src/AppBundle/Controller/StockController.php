<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\StockMovimiento;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockAjuste;
use AppBundle\Entity\StockAjusteDetalle;
use AppBundle\Form\StockAjusteDetalleType;
use AppBundle\Form\StockAjusteType;
use ComprasBundle\Entity\LoteProducto;

/**
 * @Route("/stock")
 */
class StockController extends Controller
{

    /** AJUSTES **/
    /**
     * @Route("/ajuste", name="stock_ajuste")
     * @Method("GET")
     * @Template()
     */
    public function ajusteAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_ajuste');
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
        $entities = $em->getRepository('AppBundle:StockAjuste')->getAjustesByCriteria($depId,$periodo);
        return $this->render('AppBundle:Stock:ajuste.html.twig', array(
            'entities' => $entities, 'depositos'=>$depositos, 'depId' => $depId,'periodo' => $periodo
        ));
    }

    /**
     * @Route("/ajuste/new", name="stock_ajuste_new")
     * @Method("GET")
     * @Template("AppBundle:Ajuste:ajusteEdit.html.twig")
     */
    public function ajusteNewAction(Request $request){
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_ajuste_new');
        $depId = $request->get('depId');
        $em = $this->getDoctrine()->getManager();
        $deposito = $em->getRepository('AppBundle:Deposito')->find( $depId );
        $entity = new StockAjuste();
        $entity->setFecha(new \DateTime);
        $entity->setDeposito($deposito);
        $em->persist($entity);
        $em->flush();
        $form   = $this->createEditForm($entity);
        return $this->render('AppBundle:Stock:ajusteEdit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a StockAjuste entity.
    * @param StockAjuste $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(StockAjuste $entity)
    {
        $form = $this->createForm(new StockAjusteType(), $entity, array(
            'attr'=>array('unidadnegocio' => $this->get('session')->get('unidneg_id')),
            'action' => $this->generateUrl('stock_ajuste_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/ajuste/{id}/edit", name="stock_ajuste_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_ajuste_new');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:StockAjuste')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Ajuste.');
        }
        $editForm = $this->createEditForm($entity);

        return $this->render('AppBundle:Stock:ajusteEdit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView()
        ));
    }

    /**
     * @Route("/ajuste/{id}", name="stock_ajuste_update")
     * @Method("PUT")
     * @Template("AppBundle:Ajuste:ajusteEdit.html.twig")
     */
    public function ajusteUpdateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_ajuste_new');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:StockAjuste')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Ajuste.');
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        $datos = $request->get('appbundle_stockajuste');
        $registrarAjuste = isset($datos['registrarAjuste']);
        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {

                $csv = $editForm['csv']->getData();
                if($csv){
                  // Open the file
                  if (($handle = fopen($csv->getPathname(), "r")) !== false) {
                      // Read and process the lines.
                      while (($data = fgetcsv($handle,0,';')) !== false) {
                        $detalle = new StockAjusteDetalle();
                        $letra = strtoupper(trim($data[0]));
                        if( in_array($letra , array('I','E')) ){
                          $signo = $letra === 'I' ? '+' : '-';
                        }else{
                          throw new \Exception("Error Processing Request");
                        }
                        $detalle->setSigno($signo);
                        $producto = $em->getRepository('AppBundle:Producto')->findOneByCodigo(trim($data[1]));
                        $detalle->setProducto($producto);
                        $detalle->setCantidad( floatval($data[2]) );
                        $detalle->setMotivo( $data[3]);
                        if( $data[4] ){
                          $lote = $em->getRepository('ComprasBundle:LoteProducto')->findOneByNroLote($data[4]);
                          if(!$lote && isset($data[5])){
                            $vto = new \DateTime($data[5]);
                            $lote = new LoteProducto();
                            $lote->setNroLote($data[4]);
                            $lote->setFechaVencimiento($vto);
                            $lote->setProducto($producto);
                          }
                          $detalle->addLote($lote);
                        }
                        $entity->addDetalle($detalle);
                      }
                      fclose($handle);
                  }
                }
                $em->persist($entity);
                $em->flush();
                if ($registrarAjuste) {

                    $deposito = $entity->getDeposito();
                    foreach ($entity->getDetalles() as $item) {
                        // ajustar stock
                        $producto = $item->getProducto();
                        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(), $deposito->getId());
                        if (!$stock) {
                            $stock = new Stock();
                            $stock->setProducto($producto);
                            $stock->setDeposito($deposito);
                            $stock->setCosto($producto->getCosto());
                            $stock->setCantidad(0);
                        }
                        //determinar cantidad si es x bulto.
                        $cantidad = $item->getCantidad();
                        if ($item->getBulto()) {
                            $cantidad = $cantidad * $item->getCantidadxBulto();
                        }
                        if ($item->getSigno() == '+')
                            $cant = $stock->getCantidad() + $cantidad;
                        else
                            $cant = $stock->getCantidad() - $cantidad;
                        $stock->setCantidad($cant);
                        $em->persist($stock);
                        // Cargar movimiento
                        $movim = new StockMovimiento();
                        $movim->setFecha($entity->getFecha());
                        $movim->setTipo('AJUSTE');
                        $movim->setSigno($item->getSigno());
                        $movim->setMovimiento($entity->getId());
                        $movim->setProducto($producto);
                        $movim->setCantidad($cantidad);
                        $movim->setDeposito($deposito);
                        $em->persist($movim);
                        $em->flush();
                    }
                    $entity->setProcesado(1);
                    $em->flush();
                }

                $em->getConnection()->commit();

                // si se cargo desde un archivo volver a la edicion
                if( !$csv ){
                  return $this->redirectToRoute('stock_ajuste');
                }

            } catch (\Exception $ex) {
                $msg = $csv ? "No se han podido cargar los datos desde el archivo. Controle que el formato sea correcto." : $ex->getMessage();
                $this->get('session')->getFlashBag()->add('error', $msg );
                $em->getConnection()->rollback();
            }
        }

        return $this->render('AppBundle:Stock:ajusteEdit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView()
        ));
    }



    /**
     * @Route("/ajuste", name="stock_ajuste_create")
     * @Method("POST")
     * @Template("AppBundle:Stock:ajusteNew.html.twig")
     */
    public function ajusteCreateAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_ajuste_new');
        $entity = new StockAjuste();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {

$file = $form['csv']->getData();

// Open the file
        if (($handle = fopen($csv->getPathname(), "r")) !== false) {
            // Read and process the lines.
            while (($data = fgetcsv($handle)) !== false) {
var_dump($data);
            }
            fclose($handle);
            //$em->flush();
        }
die;
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try{
                $em->persist($entity);
                $em->flush();
                $deposito = $entity->getDeposito();
                foreach ($entity->getDetalles() as $item) {
                    // ajustar stock
                    $producto = $item->getProducto();
                    $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(), $deposito->getId());
                    if (!$stock) {
                        $stock = new Stock();
                        $stock->setProducto($producto);
                        $stock->setDeposito($deposito);
                        $stock->setCosto($producto->getCosto());
                        $stock->setCantidad(0);
                    }
                    //determinar cantidad si es x bulto.
                    $cantidad = $item->getCantidad();
                    if( $item->getBulto()){
                        $cantidad = $cantidad * $item->getCantidadxBulto();
                    }
                    if ($item->getSigno() == '+')
                        $cant = $stock->getCantidad() + $cantidad;
                    else
                        $cant = $stock->getCantidad() - $cantidad;
                    $stock->setCantidad($cant);
                    $em->persist($stock);
                    // Cargar movimiento
                    $movim = new StockMovimiento();
                    $movim->setFecha($entity->getFecha());
                    $movim->setTipo('AJUSTE');
                    $movim->setSigno($item->getSigno());
                    $movim->setMovimiento($entity->getId());
                    $movim->setProducto($producto);
                    $movim->setCantidad($cantidad);
                    $movim->setDeposito($deposito);
                    $em->persist($movim);
                }
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('stock_ajuste'));
            } catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error',$ex->getMessage() );
                $em->getConnection()->rollback();
            }

        }
        return $this->render('AppBundle:Stock:ajusteNew.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * @Route("/ajuste/{id}/show", name="stock_ajuste_show")
     * @Method("GET")
     * @Template()
     */
    public function ajusteShowAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_ajuste');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:StockAjuste')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StockAjuste entity.');
        }
        return $this->render('AppBundle:Stock:ajusteShow.html.twig', array(
            'entity'      => $entity, ));
    }

    /**
     * @Route("/{id}/printStockAjuste.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ajuste_stock")
     * @Method("GET")
     */
    public function printStockAjusteAction($id){
        $em = $this->getDoctrine()->getManager();
        $ajuste = $em->getRepository('AppBundle:StockAjuste')->find($id);

      //  $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
      //  $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:Stock:pdf-ajuste-stock.pdf.twig',
                      array('ajuste'=> $ajuste), $response);

        $xml = $response->getContent();

        $content = $facade->render($xml);

        return new Response($content, 200, array('content-type' => 'application/pdf',
                            'Content-Disposition'=>'filename=ajuste_stock_'.$ajuste->getFecha()->format('dmYHi').'.pdf'));

    }

/*
 * AJAX AJUSTES
 */

    /**
     * @Route("/ajax/renderAddItemAjuste", name="stock_render_item_ajuste")
     * @Method("GET")
     */
    public function renderAddItemAjusteAction()
    {
        $entity = new StockAjusteDetalle();
        $form = $this->createForm(new StockAjusteDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_ajuste_create'),
            'method' => 'PUT',
        ));
        $html = $this->renderView('AppBundle:Stock:partial-item-ajuste.html.twig',
                array('entity' => $entity, 'form' => $form->createView() )
        );
        return new Response($html);
    }
    /**
     * @Route("/ajax/createItemAjuste", name="stock_item_ajuste_create")
     * @Method("PUT")
     * @Template()
     */
    public function createItemAjuste(Request $request){
        $em = $this->getDoctrine()->getManager();
        $data = $request->get('appbundle_stockajustedetalle');
        $producto = $em->getRepository('AppBundle:Producto')->find($data['producto']);
        $entity = new StockAjusteDetalle();
        $entity->setProducto($producto);
        $form = $this->createForm(new StockAjusteDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_ajuste_create'),
            'method' => 'PUT',
        ));
        $id = $request->get('ajusteid');
        $ajuste = $em->getRepository('AppBundle:StockAjuste')->find($id);
        $form->handleRequest($request);
       $partial = '';
        $msg = 'No se pudo realizar esta operación.'.chr(13).' Verifique que no exista el valor que está intentando ingresar.';
        if ($form->isValid()) {
            try{
                $entity->setStockAjuste($ajuste);
                $em->persist($entity);
                $em->flush();
                $msg = 'OK';
                $partial = $this->renderView('AppBundle:Stock:tr_item_ajuste.html.twig', array('item'=>$entity));
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
     * @Route("/ajax/renderEditItemAjuste/{id}", name="stock_render_edit_item_ajuste")
     * @Method("GET")
     */
    public function renderEditItemAjusteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:StockAjusteDetalle')->find($id);
        $form = $this->createForm(new StockAjusteDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_ajuste_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $html = $this->renderView('AppBundle:Stock:partial-item-ajuste.html.twig',
                array('entity' => $entity, 'form' => $form->createView() )
        );
        return new Response($html);
    }

    /**
     * @Route("/ajax/updateItemAjuste/{id}", name="stock_item_ajuste_update")
     * @Method("PUT")
     * @Template()
     */
    public function updateItemAjuste(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:StockAjusteDetalle')->find($id);
        $form = $this->createForm(new StockAjusteDetalleType(), $entity, array(
            'action' => $this->generateUrl('stock_item_ajuste_update', array('id' => $entity->getId())),
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
        $partial = $this->renderView('AppBundle:Stock:tr_item_ajuste.html.twig',
                    array('item'=>$entity));
        $result = array('msg'=>$msg, 'tr'=>$partial);
        return new JsonResponse ( $result);
    }

    /**
     * @Route("/ajax/deleteItemAjuste", name="stock_item_ajuste_delete")
     * @Method("POST")
     */
    public function deleteItemAjusteAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('AppBundle:StockAjusteDetalle')->find($id);
        try {
            $em->remove($item);
            $em->flush();
            $msg = 'OK';
        } catch (\Exception $ex) {
            $msg = UtilsController::errorMessage($ex->getErrorCode());
        }
        return new Response($msg);
    }

    /*
 * LOTES
 */

    /**
     * @Route("/lote", name="stock_lote")
     * @Method("GET")
     * @Template()
     */
    public function lotesAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto');
        $prodId = $request->get('prodId');
        $em = $this->getDoctrine()->getManager();
        $productos = $em->getRepository('AppBundle:Producto')->findBy(array(), array('nombre' => 'ASC'));
        if( $prodId ){
           $entities = $em->getRepository('ComprasBundle:LoteProducto')->findByProducto($prodId);
        }else{
            $entities = $em->getRepository('ComprasBundle:LoteProducto')->findAll();
        }
        return $this->render('AppBundle:Stock:lotes.html.twig', array(
            'entities' => $entities, 'productos'=> $productos, 'prodId' => $prodId
        ));
    }

     /**
     * @Route("/movimiento", name="stock_movimiento")
     * @Method("GET")
     * @Template()
     */
    public function movimientoStockAction(Request $request)
    {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'movimiento_stock');
        $em = $this->getDoctrine()->getManager();
        $prodId = $request->get('prodId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $productos = $em->getRepository('AppBundle:Producto')->findBy(array('activo'=>1), array('nombre' => 'ASC'));
        if( !$prodId ){
            $prodId = $productos[0]->getId();
        }
        $entities = $em->getRepository('AppBundle:StockMovimiento')->findByCriteria( $unidneg, $prodId, $desde, $hasta);

        return $this->render('AppBundle:Stock:movimientos.html.twig', array(
            'entities' => $entities,
            'productos' => $productos,
            'prodId' => $prodId,
            'desde' => $desde,
            'hasta' => $hasta
        ));

    }

    /**
     * @Route("/lote/delete/{id}", name="stock_lote_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:LoteProducto')->find($id);
        try{
            if( $entity->tieneSalidas() ){
                $msg ='No se puede eliminar este lote.';
            }else{
                $em->remove($entity);
                $em->flush();
                $msg ='OK';
            }
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }
    /**
     * @Route("/lote/toggle/{id}", name="stock_lote_toggle")
     * @Method("POST")
     */
    public function toggleAction($id)
    {
        // TOGGLE ESTADO ACTIVO
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:LoteProducto')->find($id);
        try{
            $entity->setActivo( !$entity->getActivo() );
            $em->persist($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }


     /**
     * @Route("/lote/{id}/show", name="stock_lote_show")
     * @Method("GET")
     * @Template()
     */
    public function loteShowAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto');
        $em = $this->getDoctrine()->getManager();
        $lote = $em->getRepository('ComprasBundle:LoteProducto')->find($id);
        if (!$lote) {
            throw $this->createNotFoundException('Unable to find Lote entity.');
        }
        return $this->render('AppBundle:Stock:loteProductoShow.html.twig', array(
            'entity'      => $lote, ));
    }

     /**
     * @Route("/informe/despachados", name="stock_informe_despachado")
     * @Method("GET")
     * @Template()
     */
    public function despachadoAction(Request $request)
    {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'stock_informe_despachado');
        $em = $this->getDoctrine()->getManager();
        $depId = $request->get('depId');
        $hoy = new \DateTime();
        $inicio = date("d-m-Y",strtotime($hoy->format('d-m-Y')."- 30 days"));
        $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
        $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y');
        $periodo = array('desde'=>$desde, 'hasta'=>$hasta );
//        $depositos = $em->getRepository('AppBundle:Deposito')->findBy(array("central" => "1", "unidadNegocio"=> $unidneg ));
        $depositos = $this->getUser()->getDepositos($unidneg);
        if( !$depId ){
            $depId = $depositos[0]->getId();
        }
        $entities = $em->getRepository('AppBundle:Despacho')->findDespachadoByCriteria( $depId, $periodo);

        foreach ($entities as &$ent){
            $id = $em->getRepository('AppBundle:Producto')->findOneByCodigo($ent['codigo']);
            $ult = $em->getRepository('ComprasBundle:Factura')->getPrecioUltimaCompra($id->getId());
            $ent['precultcompra'] = (float) $ult['precio'] ;
        }

        return $this->render('AppBundle:Informe:despachados.html.twig', array(
            'entities' => $entities,
            'depositos' => $depositos,
            'depId' => $depId,
            'desde' => $desde,
            'hasta' => $hasta
        ));

    }

    /**
     * IMPRESION DE listado
     */
    /**
     * @Route("/printInformeDespachado.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_informe_despachado")
     * @Method("POST")
     */
    public function printInformeDespachadoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $tipo = $request->get('tipo');
        $depositoId = $request->get('depositoid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $deposito = $em->getRepository('AppBundle:Deposito')->find($depositoId);
        $textoFiltro =  array( $deposito?$deposito->getNombre():'Todos', $fdesde?$fdesde:'', $fhasta?$fhasta:'' ) ;

    //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
    //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:Informe:pdf-despachados.pdf.twig',
                      array('items'=>json_decode($items), 'filtro'=>$textoFiltro, 'tipo'=>$tipo,
                          'search' => $request->get('searchterm') ), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition'=>'filename=informe_productos_despachados_'.$hoy->format('dmY_Hi').'.pdf'));
    }


}