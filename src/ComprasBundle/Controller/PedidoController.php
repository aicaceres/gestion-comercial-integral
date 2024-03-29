<?php

namespace ComprasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use ComprasBundle\Entity\Pedido;
use ComprasBundle\Entity\PedidoDetalle;
use ComprasBundle\Form\PedidoDetalleType;
use ComprasBundle\Form\PedidoType;
use ComprasBundle\Form\RecepcionType;
use ComprasBundle\Form\RecepcionDetalleType;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;

/**
 * @Route("/compras_pedido")
 */
class PedidoController extends Controller {

    /**
     * @Route("/", name="compras_pedido")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_pedido');
        $em = $this->getDoctrine()->getManager();
        $provId = $request->get('provId');
        $estado = $request->get('estado');

        /* $hoy = new \DateTime();
          $inicio = date("d-m-Y",strtotime($hoy->format('d-m-Y')."- 30 days"));
          $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
          $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y'); */
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $periodoEntrega = array('ini' => $request->get('entrega_desde'), 'fin' => $request->get('entrega_hasta'));
        //UtilsController::ultimoMesParaFiltro($request->get('entrega_desde'), $request->get('entrega_hasta'));
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(array('activo' => 1), array('nombre' => 'ASC'));
        $entities = $em->getRepository('ComprasBundle:Pedido')->findByCriteria($unidneg, $provId, $estado, $periodo['ini'], $periodo['fin'], $periodoEntrega['ini'], $periodoEntrega['fin']);

