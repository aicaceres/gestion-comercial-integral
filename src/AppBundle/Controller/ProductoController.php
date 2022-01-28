<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\Producto;
use AppBundle\Form\ProductoType;
//use Symfony\Component\HttpFoundation\JsonResponse;
use ComprasBundle\Entity\LoteProducto;

class ProductoController extends Controller {
    private $notfound = "No se encuentra el producto";

    /**
     * @Route("/producto", name="stock_producto")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto');
        $provId = $request->get('provId');
        $em = $this->getDoctrine()->getManager();
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(array('activo' => 1), array('nombre' => 'ASC'));
       /* if ($provId) {
            $entities = $em->getRepository('AppBundle:Producto')->findByProveedor($provId);
        }
        else {
            $entities = $em->getRepository('AppBundle:Producto')->findAll();
        }*/
        return $this->render('AppBundle:Producto:index.html.twig', array(
                   // 'entities' => $entities,
                    'proveedores' => $proveedores,
                    'provId' => $provId,
        ));
    }

    /**
     * @Route("/producto/new", name="stock_producto_new")
     * @Method("GET")
     * @Template("AppBundle:Producto:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto_new');
        $entity = new Producto();
        $em = $this->getDoctrine()->getManager();
        $unid = $em->getRepository('ConfigBundle:Parametro')->find(29);
        $entity->setUnidadMedida($unid);
        $form = $this->createCreateForm($entity);

        return $this->render('AppBundle:Producto:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Producto entity.
     * @param Producto $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Producto $entity) {
        $form = $this->createForm(new ProductoType(), $entity, array(
            'action' => $this->generateUrl('stock_producto_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/producto", name="stock_producto_create")
     * @Method("POST")
     * @Template("AppBundle:Producto:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto_new');
        $entity = new Producto();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('stock_producto'));
        }
        return $this->render('AppBundle:Producto:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/producto/{id}/edit", name="stock_producto_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Producto')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException($this->notfound);
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Producto:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Producto entity.
     *
     * @param Producto $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Producto $entity) {
        $form = $this->createForm(new ProductoType(), $entity, array(
            'action' => $this->generateUrl('stock_producto_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/producto/{id}", name="stock_producto_update")
     * @Method("PUT")
     * @Template("AppBundle:Producto:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Producto')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException($this->notfound);
        }

        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('stock_producto'));
        }
        return $this->render('AppBundle:Producto:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/producto/{id}/show", name="stock_producto_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Producto')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException($this->notfound);
        }

        // ARMAR EL TEMPLATE !!!!
        //  $this->addFlash('info', 'La Vista del producto está en desarrollo');
        //  return $this->redirect($this->generateUrl('stock_producto'));

        return $this->render('AppBundle:Producto:show.html.twig', array(
                    'entity' => $entity,
        ));
    }

    /**
     * @Route("/producto/delete/{id}", name="stock_producto_delete")
     * @Method("POST")
     */
    public function deleteAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_producto_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Producto')->find($id);
        try {
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
     *  INVENTARIO
     */

    /**
     * @Route("/inventario/enstock", name="stock_inventario_enstock")
     * @Method("GET")
     */
    public function enstockAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_inventario_enstock');
        $provId = $request->get('provId');
        $depId = $request->get('depId');
        $em = $this->getDoctrine()->getManager();
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findByActivo(1);
        $unidneg = $this->get('session')->get('unidneg_id');
        $depositos = $this->getUser()->getDepositos($unidneg);

        if (count($depositos) > 0) {
            $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findByActivo(1);
            $entities = $em->getRepository('AppBundle:Producto')->findProductosPorDepositoyProveedor($unidneg, $provId, $depId);
        }
        else {
            $this->addFlash('error', 'No posee depósitos asignados!!');
            return $this->redirect($this->generateUrl('stock'));
        }

        //$entities = $em->getRepository('AppBundle:Producto')->findProductosPorDepositoyProveedor($unidneg,$provId,$depId);
        /*        if($provId||$depId){
          //$entities = $em->getRepository('AppBundle:Producto')->findByProveedor($provId);
          $entities = $em->getRepository('AppBundle:Producto')->findProductosPorDepositoyProveedor(array("proveedor" => $provId, "deposito"=> $depId ));

          }else{
          $entities = $em->getRepository('AppBundle:Producto')->findAll();
          }
         */
        return $this->render('AppBundle:Producto:listado.html.twig', array(
                    'entities' => $entities,
                    'proveedores' => $proveedores,
                    'depositos' => $depositos,
                    'provId' => $provId,
                    'depId' => $depId
        ));
    }

    /**
     * @Route("/printProductos.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_productos")
     * @Method("POST")
     */
    public function printProductosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $proveedorId = $request->get('proveedorid');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $textoFiltro = $proveedor ? $proveedor->getNombre() : 'Todos';

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:Producto:pdf-productos.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro, 'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_productos' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /**
     * @Route("/printInventarioEnStock.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_inventario_enstock")
     * @Method("post")
     */
    public function printInventarioEnStockAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $depositoId = $request->get('depositoid');
        $proveedorId = $request->get('proveedorid');
        $deposito = $em->getRepository('AppBundle:Deposito')->find($depositoId);
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);

        $textoFiltro = array(($deposito ? $deposito->getNombre() : 'Todos'), ($proveedor ? $proveedor->getNombre() : 'Todos'));

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:Producto:pdf-enstock.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro, 'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=inventario_enstock' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /**
     * @Route("/exportProductos",
     * name="export_productos")
     * @Template()
     */
    public function exportProductosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $search =  $request->get('searchterm');
        $proveedorId = $request->get('proveedorid');        
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $items = $em->getRepository('AppBundle:Producto')->getProductosForExportXls($proveedorId,$search);

        $textoFiltro = array(($proveedor ? $proveedor->getNombre() : 'Todos'));

        $partial = $this->renderView('AppBundle:Producto:export-xls.html.twig',
                array('items' => $items, 'filtro' => $textoFiltro, 'search' => $search));
        $hoy = new \DateTime();
        $fileName = 'Productos_' . $hoy->format('dmY_Hi');
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'filename="' . $fileName . '.xls"');
        $response->setContent($partial);
        return $response;
    }
    /**
     * @Route("/exportInventarioEnStock",
     * name="export_inventario_enstock")
     * @Template()
     */
    public function exportInventarioEnStockAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $depositoId = $request->get('depositoid');
        $proveedorId = $request->get('proveedorid');
        $deposito = $em->getRepository('AppBundle:Deposito')->find($depositoId);
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);

        $textoFiltro = array(($deposito ? $deposito->getNombre() : 'Todos'), ($proveedor ? $proveedor->getNombre() : 'Todos'));

        $partial = $this->renderView('AppBundle:Producto:inventario-xls.html.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro, 'search' => $request->get('searchterm')));
        $hoy = new \DateTime();
        $fileName = 'Inventario_' . $hoy->format('dmY_Hi');
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'filename="' . $fileName . '.xls"');
        $response->setContent($partial);
        return $response;
    }

    /**
     * @Route("/inventario/bajominimo", name="stock_inventario_bajominimo")
     * @Method("GET")
     */
    public function bajominimoAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_inventario_bajominimo');
        $provId = $request->get('provId');
        $depId = $request->get('depId');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $this->get('session')->get('unidneg_id');
        $depositos = $this->getUser()->getDepositos($unidneg);

        if (count($depositos) > 0) {
            if (!$depId) {
                foreach ($depositos as $deposito) {
                    if ($deposito->getCentral() || $deposito->getPordefecto()) {
                        $depId = $deposito->getId();
                    }
                }
            }
        }
        else {
            $this->addFlash('error', 'No posee depósitos asignados');
        }

        /* if (count($depositos) > 0) {
          foreach ($depositos as $deposito) {
          if ($deposito->getCentral() && $deposito->getPordefecto()) {
          $depId = $deposito->getId();
          $depNombre = $deposito->getNombre();
          }
          }
          } else {
          $this->addFlash('error', 'No posee permiso para ver datos del depósito central');
          } */
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findByActivo(1);
        $entities = $em->getRepository('AppBundle:Producto')->findBajoMinimo($provId, $depId);
        return $this->render('AppBundle:Producto:bajominimo.html.twig', array(
                    'entities' => $entities,
                    'proveedores' => $proveedores,
                    'depositos' => $depositos,
                    'provId' => $provId, 'depId' => $depId
        ));
    }

    /**
     * @Route("/printInventarioBajoMinimo.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_inventario_bajominimo")
     * @Method("post")
     */
    public function printInventarioBajoMinimoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $proveedorId = $request->get('proveedorid');
        $depositoId = $request->get('depositoid');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $deposito = $em->getRepository('AppBundle:Deposito')->find($depositoId);

        $textoFiltroProveedor = $proveedor ? $proveedor->getNombre() : 'Todos';

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:Producto:pdf-bajominimo.pdf.twig',
                array('items' => json_decode($items), 'proveedor' => $textoFiltroProveedor,
                    'deposito' => $deposito->getEmpresaUnidadDeposito(), 'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=inventario_bajominimo' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /**
     * @Route("/inventario/valorizado", name="stock_inventario_valorizado")
     * @Method("GET")
     */
    public function valorizadoAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_inventario_valorizado');
        $provId = $request->get('provId');
        $depId = $request->get('depId');
        $em = $this->getDoctrine()->getManager();
        // control de depositos
        $unidneg = $this->get('session')->get('unidneg_id');
        $depositos = $this->getUser()->getDepositos($unidneg);
        if (count($depositos) > 0) {
            $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findByActivo(1);
            $entities = $em->getRepository('AppBundle:Producto')->findProductosPorDepositoyProveedor($unidneg, $provId, $depId);
        }
        else {
            $this->addFlash('error', 'No posee depósitos asignados!!');
            return $this->redirect($this->generateUrl('stock'));
        }

        return $this->render('AppBundle:Producto:valorizado.html.twig', array(
                    'entities' => $entities,
                    'proveedores' => $proveedores,
                    'depositos' => $depositos,
                    'provId' => $provId,
                    'depId' => $depId
        ));
    }

    /**
     * @Route("/printInventarioValorizado.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_inventario_valorizado")
     * @Method("post")
     */
    public function printInventarioValorizadoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $depositoId = $request->get('depositoid');
        $proveedorId = $request->get('proveedorid');
        $search = $request->get('searchterm');
        $deposito = $em->getRepository('AppBundle:Deposito')->find($depositoId);
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);

        $textoFiltro = array(($deposito ? $deposito->getNombre() : 'Todos'), ($proveedor ? $proveedor->getNombre() : 'Todos'));

        if ($request->get('option') == 'E') {
            $items = json_decode($request->get('datalist'));
            $hoy = new \DateTime();
            $filename = 'InventarioValorizado_' . $hoy->format('dmY_Hi') . '.xls';
            $search = $request->get('searchterm');
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
            $sheet = $phpExcelObject->setActiveSheetIndex(0);

            $user = $this->getUser()->getUsername();
            $phpExcelObject->getProperties()->setCreator($user)
                    ->setLastModifiedBy($user)
                    ->setTitle($filename)
                    ->setDescription("Listado de Proveedores");

            // Escribir títulos
            $sheet->setCellValue('A1', 'INVENTARIO VALORIZADO');

            $i = 2;
            if ($search) {
                $sheet->setCellValue('A' . $i, 'Término de Búsqueda: ' . $search);
                $i++;
            }
            if ($deposito) {
                $sheet->setCellValue('A' . $i, 'Depósito: ' . $deposito->getNombre());
                $i++;
            }
            if ($proveedor) {
                $sheet->setCellValue('A' . $i, 'Proveedor: ' . $proveedor->getNombre());
                $i++;
            }
            $i++;
            // Escribir encabezado
            $sheet->setCellValue('A' . $i, 'DEPOSITO')
                    ->setCellValue('B' . $i, 'RUBRO')
                    ->setCellValue('C' . $i, 'CODIGO')
                    ->setCellValue('D' . $i, 'PRODUCTO')
                    ->setCellValue('E' . $i, 'PROVEEDOR')
                    ->setCellValue('F' . $i, 'MINIMO')
                    ->setCellValue('G' . $i, 'ACTUAL')
                    ->setCellValue('H' . $i, 'VALORIZADO');

            // Escribir contenido
            $i++;
            $total = 0;
            foreach ($items as $item) {
                $sheet->getStyle('H' . $i)->getNumberFormat()->setFormatCode('0.000');

                $valoriz = str_replace(',', '', $item['7']);

                $sheet->setCellValue('A' . $i, $item['0'])
                        ->setCellValue('B' . $i, $item['1'])
                        ->setCellValue('C' . $i, $item['2'])
                        ->setCellValue('D' . $i, $item['3'])
                        ->setCellValue('E' . $i, $item['4'])
                        ->setCellValue('F' . $i, $item['5'])
                        ->setCellValue('G' . $i, $item['6'])
                        ->setCellValue('H' . $i, $valoriz);
                $i++;
                $total = $total + $valoriz;
            }
            $i++;
            $sheet->setCellValue('A' . $i, 'VALORIZADO TOTAL: $' . number_format($total, 3) . '.-');

            $phpExcelObject->getActiveSheet()->setTitle('Inventario Valorizado');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $phpExcelObject->setActiveSheetIndex(0);

            // create the writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            // create the response
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // adding headers


            $dispositionHeader = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $filename
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }
        else {
            $items = $request->get('datalist');
            $facade = $this->get('ps_pdf.facade');
            $response = new Response();
            $this->render('AppBundle:Producto:pdf-valorizado.pdf.twig',
                    array('items' => json_decode($items), 'filtro' => $textoFiltro, 'search' => $search), $response);

            $xml = $response->getContent();
            $content = $facade->render($xml);
            $hoy = new \DateTime();
            return new Response($content, 200, array('content-type' => 'application/pdf',
                'Content-Disposition' => 'filename=inventario_valorizado' . $hoy->format('dmY_Hi') . '.pdf'));
        }
    }

    /**
     * @Route("/inventario/valorizado/print", name="stock_inventario_valorizado_print")
     * @Method("GET")
     */
    public function valorizadoPrintAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'stock_inventario_valorizado');
        $id = $request->get('provId');
        $em = $this->getDoctrine()->getManager();
        if ($id) {
            $entities = $em->getRepository('AppBundle:Producto')->findByProveedor($id);
        }
        else {
            $entities = $em->getRepository('AppBundle:Producto')->findAll();
        }
        $html = $this->renderView('AppBundle:Producto:valorizadoPdf.html.twig',
                array('listado' => $entities, 'idprov' => $id));
        return new Response($html);
    }

