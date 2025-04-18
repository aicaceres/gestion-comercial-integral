<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\Cobro;
use VentasBundle\Form\CobroType;
use VentasBundle\Entity\CobroDetalle;
use ConfigBundle\Entity\BancoMovimiento;
use VentasBundle\Entity\FacturaElectronica;
use VentasBundle\Afip\src\Afip;
use Endroid\QrCode\QrCode;
// $afip = new Afip(array('CUIT'=> '30714151971'));

/**
 * @Route("/cobroVentas")
 */
class CobroController extends Controller {

    /**
     * @Route("/prueba", name="prueba_tickeadora")
     * @Method("GET")
     * @Template()
     */
    public function pruebaAction(Request $request) {
        return $this->render('VentasBundle:Cobro:prueba.html.twig');
    }

    /**
     * @Route("/", name="ventas_cobro")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $session = $this->get('session');
        $unidneg = $session->get('unidneg_id');
        $user = $this->getUser();
        UtilsController::haveAccess($user, $unidneg, 'ventas_factura');
        $em = $this->getDoctrine()->getManager();
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        if ($user->getAccess($unidneg, 'ventas_factura_own') && !$user->isAdmin($unidneg)) {
            $id = $user->getId();
            $owns = true;
        }
        else {
            $id = $request->get('userId');
            $owns = false;
        }

        $entities = $em->getRepository('VentasBundle:Cobro')->findByCriteria($unidneg, $periodo['ini'], $periodo['fin'], $id);
        $users = $em->getRepository('VentasBundle:Cobro')->getUsers();

        $ventas = $em->getRepository('VentasBundle:Venta')->findPorCobrarByCriteria($unidneg, $periodo['ini'], $periodo['fin'], $id);
        if(!$session->get('caja')){
            $this->addFlash('error', 'Este equipo ' . $session->get('hostname') . ' no está definido como caja!');
        }

        return $this->render('VentasBundle:Cobro:index.html.twig', array(
                'entities' => $entities,
                'ventas' => $ventas,
                'id' => $id,
                'owns' => $owns,
                'users' => $users,
                'desde' => $periodo['ini'],
                'hasta' => $periodo['fin'],
                'selectedtab' => $request->get('selectedtab'),
                'printpdf' => $request->get('printpdf')
        ));
    }

    /**
     * @Route("/facturarVenta/{id}", name="ventas_cobro_facturar")
     * @Method("GET")
     * @Template()
     */
    public function facturarVentaAction(Request $request, $id) {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');
        $em = $this->getDoctrine()->getManager();

        $referer = $request->headers->get('referer');
        if ($this->checkCajaAbierta($em,$session)) {
            return $this->redirect($referer);
        }

        $entity = new Cobro();

        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if ($param) {
            // ultimo nro de operacion de cobro
            $entity->setNroOperacion($param->getUltimoNroOperacionCobro() + 1);
            $cantidadItemsParaFactura = $param->getCantidadItemsParaFactura();
        }
        else {
            $this->addFlash('error', 'No se ha podido acceder a la parametrización. Intente nuevamente o contacte a servicio técnico.');
            return $this->redirect($referer);
        }

        $venta = $em->getRepository('VentasBundle:Venta')->find($id);
        if (!$venta) {
            $this->addFlash('error', 'No se encuentra este registro de venta.');
            return $this->redirect($referer);
        }

        $entity->setVenta($venta);
        $entity->setCliente($venta->getCliente());
        $entity->setNombreCliente($venta->getNombreCliente());
        if ($venta->getCliente()->getConsumidorFinal()) {
            $tipoDoc = $em->getRepository('ConfigBundle:Parametro')->filterByCodigo(96, 'tipo-documento');
            $entity->setTipoDocumentoCliente($tipoDoc);
            $entity->setNroDocumentoCliente(1);
        }
        $entity->setMoneda($venta->getMoneda());
        $entity->setFormaPago($venta->getFormaPago());

        $form = $this->createCreateForm($entity);
        return $this->render('VentasBundle:CobroVenta:form-facturar-venta.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
                'cantidadItemsParaFactura' => $cantidadItemsParaFactura,
        ));
    }

    /**
     * Creates a form to create a Venta entity.
     * @param Cobro $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Cobro $entity) {
        $form = $this->createForm(new CobroType(), $entity, array(
            'action' => $this->generateUrl('ventas_cobro_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/", name="ventas_cobro_create")
     * @Method("POST")
     * @Template("VentasBundle:Cobro:new.html.twig")
     */
    public function createAction(Request $request) {
        $datos = $request->get('ventasbundle_cobro');
        $ventaId = $request->get('ventasbundle_cobro_venta');
        $cobroId = $request->get('ventasbundle_cobro_id');
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');
        $em = $this->getDoctrine()->getManager();
        $response = array('res' => 'OK', 'msg' => '', 'id' => null);

        // Verificar si la caja está abierta
        $apertura = $em->getRepository('VentasBundle:CajaApertura')->find($session->get('caja')['apertura']);
        if (!$apertura) {
            $response['res'] = 'ERROR';
            $response['msg'] = 'La caja está cerrada. Debe realizar la apertura para iniciar cobros';
            return new JsonResponse($response);
        }

        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));

        if ($cobroId) {
            $entity = $em->getRepository('VentasBundle:Cobro')->find($cobroId);
        }
        else {
            $entity = new Cobro();
            $entity->setFechaCobro(new \DateTime());
        }

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $venta = $em->getRepository('VentasBundle:Venta')->find($ventaId);
                $entity->setVenta($venta);
                $cliente = $entity->getVenta()->getCliente();
                $entity->setCliente($cliente);
                $entity->setMoneda($entity->getVenta()->getMoneda());
                $entity->setCotizacion($entity->getVenta()->getCotizacion());
                $formapago = $em->getRepository('ConfigBundle:FormaPago')->find($request->get('select_formapago'));
                if ($entity->getVenta()->getFormaPago() !== $formapago) {
                    $venta->setFormaPago($formapago);
                    $venta->setDescuentoRecargo($formapago->getPorcentajeRecargo());
                    $em->persist($venta);
                }
                $entity->setFormaPago($formapago);

                if ($cliente->getConsumidorFinal()) {
                    $entity->setNombreCliente($request->get('ventasbundle_nombreCliente') ? $request->get('ventasbundle_nombreCliente') : 'CONSUMIDOR FINAL');
                    if ($request->get('ventasbundle_tipoDocumentoCliente')) {
                        $tipoDoc = $em->getRepository('ConfigBundle:Parametro')->find($request->get('ventasbundle_tipoDocumentoCliente'));
                        $entity->setTipoDocumentoCliente($tipoDoc);
                    }
                    else {
                        $tipoDoc = $em->getRepository('ConfigBundle:Parametro')->filterByCodigo(96, 'tipo-documento');
                        $entity->setTipoDocumentoCliente($tipoDoc);
                    }
                    $entity->setNroDocumentoCliente($request->get('ventasbundle_nroDocumentoCliente') ? $request->get('ventasbundle_nroDocumentoCliente') : 1);
                }

                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
                $entity->setUnidadNegocio($unidneg);

                if ($param && !$entity->getNroOperacion()) {
                    // cargar datos parametrizados por defecto
                    $entity->setNroOperacion($param->getUltimoNroOperacionCobro() + 1);
                    $param->setUltimoNroOperacionCobro($entity->getNroOperacion());
                    $em->persist($param);
                }

                // completar datos de detalles
                $efectivo = true;
                $transferencias = array();
                if (count($entity->getDetalles()) == 0) {
                    if ($formapago->getTipoPago() === 'CTACTE') {
                        // insertar un detalle para ctacte
                        $detalle = new CobroDetalle();
                        $detalle->setTipoPago('CTACTE');
                        $detalle->setMoneda($entity->getMoneda());
                        $detalle->setImporte($venta->getMontoTotal());
                        $detalle->setCajaApertura($apertura);
                        $entity->addDetalle($detalle);
                        // $saldo = round($impTotal, 2);
                        $efectivo = false;
                    }
                }
                else {
                    $detalles = array_values($datos['detalles']);
                    foreach ($entity->getDetalles() as $key => $detalle) {
                        $detalle->setCajaApertura($apertura);
                        if (!$detalle->getMoneda()) {
                            $detalle->setMoneda($entity->getMoneda());
                        }
                        $tipoPago = $detalle->getTipoPago();
                        if ($tipoPago !== 'CHEQUE') {
                            $detalle->setChequeRecibido(null);
                        }
                        else {
                            $detalle->getChequeRecibido()->setTomado(new \DateTime());
                        }
                        if ($tipoPago !== 'TARJETA') {
                            $detalle->setDatosTarjeta(null);
                        }
                        if ($tipoPago !== 'EFECTIVO') {
                            $efectivo = false;
                        }
                        if ($tipoPago === 'TRANSFERENCIA') {
                            // cargar movimiento de credito bancario
                            $movBanco = new BancoMovimiento();
                            $banco = $em->getRepository('ConfigBundle:Banco')->find($detalles[$key]['bancoTransferencia']);
                            $movBanco->setBanco($banco);
                            $cuenta = $em->getRepository('ConfigBundle:CuentaBancaria')->find($detalles[$key]['cuentaTransferencia']);
                            $movBanco->setCuenta($cuenta);
                            $movBanco->setNroMovimiento($detalles[$key]['nroMovTransferencia']);
                            $movBanco->setConciliado(false);
                            $movBanco->setImporte($detalles[$key]['importe']);
                            $movBanco->setFechaAcreditacion(new \DateTime());
                            $movBanco->setFechaCarga(new \DateTime());
                            $tipoMov = $em->getRepository('ConfigBundle:BancoTipoMovimiento')->findOneByNombre('CREDITO');
                            $movBanco->setTipoMovimiento($tipoMov);
                            $movBanco->setCobroDetalle($detalle);
                            $movBanco->setObservaciones('Transferencia por cobro de ventas - Op. #'.$entity->getNroOperacion());
                            $em->persist($movBanco);
                        }
                    }
                }

                $entity->getVenta()->setEstado('COBRADO');
                $entity->setEstado('CREADO');
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();

                $response['res'] = 'OK';
                $response['msg'] = 'El registro del cobro se han guardado correctamente!';
                $response['id'] = $entity->getId();
                return new JsonResponse($response);
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $response['res'] = 'ERROR';
                $response['msg'] = $ex->getMessage();
                return new JsonResponse($response);
            }
        }

        $response['res'] = 'ERROR';
        $response['msg'] = 'Formulario inválido';
        return new JsonResponse($response);
    }

    /**
     * @Route("/{id}/edit", name="ventas_cobro_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Cobro')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Cobro.');
        }
        $editForm = $this->createEditForm($entity);

        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));

        return $this->render('VentasBundle:CobroVenta:form-facturar-venta.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView(),
                'cantidadItemsParaFactura' => $param->getCantidadItemsParaFactura(),
        ));
    }

    private function createEditForm(Cobro $entity) {
        $form = $this->createForm(new CobroType(), $entity, array(
            'action' => $this->generateUrl('ventas_cobro_create', array('id' => $entity->getVenta()->getId())),
            'method' => 'POST',
            'attr' => array('type' => ''),
        ));
        return $form;
    }

    /**
     * @Route("/{id}/showVenta", name="ventas_cobro_showventa")
     * @Method("GET")
     * @Template()
     */
    public function showVentaAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:FacturaElectronica')->find($id);
        $venta = $entity->getCobro()->getVenta();
        return $this->redirectToRoute('ventas_venta_show', array('id' => $venta->getId()));
    }

    /**
     * @Route("/{id}/show", name="ventas_cobro_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Cobro')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cobro entity.');
        }
        return $this->render('VentasBundle:Cobro:show.html.twig', array(
                'entity' => $entity
        ));
    }

    /**
     * @Route("/ventasPorCobrar", name="ventas_por_cobrar")
     */
    public function ventasPorCobrarAction() {
        $em = $this->getDoctrine()->getManager();
        $ventas = $em->getRepository('VentasBundle:Venta')->findBy(array('estado' => 'PENDIENTE'), array('nroOperacion' => 'ASC'));

        return $this->render('VentasBundle:Cobro:_partial-ventas-por-cobrar.html.twig', array(
                'ventas' => $ventas
        ));
    }

    /**
     * @Route("/ventasViewDetail", name="ventas_view_detail")
     */
    public function ventasViewDetailAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $venta = $em->getRepository('VentasBundle:Venta')->find($request->get('id'));

        return $this->render('VentasBundle:Cobro:_partial-view-detalle.html.twig', array(
                'venta' => $venta
        ));
    }

    /**
     * @Route("/getAutocompleteFacturas", name="get_autocomplete_facturas")
     * @Method("GET")
     */
    public function getAutocompleteFacturasAction(Request $request) {
        $cliente = $request->get('id');
        $term = $request->get('searchTerm');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('VentasBundle:Cobro')->filterByCliente($cliente, $term);
        $facturas = array();
        if ($results) {
            foreach ($results as $row) {
                $facturas[] = array('id' => $row->getId(), 'text' => $row->getSelectComprobanteTxt());
//                    , 'cargaritems' => $row->getNotaDebCred());
            }
        }
        return new JsonResponse($facturas);
    }

    /**
     * @Route("/getItemsComprobante", name="get_items_comprobante")
     * @Method("GET")
     *
     */
    public function getItemsComprobanteAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $comprobante = $em->getRepository('VentasBundle:FacturaElectronica')->find($id);
        $items = array();
        if ($comprobante) {
            $detalle = ($comprobante->getCobro()) ? $comprobante->getCobro()->getVenta()->getDetalles() : $comprobante->getNotaDebCred()->getDetalles();
            $dtorec = ($comprobante->getCobro()) ? $comprobante->getCobro()->getVenta() : $comprobante->getNotaDebCred();
            foreach ($detalle as $row) {

                $text = $row->getProducto()->getNombre() . ' | COD ' . $row->getProducto()->getCodigo();
                $items[] = array(
                    'id' => $row->getProducto()->getId(),
                    'text' => $text,
                    'cant' => $row->getCantidad(),
                    'comodin' => $row->getProducto()->getComodin(),
                    'precio' => $row->getPrecio(),
                    'alicuota' => $row->getAlicuota(),
                    'bajominimo' => 0
                );
            }
        }
        return new JsonResponse(array('items' => $items, 'dtorec' => $dtorec->getDescuentoRecargo()));
    }

    /**
     * @Route("/getTiposComprobanteValido", name="get_tipos_comprobante_valido")
     * @Method("GET")
     *
     */
    public function getTiposComprobanteValido(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $comprobante = $em->getRepository('VentasBundle:FacturaElectronica')->find($id);
        $items = array();
        if ($comprobante) {
            $valor = explode('-', $comprobante->getTipoComprobante()->getValor());
            $tipos = $em->getRepository('ConfigBundle:AfipComprobante')->findByValor(array('CRE-' . $valor[1]));
            foreach ($tipos as $tipo) {
                $items[] = $tipo->getId();
            }
            $tipos = $em->getRepository('ConfigBundle:AfipComprobante')->findByValor(array('NCE-' . $valor[1]));
            foreach ($tipos as $tipo) {
                $items[] = $tipo->getId();
            }
            $tipos = $em->getRepository('ConfigBundle:AfipComprobante')->findByValor(array('NDE-' . $valor[1]));
            foreach ($tipos as $tipo) {
                $items[] = $tipo->getId();
            }
        }
        return new JsonResponse($items);
    }

    private function checkCajaAbierta($em,$session) {
        $apertura = $em->getRepository('VentasBundle:CajaApertura')->find($session->get('caja')['apertura']);
        if (!$apertura) {
            $this->addFlash('error', 'La caja está cerrada. Debe realizar la apertura para iniciar cobros');
            return true;
        }
        return false;
    }

    /**
     * @Route("/getVentasPorCobrar", name="get_ventas_por_cobrar")
     * @Method("GET")
     */
    public function getVentasPorCobrar(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $ventas = $em->getRepository('VentasBundle:Venta')->findPorCobrarByCriteria(
            $this->get('session')->get('unidneg_id'),
            $request->get('desde'),
            $request->get('hasta'),
            $request->get('id'));

        $partial = $this->renderView(
            'VentasBundle:Cobro:_partial-tabla-por-cobrar.html.twig',
            array('ventas' => $ventas)
        );
        return new JsonResponse($partial);
    }

    /**
     * @Route("/delete/{id}", name="ventas_cobro_delete")
     * @Method("GET")
     */
    public function deleteAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura_cancel');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Cobro')->find($id);
        try {
            if ($entity->getFacturaElectronica()) {
                throw $this->createNotFoundException('No se puede eliminar un cobro emitido fiscalmente.');
            }
            $operacion = $entity->getNroOperacion();
            $venta = $entity->getVenta();
            $venta = $venta->setEstado('PENDIENTE');
            $em->persist($venta);

            $em->remove($entity);
            $em->flush();
            $this->addFlash('success', 'El cobro #' . $operacion . ' correspondiente a la venta #' . $venta->getNroOperacion() . ' fue eliminado!');
        }
        catch (\Exception $ex) {
            $this->addFlash('error', 'Error de eliminación. ' . $ex->getMessage());
        }
        return $this->redirectToRoute('ventas_cobro', ['selectedtab' => '#tab-cobrados']);
    }

}