        return $this->render('ComprasBundle:Pedido:index.html.twig', array(
                'entities' => $entities, 'title' => 'Pedidos a Proveedores', 'tipo' => 'P',
                'proveedores' => $proveedores,
                'provId' => $provId,
                'estado' => $estado,
                'desde' => $periodo['ini'],
                'hasta' => $periodo['fin'],
                'entrega_desde' => $periodoEntrega['ini'],
                'entrega_hasta' => $periodoEntrega['fin']
        ));
    }

    /**
     * @Route("/new", name="compras_pedido_new")
     * @Method("GET")
     * @Template("ComprasBundle:Pedido:new.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido_new');
        $entity = new Pedido();
        $em = $this->getDoctrine()->getManager();
        // set numeracion
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
        $entity->setPedidoNro(sprintf("%08d", $equipo->getNroPedidoCompra() + 1));
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $entity->setUnidadNegocio($unidneg);
        $form = $this->createCreateForm($entity);
        return $this->render('ComprasBundle:Pedido:new.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Pedido entity.
     * @param Pedido $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Pedido $entity) {
        $form = $this->createForm(new PedidoType(), $entity, array(
            'action' => $this->generateUrl('compras_pedido_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/", name="compras_pedido_create")
     * @Method("POST")
     * @Template("ComprasBundle:Pedido:new.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido_new');
        $entity = new Pedido();
        $em = $this->getDoctrine()->getManager();
        // set Unidad de negocio
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $entity->setUnidadNegocio($unidneg);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                //$datos = $request->get('comprasbundle_pedido');
                //if( isset($datos['cerrado']) ) $entity->setEstado('PENDIENTE');
                // set numeracion
                $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                $entity->setPedidoNro(sprintf("%08d", $equipo->getNroPedidoCompra() + 1));
                /* Guardar ultimo nro */
                $equipo->setNroPedidoCompra($equipo->getNroPedidoCompra() + 1);
                $em->persist($entity);
                $em->persist($equipo);
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirectToRoute('compras_pedido_edit', array('id' => $entity->getId()));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
                return $this->render('ComprasBundle:Pedido:edit.html.twig', array(
                        'entity' => $entity,
                        'form' => $form->createView()
                ));
            }
        }
    }

    /**
     * @Route("/{id}/edit", name="compras_pedido_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Pedido')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Pedido.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('ComprasBundle:Pedido:edit.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Pedido entity.
     * @param Pedido $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Pedido $entity) {
        $form = $this->createForm(new PedidoType(), $entity, array(
            'action' => $this->generateUrl('compras_pedido_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="compras_pedido_update")
     * @Method("PUT")
     * @Template("ComprasBundle:Pedido:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido_edit');
        $datos = $request->get('comprasbundle_pedido');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Pedido')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Pedido.');
        }
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            try {
                if (isset($datos['cerrado'])) {
                    $entity->setEstado('PENDIENTE');
                }
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('compras_pedido'));
            }
            catch (\Exception $ex) {
                $msg = $ex->getTraceAsString();
                $this->addFlash('error', $msg);
            }
        }
        return $this->render('ComprasBundle:Pedido:edit.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/{id}/show", name="compras_pedido_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Pedido')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Pedido.');
        }
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('ComprasBundle:Pedido:show.html.twig', array(
                'entity' => $entity,
                'delete_form' => $deleteForm->createView()
        ));
    }

    /**
     * DELETE PEDIDOS
     */

    /**
     * @Route("/deleteAjax/{id}", name="compras_pedido_delete_ajax")
     * @Method("POST")
     */
    public function deleteAjaxAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido_delete');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Pedido')->find($id);
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
     * @Route("/delete/{id}", name="compras_pedido_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido_delete');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ComprasBundle:Pedido')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('No se encuentra el Pedido.');
            }
            try {
                $em->remove($entity);
                $em->flush();
                $this->addFlash('success', 'El pedido fue eliminado!');
            }
            catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('error', 'Este dato no puede ser eliminado porque está siendo utilizado en el sistema.');
            }
        }
        return $this->redirectToRoute('compras_pedido');
    }

    /**
     * Creates a form to delete a Pedido entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('compras_pedido_delete', array('id' => $id)))
                ->setMethod('DELETE')
                ->getForm();
    }

    /*
     * AJAX PEDIDOS
     */



    /**
     * IMPRESION DE PEDIDOS
     */

    /**
     * @Route("/printComprasPedido.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_compras_pedido")
     * @Method("POST")
     */
    public function printComprasPedidoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $tipo = $request->get('tipo');
        $proveedorId = $request->get('proveedorid');
        $estado = $request->get('estado');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $edesde = $request->get('edesde');
        $ehasta = $request->get('ehasta');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $textoFiltro = array($proveedor ? $proveedor->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '', $estado ? $estado : 'Todos', $edesde ? $edesde : '', $ehasta ? $ehasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render(
            'ComprasBundle:Pedido:pdf-pedidos.pdf.twig',
            array(
                'items' => json_decode($items), 'filtro' => $textoFiltro, 'tipo' => $tipo,
                'search' => $request->get('searchterm')
            ),
            $response
        );

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array(
            'content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_compras_pedidos' . $hoy->format('dmY_Hi') . '.pdf'
        ));
    }

    /**
     * @Route("/{id}/imprimir.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="compras_pedido_print")
     * @Method("GET")
     */
    public function printAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Pedido')->find($id);
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        if ($request->get('valorizado')) {
            $this->render('ComprasBundle:Pedido:pdf-pedido-valorizado.pdf.twig', array('entity' => $entity), $response);
        }
        else {
            $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);
            $logo = __DIR__ . '/../../../web/assets/images/logo_comprobante.png';
            $this->render('ComprasBundle:Pedido:pedido-proveedor.pdf.twig',
                array('entity' => $entity, 'logo' => $logo, 'empresa' => $empresa), $response);
        }
        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array(
            'content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=pedido' . $entity->getNroPedido() . '.pdf'
        ));
        /*  return $this->render('AppBundle:Pedido:print.html.twig', array(
          'entity' => $entity)); */
    }

    /**
     * RECEPCION DE MERCADERIAS
     */

    /**
     * @Route("/recepcion", name="compras_pedido_recepcion")
     * @Method("GET")
     * @Template()
     */
    public function recepcionPedidoAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_pedido_recepcion');
        $em = $this->getDoctrine()->getManager();
        $provId = $request->get('provId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(array('activo' => 1), array('nombre' => 'ASC'));
        $entities = $em->getRepository('ComprasBundle:Pedido')->findByCriteria($unidneg, $provId, $desde, $hasta);

        return $this->render('ComprasBundle:Pedido:index.html.twig', array(
                'entities' => $entities, 'title' => 'Recepción de Pedidos', 'tipo' => 'R',
                'proveedores' => $proveedores,
                'provId' => $provId,
                'desde' => $desde,
                'hasta' => $hasta
        ));
    }

    /**
     * @Route("/recepcion/new/{id}", name="compras_pedido_recepcion_new")
     * @Method("GET")
     * @Template("ComprasBundle:Pedido:recepcion.html.twig")
     */
    public function recepcionNewAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido_recepcion');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Pedido')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Pedido.');
        }
        foreach ($entity->getDetalles() as $detalle) {
            if (!$detalle->getEntregado())
                $detalle->setEntregado($detalle->getCantidadTotal());
        }
        $editForm = $this->createRecepcionForm($entity);
        /* $depositos = $em->getRepository('AppBundle:Deposito')->findBy(array("central" => "1", "unidadNegocio"=> $this->get('session')->get('unidneg_id') ));
          if(!$depositos){
          $this->addFlash('alert', 'Debe registrar un depósito como Central para la recepción de mercaderías.');
          } */
        return $this->render('ComprasBundle:Pedido:recepcion.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView()
        ));
    }

    /**
     * Creates a form to edit a Pedido entity.
     * @param Pedido $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRecepcionForm(Pedido $entity) {
        $form = $this->createForm(new RecepcionType(), $entity, array(
            'action' => $this->generateUrl('compras_pedido_recepcion_create', array('id' => $entity->getId())),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/{id}/recepcion", name="compras_pedido_recepcion_create")
     * @Method("POST")
     * @Template("ComprasBundle:Pedido:recepcion.html.twig")
     */
    public function recepcionCreateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido_recepcion');
        $recepcion = $request->get('comprasbundle_recepcion');
        $recibido = array_key_exists("recibido", $recepcion);
        $nuevo = array_key_exists("generarnuevo", $recepcion);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $pedido = $em->getRepository('ComprasBundle:Pedido')->find($id);
        $recepcionForm = $this->createRecepcionForm($pedido);
        $recepcionForm->handleRequest($request);
        if ($recepcionForm->isValid()) {
            try {
                if ($recibido) {
                    $deposito = $pedido->getDeposito();
                    if ($nuevo) {
                        $clone = new Pedido();
                        $clone->setProveedor($pedido->getProveedor());
                        $clone->setDeposito($pedido->getDeposito());
                        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                        $clone->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                        $clone->setPedidoNro(sprintf("%08d", $equipo->getNroPedidoCompra() + 1));
                        /* Guardar ultimo nro */
                        $equipo->setNroPedidoCompra($equipo->getNroPedidoCompra() + 1);
                        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
                        $clone->setUnidadNegocio($unidneg);
                    }
                    foreach ($pedido->getDetalles() as $detalle) {
                        if (!$detalle->getEntregado()) {
                            $detalle->setEntregado($detalle->getCantidadTotal());
                        }
                        if (!$detalle->getCantidadOriginal()) {
                            $detalle->setCantidadOriginal($detalle->getCantidadTxt());
                        }
                        if (!$detalle->getCantidadTotalOriginal()) {
                            $detalle->setCantidadTotalOriginal($detalle->getCantidadTotal());
                        }
                        $cantFaltante = $detalle->getFaltanteDelItem();
                        if ($nuevo && ($cantFaltante > 0)) {
                            //hay faltante y se debe genera nuevo
                            $newdet = new PedidoDetalle();
                            $newdet->setProducto($detalle->getProducto());
                            $newdet->setCantidad($cantFaltante);
                            $clone->addDetalle($newdet);
                        }
                        // Ingresar al stock
                        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                        if ($stock) {
                            $stock->setCantidad($stock->getCantidad() + $detalle->getEntregado());
                        }
                        else {
                            $stock = new Stock();
                            $stock->setProducto($detalle->getProducto());
                            $stock->setDeposito($deposito);
                            $stock->setCantidad($detalle->getEntregado());
                        }
                        $em->persist($stock);
                        // Cargar movimiento
                        $movim = new StockMovimiento();
                        $movim->setFecha($pedido->getFechaEntrega());
                        $movim->setTipo('compras_pedido');
                        $movim->setSigno('+');
                        $movim->setMovimiento($id);
                        $movim->setProducto($detalle->getProducto());
                        $movim->setCantidad($detalle->getEntregado());
                        $movim->setDeposito($deposito);
                        $em->persist($movim);
                        $em->flush();
                    }
                    $pedido->setEstado('RECIBIDO');
                    if ($nuevo) {
                        $em->persist($clone);
                        $em->persist($equipo);
                        $em->flush();
                    }
                }
                $em->persist($pedido);
                $em->flush();
                $em->getConnection()->commit();
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('error', $ex->getMessage());
                $editForm = $this->createRecepcionForm($pedido);
                return $this->render('ComprasBundle:Pedido:recepcion.html.twig', array(
                        'entity' => $pedido,
                        'form' => $editForm->createView()
                ));
            }

            return $this->redirect($this->generateUrl('compras_pedido_recepcion'));
        }
        foreach ($pedido->getDetalles() as $detalle) {
            if (!$detalle->getEntregado())
                $detalle->setEntregado($detalle->getCantidadTotal());
        }
    }

    /**
     * @Route("/pendientes", name="compras_pedidos_pendientes")
     * @Method("POST")
     * @Template()
     */
    public function pedidosPendientesAction() {
        $em = $this->getDoctrine()->getManager();
        $pedidos = $em->getRepository('ComprasBundle:Pedido')->getPendientes();
        $partial = $this->renderView(
            'ComprasBundle:Pedido:_partial-pendientes.html.twig',
            array('pedidos' => $pedidos)
        );
        return new Response($partial);
    }

    // MODAL PARA PEDIDO

    /**
     * @Route("/ajax/renderAddItemPedido", name="compras_render_item_pedido")
     * @Method("GET")
     */
    public function renderAddItemPedidoAction(Request $request) {
        $id = $request->get('id');
        if ($id) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ComprasBundle:PedidoDetalle')->find($id);
            if (!$entity->getPrecio()) {
                $entity->setPrecio($entity->getProducto()->getCosto());
            }
        }
        else {
            $entity = new PedidoDetalle();
        }
        $form = $this->createForm(new PedidoDetalleType(), $entity, array(
            'action' => $this->generateUrl('compras_item_pedido_create'),
            'method' => 'PUT',
        ));
        $html = $this->renderView(
            'ComprasBundle:Pedido:partial-item-pedido.html.twig',
            array('entity' => $entity, 'form' => $form->createView())
        );
        return new Response($html);
    }

    /**
     * @Route("/ajax/createItemPedido", name="compras_item_pedido_create")
     * @Method("PUT")
     * @Template()
     */
    public function createItemPedido(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        if ($id) {
            $entity = $em->getRepository('ComprasBundle:PedidoDetalle')->find($id);
        }
        else {
            $entity = new PedidoDetalle();
            $pedId = $request->get('pedidoid');
            $em = $this->getDoctrine()->getManager();
            $pedido = $em->getRepository('ComprasBundle:Pedido')->find($pedId);
            $entity->setPedido($pedido);
        }
        $form = $this->createForm(new PedidoDetalleType(), $entity, array(
            'action' => $this->generateUrl('compras_item_pedido_create'),
            'method' => 'PUT',
        ));
        $form->handleRequest($request);
        $msg = 'No se pudo realizar esta operación.' . chr(13) . ' Verifique que no exista el valor que está intentando ingresar.';
        if ($form->isValid()) {
            try {
                $em->persist($entity);
                $em->flush();
                $msg = 'OK';
            }
            catch (\Exception $ex) {
                $msg = $ex->getMessage();
            }
        }
        $partial = $this->renderView(
            'ComprasBundle:Pedido:partial-items.html.twig',
            array('item' => $entity)
        );
        $result = array('msg' => $msg, 'tr' => $partial);
        return new JsonResponse($result);
        /* $result = array('msg'=>$msg, 'id'=>$entity->getId(), 'prod'=>$entity->getProducto()->getCodigoNombre(), 'cant'=> $entity->getCantidadItemTxt(),
          'stock'=>$entity->getProducto()->getStockActualxDeposito($entity->getPedido()->getDeposito()->getId()).' '.$entity->getProducto()->getUnidadMedida()->getNombre(),
          'total'=>$entity->getCantidadItemTotal().' '.$entity->getProducto()->getUnidadMedida()->getNombre());
          return new JsonResponse($result); */
    }

    /**
     * @Route("/ajax/deleteItemPedido", name="compras_item_pedido_delete")
     * @Method("POST")
     */
    public function deleteItemPedidoAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('ComprasBundle:PedidoDetalle')->find($id);
        try {
            $em->remove($item);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Exception $ex) {
            $msg = $ex->getMessage();
        }
        return new Response($msg);
    }

    /**
     * @Route("/getPartialItems", name="get_compras_partial_items")
     * @Method("GET")
     */
    public function getPartialItemsAction(Request $request) {
        $id = $request->get('id');
        $dep = $request->get('dep');
        $em = $this->getDoctrine()->getManager();
        $pedido = $em->getRepository('ComprasBundle:Pedido')->find($id);
        $deposito = $em->getRepository('AppBundle:Deposito')->find($dep);
        $pedido->setDeposito($deposito);
        $em->persist($pedido);
        $em->flush();
        $partial = $this->renderView(
            'ComprasBundle:Pedido:partial-items.html.twig',
            array('entity' => $pedido)
        );
        return new Response($partial);
    }

    // MODAL PARA RECEPCION DE PEDIDO

    /**
     * @Route("/ajax/renderEditItemRecepcion/{id}", name="compras_render_edit_item_recepcion")
     * @Method("GET")
     */
    public function renderEditItemRecepcionAction($id) {
        $em = $this->getDoctrine()->getManager();
        if ($id == 0) {
            $entity = new PedidoDetalle();
        }
        else {
            $entity = $em->getRepository('ComprasBundle:PedidoDetalle')->find($id);
            if (!$entity->getEntregado()) {
                $entity->setEntregado($entity->getCantidad());
            }
            else {
                if ($entity->getBulto()) {
                    $entity->setEntregado($entity->getEntregado() / $entity->getCantidadxBulto());
                }
            }
        }
        $form = $this->createForm(new RecepcionDetalleType(), $entity, array(
            'action' => $this->generateUrl('compras_item_recepcion_update', array('id' => $id)),
            'method' => 'PUT',
        ));
        $html = $this->renderView(
            'ComprasBundle:Pedido:partial-item-recepcion.html.twig',
            array('entity' => $entity, 'form' => $form->createView())
        );
        return new Response($html);
    }

    /**
     * @Route("/ajax/updateItemRecepcion/{id}", name="compras_item_recepcion_update")
     * @Method("PUT")
     * @Template()
     */
    public function updateItemRecepcion(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $recform = $request->get('comprasbundle_recepciondetalle');
        if ($id == 0) {
            $entity = new PedidoDetalle();
            $ped = $em->getRepository('ComprasBundle:Pedido')->find($request->get('pedidoid'));
            $entity->setPedido($ped);
            $prod = $em->getRepository('AppBundle:Producto')->find($recform['producto']);
            $entity->setProducto($prod);
            $entity->setCantidad(0);
            $em->persist($entity);
            $em->flush();
        }
        else {
            $entity = $em->getRepository('ComprasBundle:PedidoDetalle')->find($id);
        }
        $bulto = array_key_exists("bulto", $recform);
        $cantxbulto = empty($recform['cantidadxBulto']) ? 0 : $recform['cantidadxBulto'];
        if ($bulto) {
            $entregado = $recform['entregado'] * $cantxbulto;
            // $costo = $detalle['precio'] / $detalle['cantidadxBulto'];
        }
        else {
            $entregado = $recform['entregado'];
            //$costo = $detalle['precio'];
        }
        $cantOrig = $entity->getCantidadTxt();
        $cantTotOrig = $entity->getCantidadTotal();
        $form = $this->createForm(new RecepcionDetalleType(), $entity, array(
            'action' => $this->generateUrl('compras_item_recepcion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        //$cant_original = array('cantidad'=>$entity->getCantidad(), 'bulto'=>$entity->getBulto(), 'cantxbulto'=>$entity->getCantidadxBulto());
        $form->handleRequest($request);
        $msg = 'No se pudo realizar esta operación.' . chr(13) . ' Intente nuevamente.';
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $entity->setEntregado($entregado);
                if (!$entity->getCantidadOriginal()) {
                    $entity->setCantidadOriginal($cantOrig);
                }
                if (!$entity->getCantidadTotalOriginal()) {
                    $entity->setCantidadTotalOriginal($cantTotOrig);
                }
                // $entity->setCantidad( $cant_original['cantidad'] );
                $entity->setBulto($bulto);
                $entity->setCantidadxBulto($cantxbulto);
                $em->persist($entity);
                $em->flush();
                $msg = 'OK';
            }
            catch (\Exception $ex) {
                //$msg = UtilsController::errorMessage($ex->getErrorCode());
                $msg = $ex->getMessage();
            }
        }
        else {
            $msg = "Formulario inválido";
        }
        $partial = $this->renderView(
            'ComprasBundle:Pedido:tr_item_recepcion.html.twig',
            array('item' => $entity)
        );
        $result = array('msg' => $msg, 'tr' => $partial);
        return new JsonResponse($result);
    }

    /**
     * @Route("/ajax/renderAddItemRecepcion", name="compras_render_item_recepcion")
     * @Method("GET")
     */
    public function renderAddItemRecepcionAction() {
        $entity = new PedidoDetalle();
        $form = $this->createForm(new RecepcionDetalleType(), $entity, array(
            'action' => $this->generateUrl('compras_item_pedido_create'),
            'method' => 'PUT',
        ));
        $html = $this->renderView(
            'ComprasBundle:Pedido:partial-item-recepcion.html.twig',
            array('entity' => $entity, 'form' => $form->createView())
        );
        return new Response($html);
    }

    /**
     * @Route("/ajax/setItemDespachoNoEntregado", name="compras_item_despacho_noentregado")
     * @Method("POST")
     */
    public function setItemDespachoNoEntregadoAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('ComprasBundle:PedidoDetalle')->find($id);
        try {
            $item->setEntregado(0);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Exception $ex) {
            $msg = $ex->getMessage();
        }
        return new Response($msg);
    }

    /**
     * @Route("/informe/pendientes", name="compras_informe_pendientes")
     * @Method("GET")
     * @Template()
     */
    public function pendientesAction(Request $request) {
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
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(array('activo' => '1'), array('nombre' => 'ASC'));
        $productos = $em->getRepository('AppBundle:Producto')->findBy(array(), array('nombre' => 'ASC'));

        $entities = $em->getRepository('ComprasBundle:Pedido')->findPendientesEntregaByCriteria($unidneg, $provId, $prodId, $periodo);

        return $this->render('ComprasBundle:Informe:pendientes.html.twig', array(
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
     * @Route("/printInformePendientes.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_informe_pendientes")
     * @Method("POST")
     */
    public function printInformePendientesAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $tipo = $request->get('tipo');
        $proveedorId = $request->get('proveedorid');
        $productoId = $request->get('productoid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $producto = $em->getRepository('AppBundle:Producto')->find($productoId);
        $textoFiltro = array(
            $proveedor ? $proveedor->getNombre() : 'TODOS', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '',
            $producto ? $producto->getCodigoNombre() : 'TODOS'
        );

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render(
            'ComprasBundle:Informe:pdf-pendientes.pdf.twig',
            array(
                'items' => json_decode($items), 'filtro' => $textoFiltro, 'tipo' => $tipo,
                'search' => $request->get('searchterm')
            ),
            $response
        );

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array(
            'content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=informe_productos_pendientes_' . $hoy->format('dmY_Hi') . '.pdf'
        ));
    }

    /**
     * @Route("/cargarProductosBajominimo", name="cargar_productos_bajominimo")
     * @Method("Get")
     */
    public function cargarProductosBajominimoAction(Request $request) {
        $id = $request->get('id');
        $provId = $request->get('proveedor');
        $depId = $request->get('deposito');
        $msg = 'No hay productos por bajo del stock para agregar';
        $em = $this->getDoctrine()->getManager();
        $pedido = $em->getRepository('ComprasBundle:Pedido')->find($id);
        $productos = $em->getRepository('AppBundle:Producto')->findBajoMinimo($provId, $depId);
        if ($productos) {
            foreach ($productos as $prod) {
                $producto = $em->getRepository('AppBundle:Producto')->findOneByCodigo($prod['codigo']);
                $cargar = true;
                foreach ($pedido->getDetalles() as $det) {
                    if ($det->getProducto()->getId() === $producto->getId()) {
                        $cargar = false;
                        break;
                    }
                }
                if ($cargar) {
                    $entity = new PedidoDetalle();
                    $entity->setPedido($pedido);
                    $entity->setProducto($producto);
                    $cantidad = $prod['stockMinimo'] - $prod['stockActual'];
                    $entity->setCantidad($cantidad);
                    $entity->setPrecio($prod['costo']);
                    $pedido->addDetalle($entity);
                    $msg = 'Los productos fueron agregados al pedido!';
                }
            }
        }

        if ($provId != $pedido->getProveedor()->getId()) {
            $pedido->setProveedor($em->getRepository('ComprasBundle:Proveedor')->find($provId));
        }
        if ($depId != $pedido->getDeposito()->getId()) {
            $pedido->setDeposito($em->getRepository('AppBundle:Deposito')->find($depId));
        }
        $em->persist($pedido);
        $em->flush();

        $this->addFlash('success', $msg);

        return $this->redirectToRoute('compras_pedido_edit', array('id' => $pedido->getId()));
    }

}