// DATOS DEL PRODUCTO - AJAX

    /**
     * @Route("/getDataProducto", name="get_data_producto")
     * @Method("GET")
     */
    public function getDataProductoAction(Request $request) {
        $id = $request->get('prod');
        $dep = $request->get('dep');
        $cantStock = 0;
        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository('AppBundle:Producto')->find($id);
        // si paso deposito consultar stock y si ya está pedido
        $prodPedPendiente = NULL;
        if ($dep) {
            $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($producto->getId(), $dep);
            $cantStock = ($stock) ? $stock->getCantidad() : 0;

            $prodPedPendiente = $em->getRepository('AppBundle:Pedido')->findProdPedPendiente($producto->getId(), $dep);
        }
        // lotes
        $lotes = $em->getRepository('ComprasBundle:LoteProducto')->getByProductoId($producto->getId());
        $salida = array();
        foreach ($lotes as $lote) {
            $salida[] = array('id' => $lote->getId(), 'name' => $lote->__toString());
        }
        $resul = array('nombre' => $producto->getCodigoNombre(), 'productoId' => $producto->getId(), 'iva' => $producto->getIva(),
            'unidmed' => $producto->getUnidadMedida()->getNombre(), 'costo' => $producto->getCosto(),
            'bulto' => $producto->getBulto(), 'cantxBulto' => $producto->getCantidadxBulto(), 'stock' => $cantStock,
            'lotes' => $salida, 'prodPedPendiente' => $prodPedPendiente);
        return new Response(json_encode($resul));
    }

    /**
     * @Route("/getDataProductoVenta", name="get_data_producto_venta")
     * @Method("GET")
     */
    public function getDataProductoVentaAction(Request $request) {
        $id = $request->get('prod');
        $lista = $request->get('lista');
        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository('AppBundle:Producto')->find($id);
        // si paso deposito consultar stock
        $precio = $em->getRepository('AppBundle:PrecioLista')->findByProductoyLista($id, $lista);
        $resul = array('nombre' => $producto->getCodigoNombre(), 'productoId' => $producto->getId(),
            'unidmed' => $producto->getUnidadMedida()->getNombre(), 'precio' => ($precio) ? $precio->getPrecio() : 0,
            'iva' => $producto->getIva(), 'bulto' => $producto->getBulto(), 'cantxBulto' => $producto->getCantidadxBulto());
        return new Response(json_encode($resul));
    }

    /**
     * @Route("/getUnidadMedida", name="get_unidad_medida")
     * @Method("GET")
     */
    public function getUnidadMedidaAction(Request $request) {
        $id = $request->get('prod');
        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository('AppBundle:Producto')->find($id);
        return new Response(json_encode($producto->getUnidadMedida()->getNombre()));
    }

    public function getCostoProductoAction(Request $request) {
        $id = $request->get('prod');
        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository('AdminBundle:Producto')->find($id);
        return new Response($producto->getCosto());
    }

    /**
     * @Route("/getDepositosSinStockPorProducto", name="get_depositos_sinstock_por_producto")
     * @Method("GET")
     */
    public function getDepositosSinStockPorProducto(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $this->get('session')->get('unidneg_id');
        $depositos = $em->getRepository('AppBundle:Producto')->findDepositosSinstockPorProducto($id, $unidneg);
        return new Response(json_encode($depositos));
    }
    /**
     * @Route("/getListasSinPrecioPorProducto", name="get_listas_sinprecio_por_producto")
     * @Method("GET")
     */
    public function getListasSinPrecioPorProducto(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();  
        if($id){
            $listas = $em->getRepository('AppBundle:Producto')->findListasSinPrecioPorProducto($id);
        }else{
            $aux = $em->getRepository('AppBundle:PrecioLista')->findByActivo(1);
            $listas = array();
            /*foreach($aux as $lista){

            }*/
        }      
        return new Response(json_encode($listas));
    }

// STOCK DEL PRODUCTO EN UN DEPOSITO

    /**
     * @Route("/getStockProductoDeposito", name="get_stock_producto_deposito")
     * @Method("GET")
     */
    public function getStockProductoDepositoAction(Request $request) {
        $id = $request->get('id');
        $dep = $request->get('dep');
        $em = $this->getDoctrine()->getManager();
        $resul = $em->getRepository('AppBundle:Stock')->getStockProductoDeposito($id, $dep);
        return new Response(($resul) ? $resul : 0);
    }

    public function listModvtasAction() {
        // ir al listado de productos
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AdminBundle:Producto')->findAll();
        return $this->render('AdminBundle:Producto:listmodvtas.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * @Route("/selectLotes", name="select_lotes")
     * @Method("GET")
     */
    public function selectLotesAction(Request $request) {
        $prodid = $request->get('prodid');
        $em = $this->getDoctrine()->getManager();
        $lotes = $em->getRepository('ComprasBundle:LoteProducto')->getByProductoId($prodid);
        $array = array();
        foreach ($lotes as $lote) {
            $array[] = ['id' => $lote->getId(), 'text' => $lote->__toString()];
        }
        return new Response(json_encode($array));
        /* $salida = array();
          foreach ($lotes as $lote) {
          $salida[] = array('id'=>$lote->getId(),'text'=>$lote->__toString() ) ;
          }

          return new JsonResponse($salida); */
    }

    /**
     * @Route("/selectProductos", name="select_productos")
     * @Method("GET")
     */
    public function selectProductosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $term = $request->get('term');
        var_dump($term);
        die;
        $productos = $em->getRepository('AppBundle:Producto')->getProductosByTerm($term);
        $array = array();
        foreach ($productos as $prod) {
            $array[] = ['id' => $prod->getId(), 'text' => $prod->getCodigoNombre()];
        }
        return new Response(json_encode($productos));
    }

    /**
     * @Route("/addLoteProducto", name="add_loteproducto")
     * @Method("GET")
     */
    public function addLoteProductoAction(Request $request) {
        $prod_id = $request->get('prod_id');
        $nro = $request->get('nro');
        $vto = $request->get('vto');
        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository('AppBundle:Producto')->find($prod_id);
        $lote = new LoteProducto();
        $lote->setNroLote($nro);
        $lote->setFechaVencimiento(new \DateTime($vto));
        $lote->setProducto($producto);
        $em->persist($lote);
        $em->flush();
        return new Response(json_encode(array('id' => $lote->getId(), 'text' => $lote->__toString())));
    }

/// PARA VENTA RAPIDA
    /**
     * @Route("/getListaProductos", name="get_lista_productos")
     * @Method("GET")
     */
    public function getListaProductosAction() {
        $em = $this->getDoctrine()->getManager();
        $productos = $em->getRepository('AppBundle:Producto')->findByActivo(1);
        $partial = $this->renderView('AppBundle:Producto:_partial-lista-productos.html.twig',
                array('productos' => $productos));
        return new Response($partial);
    }

    /**
     * @Route("/productoListDatatables", name="producto_list_datatables")
     * @Method("POST")
     * @Template()
     */
    public function productoListDatatablesAction(Request $request) {
        // Set up required variables
        $this->entityManager = $this->getDoctrine()->getManager();        
        $this->repository = $this->entityManager->getRepository('AppBundle:Producto');
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->get('draw'));
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search');
            $orders = $request->get('order');
            $columns = $request->get('columns');

            $listaprecio = $request->get('listaprecio');
        }
        else // If the request is not a POST one, die hard
            die;

        // Process Parameters
        // Orders       

        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = "array or whatever is needed";

        // Get results from the Repository
        $results = $this->repository->getListDTData($start, $length, $orders, $search, $columns, $otherConditions = null, $listaprecio);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $this->repository->listcount();
        // Get total number of results
        $selected_objects_count = count($objects);
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        // Construct response
        $response = '{
            "draw": ' . $draw . ',
            "recordsTotal": ' . $total_objects_count . ',
            "recordsFiltered": ' . $filtered_objects_count . ',
            "data": [';

        $i = 0;

        foreach ($objects as $key => $producto) {
            $response .= '["';

            $j = 0;
            $nbColumn = count($columns);
            foreach ($columns as $key => $column) {
                // In all cases where something does not exist or went wrong, return -
                $responseTemp = $precioTemp = "-";
                $precio = $producto->getPrecios()[0];
                if ($precio !== null) {
                    $precioiva = $precio->getPrecio() * ( 1 + ( $producto->getIva()/100 ) );
                    $precioTemp = htmlentities(str_replace(array("\r\n", "\n", "\r", "\t"), ' ', round($precioiva,3) ));
                }
                switch ($column['name']) {                    
                    case 'nombre': {
                            // Do this kind of treatments if you suspect that the string is not JS compatible
                            $name = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $producto->getNombre()));
                            $responseTemp = "<a class='nombre-producto' data-id='".$producto->getId()."' data-precio='".$precioTemp."' href='javascript:void(0);'>".$name."</a>";                            
                            break;
                        }                    
                    case 'codigo': {
                            $codigo = $producto->getCodigo();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $codigo));
                            break;
                        } 
                    case 'precio': {
                            $responseTemp = $precioTemp;
                            break;
                        }                                       
                }

                // Add the found data to the json
                $response .= $responseTemp;

                if (++$j !== $nbColumn)
                    $response .= '","';
            }

            $response .= '"]';

            // Not on the last item
            if (++$i !== $selected_objects_count)
                $response .= ',';
        }

        $response .= ']}';

        // Send all this stuff back to DataTables
        return new Response($response);
    }

    /**
     * @Route("/productoIndexDatatables", name="producto_index_datatables")
     * @Method("POST")
     * @Template()
     */
    public function productoIndexDatatablesAction(Request $request) {
        // Set up required variables
        $this->entityManager = $this->getDoctrine()->getManager();        
        $this->repository = $this->entityManager->getRepository('AppBundle:Producto');
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->get('draw'));
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search');
            $orders = $request->get('order');
            $columns = $request->get('columns');

            $provId = $request->get('proveedor');
        }
        else // If the request is not a POST one, die hard
            die;

        // Process Parameters
        // Orders       

        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = "array or whatever is needed";

        $unidNeg = $this->get('session')->get('unidneg_id');

        // Get results from the Repository
        $results = $this->repository->getIndexDTData($start, $length, $orders, $search, $columns, $otherConditions = null, $provId);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $this->repository->indexCount();
        // Get total number of results
        $selected_objects_count = count($objects);
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        // Construct response
        $response = '{
            "draw": ' . $draw . ',
            "recordsTotal": ' . $total_objects_count . ',
            "recordsFiltered": ' . $filtered_objects_count . ',
            "data": [';

        $i = 0;

        foreach ($objects as $key => $producto) {
            $response .= '["';

            $j = 0;
            $nbColumn = count($columns);
            foreach ($columns as $key => $column) {
                // In all cases where something does not exist or went wrong, return -
                switch ($column['name']) {                    
                    case 'codigo': {
                            $codigo = $producto->getCodigo();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $codigo));
                            break;
                        } 
                    case 'nombre': {
                            // Do this kind of treatments if you suspect that the string is not JS compatible
                            $name = $producto->getNombre();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $name));                            
                            break;
                        }                    
                    case 'proveedor': {
                            $prov = $producto->getProveedor();
                            // This cannot happen if inner join is used
                            // However it can happen if left or right joins are used
                            if ($prov !== null) {
                                $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $prov->getNombre()));
                            }
                            break;
                        }
                    case 'rubro': {
                            $rubro = $producto->getRubro();
                            // This cannot happen if inner join is used
                            // However it can happen if left or right joins are used
                            if ($rubro !== null) {
                                $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $rubro->getNombre()));
                            }
                            break;
                        }
                    case 'costo': {
                            $costo = $producto->getCosto();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $costo));
                            break;
                        }
                    case 'activo': {                
                            $activo = ($producto->getActivo()) ? " checked='checked'" : "";           
                            $title = ($producto->getActivo()) ? " title='Activo'" : " title='Inactivo'";           
                            $responseTemp = "<input type='checkbox' disabled='disabled' ".$activo.$title. " />" ;                            
                           break;                        
                    }                                       
                    case 'actions': {
                            $user = $this->getUser();
                            $responseTemp = "<a href='" . $this->generateUrl('stock_producto_show', array('id' => $producto->getId())) . "' class='editar btn btnaction btn_folder' title='Ver' ></a>&nbsp;";                            
                            if ($user->getAccess($unidNeg, 'stock_producto_edit')) {
                                $linkEdit = "<a href='" . $this->generateUrl('stock_producto_edit', array('id' => $producto->getId())) . "' class='editar btn btnaction btn_pencil' title='Editar' ></a>&nbsp;";     
                                $responseTemp = $responseTemp . $linkEdit;
                            }                            
                            if ($user->getAccess($unidNeg, 'stock_producto_delete')) {
                                $linkDel = "<a href url='" . $this->generateUrl('stock_producto_delete', array('id' => $producto->getId())) . "' class='delete btn btnaction btn_trash' title='Borrar' ></a>&nbsp;";     
                                $responseTemp = $responseTemp . $linkDel;
                            }                             
                        }                                       
                }

                // Add the found data to the json
                $response .= $responseTemp;

                if (++$j !== $nbColumn)
                    $response .= '","';
            }

            $response .= '"]';

            // Not on the last item
            if (++$i !== $selected_objects_count)
                $response .= ',';
        }

        $response .= ']}';

        // Send all this stuff back to DataTables
        return new Response($response);
    }

}