<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\NotaDebCred;
use VentasBundle\Form\NotaDebCredType;
use VentasBundle\Entity\CobroDetalle;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;
use ConfigBundle\Controller\FormaPagoController;
use ConfigBundle\Entity\BancoMovimiento;
use VentasBundle\Entity\FacturaElectronica;
use VentasBundle\Afip\src\Afip;
use Endroid\QrCode\QrCode;

/**
 * @Route("/notadebcredVentas")
 */
class NotaDebCredController extends Controller {

    /**
     * @Route("/", name="ventas_notadebcred")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $cliId = $request->get('cliId');
        $cliente = null;
        if ($cliId) {
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($cliId);
        }
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $desde = $periodo['ini'];
        $hasta = $periodo['fin'];

        $entities = $em->getRepository('VentasBundle:NotaDebCred')->findByCriteria($unidneg, $cliId, $desde, $hasta);

        return $this->render('VentasBundle:NotaDebCred:index.html.twig', array(
                'entities' => $entities,
                'cliente' => $cliente,
                'cliId' => $cliId,
                'desde' => $desde,
                'hasta' => $hasta,
                'printpdf' => $request->get('printpdf')
        ));
    }

    /**
     * @Route("/new", name="ventas_notadebcred_new")
     * @Method("GET")
     * @Template("VentasBundle:NotaDebCred:new.html.twig")
     */
    public function newAction(Request $request) {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_notadebcred');
        $tipoNota = $request->get('tipo');
        $em = $this->getDoctrine()->getManager();
        // Verificar si la caja está abierta CAJA=1
        $apertura = $em->getRepository('VentasBundle:CajaApertura')->findAperturaSinCerrar(1);
        if (!$apertura) {
            $this->addFlash('error', 'La caja 1 está cerrada. Debe realizar la apertura para iniciar cobros');
            return $this->redirect($request->headers->get('referer'));
        }

        $entity = new NotaDebCred();
        $entity->setFecha(new \DateTime());
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if ($param) {
            // cargar datos parametrizados por defecto
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            $entity->setCliente($cliente);
            $entity->setCategoriaIva($cliente->getCategoriaIva()->getNombre());
            $entity->setPercepcionRentas($cliente->getPercepcionRentas() ? $cliente->getPercepcionRentas() : 0);
            $entity->setFormaPago($cliente->getFormaPago());
            $entity->setDescuentoRecargo($cliente->getFormaPago()->getPorcentajeRecargo());
            $entity->setPrecioLista($cliente->getPrecioLista());
            $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneBy(array('byDefault' => 1));
            $entity->setMoneda($moneda);
            $entity->setCotizacion($moneda->getCotizacion());
        }
        $entity->setSigno($tipoNota == 'CRE' ? '-' : '+' );
        // tipo de comprobante B para consumidor final que es cliente x defecto al inicio
        $tipoComprobante = $em->getRepository('ConfigBundle:AfipComprobante')->getIdByTipo($tipoNota . '-B');
        $entity->setTipoComprobante($tipoComprobante);
        $hoy = new \DateTime();
        $entity->setPeriodoAsocDesde($hoy);
        $entity->setPeriodoAsocHasta($hoy);

        $form = $this->createCreateForm($entity, null);
        return $this->render('VentasBundle:NotaDebCred:new.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a NotaCredito entity.
     * @param NotaCredito $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(NotaDebCred $entity, $tipoComp) {
        $form = $this->createForm(new NotaDebCredType(), $entity, array(
            'action' => $this->generateUrl('ventas_notadebcred_create'),
            'method' => 'POST',
            'attr' => array('tipoComp' => $tipoComp),
        ));
        return $form;
    }

    /**
     * @Route("/", name="ventas_notadebcred_create")
     * @Method("POST")
     * @Template("VentasBundle:NotaDebCred:new.html.twig")
     */
    public function createAction(Request $request) {
        $datos = $request->get('ventasbundle_notadebcred');
        $notadebcredId = $request->get('ventasbundle_notadebcred_id');
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($unidneg_id);
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_notadebcred');
        // credito - (descuenta la deuda del cliente)
        // debito + (aumenta la deuda del cliente)
        $response = array('res' => '', 'msg' => '', 'id' => null);

        // Verificar si la caja está abierta
        $apertura = $em->getRepository('VentasBundle:CajaApertura')->findAperturaSinCerrar(1);
        if (!$apertura) {
            $response['res'] = 'ERROR';
            $response['msg'] = 'La caja 1 está cerrada. Debe realizar la apertura para iniciar cobros';
            return new JsonResponse($response);
        }

        if ($notadebcredId) {
            $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($notadebcredId);
        }
        else {
            $entity = new NotaDebCred();
            $entity->setFecha(new \DateTime());
            $entity->setUnidadNegocio($unidneg);
        }
        $form = $this->createCreateForm($entity, $datos['tipoComprobante']);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em->getConnection()->beginTransaction();
            try {

                // cargar referencias faltantes
                $cliente = $em->getRepository('VentasBundle:Cliente')->find($request->get('ventasbundle_cliente'));
                $entity->setCliente($cliente);

                $formapago = $em->getRepository('ConfigBundle:FormaPago')->find($request->get('select_formapago'));
                $entity->setFormaPago($formapago);
                $entity->setCotizacion($entity->getMoneda()->getCotizacion());
                $tipoDocReq = $request->get('ventasbundle_tipoDocumentoCliente');
                if ($tipoDocReq) {
                    $tipoDoc = $em->getRepository('ConfigBundle:Parametro')->find($tipoDocReq);
                    $docNro = $request->get('ventasbundle_nroDocumentoCliente') ? $request->get('ventasbundle_nroDocumentoCliente') : 1;
                    $entity->setTipoDocumentoCliente($tipoDoc);
                    $entity->setNroDocumentoCliente($docNro);
                }
                else if ($cliente->getCuit()) {
                    $tipoDoc = $em->getRepository('ConfigBundle:Parametro')->filterByCodigo(80, 'tipo-documento');
                    $entity->setTipoDocumentoCliente($tipoDoc);
                    $entity->setNroDocumentoCliente($cliente->getCuit());
                }
                else {
                    $tipoDoc = $em->getRepository('ConfigBundle:Parametro')->filterByCodigo(96, 'tipo-documento');
                    $entity->setTipoDocumentoCliente($tipoDoc);
                    $entity->setNroDocumentoCliente(1);
                }
                if ($cliente->getConsumidorFinal()) {
                    $entity->setNombreCliente($request->get('ventasbundle_nombreCliente') ? $request->get('ventasbundle_nombreCliente') : 'CONSUMIDOR FINAL');
                }
                else {
                    $entity->setNombreCliente($cliente->getNombre());
                }
                if ($request->get('ventasbundle_notadebcred_comprobanteAsociado')) {

                    $compAsoc = $em->getRepository('VentasBundle:FacturaElectronica')->find($request->get('ventasbundle_notadebcred_comprobanteAsociado'));
                    if ($compAsoc->getSaldo() === $compAsoc->getTotal()) {
                        $entity->setComprobanteAsociado($compAsoc);
                    }
                    else {
                        $entity->setConcepto($compAsoc->getComprobanteTxt());
                    }
                }

                if (is_null($entity->getDescuentoRecargo())) {
                    $entity->setDescuentoRecargo($entity->getFormaPago()->getPorcentajeRecargo());
                }

                $catIva = ($entity->getCliente()->getCategoriaIva()) ? $entity->getCliente()->getCategoriaIva()->getNombre() : 'C';
                if ($entity->getDetalles()) {
                    $impTotal = $impNeto = $impIVA = $impTrib = $impDtoRec = 0;
                    $productos = $request->get('ventasbundle_producto');
                    foreach ($entity->getDetalles() as $key => $item) {
// ver como borrar los items antes de volver a cargar cuando se modifica el comprobante asociado en el detalle. en la pantalla
                        $producto = $em->getRepository('AppBundle:Producto')->find($productos[$key]);
                        if ($producto) {
                            $item->setProducto($producto);
                        }
                        else {
                            $entity->removeDetalle($item);
                        }

                        $dtoRec = $item->getTotalDtoRecItem();
                        $baseImp = $item->getBaseImponibleItem() + $dtoRec;
                        $importe = $item->getTotalIvaItem();

                        $impNeto += $baseImp;
                        $impIVA += $importe;
                        $impTotal += ($baseImp + $importe);
                    }
                    // TRIBUTOS
                    $impTrib = 0;
                    if ($catIva == 'I') {
                        $neto = round($impNeto, 2);
                        $iibb = round(($neto * $cliente->getPercepcionRentas() / 100), 2);
                        $impTrib = $iibb;
                    }
                    $impTotal += $impTrib;
                }

                // completar datos de detalles
                $saldo = 0;
                if (count($entity->getCobroDetalles()) == 0) {
                    if ($entity->getFormaPago()->getTipoPago() == 'CTACTE') {
                        // insertar un detalle para ctacte
                        $detalle = new CobroDetalle();
                        $detalle->setCajaApertura($apertura);
                        $detalle->setTipoPago('CTACTE');
                        $detalle->setMoneda($entity->getMoneda());
                        $detalle->setImporte($impTotal);
                        $entity->addCobroDetalle($detalle);
                        $saldo = round($impTotal, 2);
                    }
                }
                else {
                    $detalles = array_values($datos['cobroDetalles']);
                    foreach ($entity->getCobroDetalles() as $key => $detalle) {
                        $detalle->setCajaApertura($apertura);
                        if (!$detalle->getMoneda()) {
                            $detalle->setMoneda($entity->getMoneda());
                        }
                        $tipoPago = $detalle->getTipoPago();
                        if ($tipoPago != 'CHEQUE') {
                            $detalle->setChequeRecibido(null);
                        }
                        else {
                            $detalle->getChequeRecibido()->setTomado(new \DateTime());
                        }
                        if ($tipoPago != 'TARJETA') {
                            $detalle->setDatosTarjeta(null);
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
                            $tipoNota = $entity->getSigno() == 'CRE' ? 'Credito' : 'Debito';
                            $movBanco->setCobroDetalle($detalle);
                            $movBanco->setObservaciones('Transferencia por nota de '.$tipoNota.' - '.$entity->getNombreCliente());
                            $em->persist($movBanco);
                        }
                    }
                }
                // seteo ultimos valores
                $tipo = $entity->getTipoComprobante();
                $entity->setIva($impIVA);
                $entity->setPercIibb($impTrib);
                $entity->setTotal($impTotal);
                // signo
                $entity->setEstado('CREADO');
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();

                $response['res'] = 'OK';
                $response['msg'] = 'El registro de la nota se han guardado correctamente!';
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
        $errors = array();
        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = (String) $form[$child->getName()]->getErrors();
                }
            }
        }
        var_dump($errors);
        die;
        $response['res'] = 'ERROR';
        $response['msg'] = 'Formulario inválido';
        return new JsonResponse($response);
    }

    /**
     * @Route("/{id}/show", name="ventas_notadebcred_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota de Débito/Crédito.');
        }
        return $this->render('VentasBundle:NotaDebCred:show.html.twig', array(
                'entity' => $entity
        ));
    }

    /**
     * @Route("/{id}/edit", name="ventas_notadebcred_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_notadebcred_print');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota.');
        }
        $editForm = $this->createEditForm($entity, null);
        return $this->render('VentasBundle:NotaDebCred:new.html.twig', array(
                'entity' => $entity,
                'descuentoContado' => FormaPagoController::getDescuentoContado($em),
                'form' => $editForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a NotaCredito entity.
     * @param NotaCredito $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(NotaDebCred $entity, $tipoComp) {
        $form = $this->createForm(new NotaDebCredType(), $entity, array(
            'action' => $this->generateUrl('ventas_notadebcred_create', array('id' => $entity->getId())),
            'method' => 'POST',
            'attr' => array('tipoComp' => $tipoComp),
        ));
        return $form;
    }

    /**
     * @Route("/emitir", name="ventas_notadebcred_emitir")
     * @Method("POST")
     * @Template()
     */
    public function emitirAction(Request $request) {
        $id = $request->get('id');
        $entity = 'NotaDebCred';
        $serviceFacturar = $this->get('factura_electronica_webservice');
        $result = $serviceFacturar->procesarComprobante($id, $entity, 'WS');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            // Reponer stock si es credito
            $comprobante = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
            $tipo = $comprobante->getTipoComprobante();
            if ($tipo->getClase() == 'CRE' && $comprobante->getDetalles()) {
                $cbteAsoc = $comprobante->getComprobanteAsociado();
                if ($cbteAsoc) {
                    $deposito = $comprobante->getVenta()->getDeposito();
                }
                else {
                    $deposito = $em->getRepository('AppBundle:Deposito')->findOneByPordefecto(1);
                }
                foreach ($comprobante->getDetalles() as $detalle) {
                    $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                    if ($stock) {
                        $stock->setCantidad($stock->getCantidad() + $detalle->getCantidad());
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
                    $movim->setFecha($comprobante->getFecha());
                    $movim->setTipo('ventas_notadebcred');
                    $movim->setSigno('+');
                    $movim->setMovimiento($comprobante->getId());
                    $movim->setProducto($detalle->getProducto());
                    $movim->setCantidad($detalle->getCantidad());
                    $movim->setDeposito($deposito);
                    $em->persist($movim);
                    $em->flush();
                }
            }
            $em->getConnection()->commit();
            if ($result['res'] == 'OK') {
                $result['urlprint'] = $this->generateUrl('ventas_factura_print', ['id' => $id, 'entity' => $entity]);
            }
            return new JsonResponse($result);
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $response['res'] = 'ERROR';
            $response['msg'] = $ex->getMessage();
            return new JsonResponse($response);
        }
    }

    /**
     * @Route("/{id}", name="ventas_notadebcred_update")
     * @Method("PUT")
     * @Template("VentasBundle:NotaDebCred:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $modificaStock = ($request->get('modificaStock') == 'SI') ? true : false;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota.');
        }
        $original = new ArrayCollection();
        // Create an ArrayCollection of the current objects in the database
        foreach ($entity->getDetalles() as $item) {
            $original->add($item);
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // remove the relationship between the item and the pedido
                foreach ($original as $item) {
                    if (false === $entity->getDetalles()->contains($item)) {
                        $em->remove($item);
                    }
                }
                $em->flush();


                $em->getConnection()->commit();
            }
            catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('VentasBundle:NotaCredito:edit.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView(),
        ));
    }

    public function acreditarAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaCredito')->find($id);
        $entity->setEstado('ACREDITADO');
        $em->persist($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('ventas_notacredito'));
    }

    /**
     * IMPRESION DE listado
     */
    /**
     * @Route("/{id}/printNotaDebCredVentas.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="xprint_notadebcred_ventas")
     * @Method("GET")
     */
    // public function xprintNotaDebCredAction(Request $request, $id)
    // {
    //   $em = $this->getDoctrine()->getManager();
    //   $nota = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
    //   $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);
    //   $logo = __DIR__ . '/../../../web/assets/images/logo_comprobante.png';
    //   $qr = __DIR__ . '/../../../web/assets/imagesafip/qr.png';
    //   $logoafip = __DIR__ . '/../../../web/assets/imagesafip/logoafip.png';
    //   $url = $this->getParameter('url_qr_afip');
    //   $cuit = $this->getParameter('cuit_afip');
    //   $ptovta = $this->getParameter('ptovta_ws_factura');
    //   $data = array(
    //     "ver" => 1,
    //     "fecha" => $nota->getFecha()->format('Y-m-d'),
    //     "cuit" => $cuit,
    //     "ptoVta" => $ptovta,
    //     "tipoCmp" => $nota->getNotaElectronica()->getCodigoComprobante(),
    //     "nroCmp" => $nota->getNotaElectronica()->getNroComprobante(),
    //     "importe" => round($nota->getMontoTotal(), 2),
    //     "moneda" => $nota->getMoneda()->getCodigoAfip(),
    //     "ctz" => $nota->getCotizacion(),
    //     "tipoDocRec" => 0,
    //     "nroDocRec" => 0,
    //     "tipoCodAut" => "E",
    //     "codAut" => $nota->getNotaElectronica()->getCae()
    //   );
    //   $base64 = base64_encode(json_encode($data));
    //   $qrCode = new QrCode();
    //   $qrCode
    //     ->setText($url . $base64)
    //     ->setSize(120)
    //     ->setPadding(5)
    //     ->setErrorCorrection('low')
    //     ->setImageType(QrCode::IMAGE_TYPE_PNG);
    //   $qrCode->render($qr);
    //   $facade = $this->get('ps_pdf.facade');
    //   $response = new Response();
    //   $this->render(
    //     'VentasBundle:NotaDebCred:comprobante.pdf.twig',
    //     array('nota' => $nota, 'empresa' => $empresa, 'logo' => $logo, 'qr' => $qr, 'logoafip' => $logoafip),
    //     $response
    //   );
    //   $xml = $response->getContent();
    //   $content = $facade->render($xml);
    //   $hoy = new \DateTime();
    //   $filename = $nota->getNotaElectronica()->getComprobanteTxt() . '.pdf';
    //   if ($this->getParameter('billing_folder')) {
    //     $file = $this->getParameter('billing_folder') . $filename;
    //     if (!file_exists($file)) {
    //       file_put_contents($file, $content, FILE_APPEND);
    //     }
    //   }
    //   return new Response($content, 200, array(
    //     'content-type' => 'application/pdf',
    //     'Content-Disposition' => 'filename=' . $filename
    //   ));
    // }

    /**
     * @Route("/printVentasListNotaDebCred.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ventas_list_notadebcred")
     * @Method("POST")
     */
    public function printVentasListNotaDebCredAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $clienteId = $request->get('clienteid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $cliente = $em->getRepository('VentasBundle:Cliente')->find($clienteId);
        $textoFiltro = array($cliente ? $cliente->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render(
            'VentasBundle:NotaDebCred:pdf-notasdebcred.pdf.twig',
            array(
                'items' => json_decode($items), 'filtro' => $textoFiltro,
                'search' => $request->get('searchterm')
            ),
            $response
        );

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array(
            'content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_ventas_notasdebcred_' . $hoy->format('dmY_Hi') . '.pdf'
        ));
    }

    /**
     * @Route("/delete/{id}", name="ventas_notadebcred_delete")
     * @Method("GET")
     */
    public function deleteAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        try {
            if ($entity->getNotaElectronica()) {
                throw $this->createNotFoundException('No se puede eliminar una nota emitida fiscalmente.');
            }
            $cliente = $entity->getNombreClienteTxt();
            $operacion = $entity->getId();
            $entity->setEstado('ELIMINADO');
//            $em->remove($entity);
            $em->flush();
            $this->addFlash('success', 'La nota #' . $operacion . ' de ' . $cliente . ' fue eliminada!');
        }
        catch (\Exception $ex) {
            $this->addFlash('error', 'Error de eliminación. ' . $ex->getMessage());
        }
        return $this->redirectToRoute('ventas_notadebcred');
    }

    /**
     * @Route("/release/{id}", name="ventas_notadebcred_release")
     * @Method("GET")
     */
    public function releaseAction($id) {
        if ($this->getUser()->isAdmin($this->get('session')->get('unidneg_id'))) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
            $entity->setComprobanteAsociado(null);
            $entity->setSaldo($entity->getTotal());
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'Se liberó el comprobante asociado');
            return $this->redirectToRoute('ventas_notadebcred_show', array('id' => $id));
        }
//         UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
//         $em = $this->getDoctrine()->getManager();
//         $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
//         try {
//             if ($entity->getNotaElectronica()) {
//                 throw $this->createNotFoundException('No se puede eliminar una nota emitida fiscalmente.');
//             }
//             $cliente = $entity->getNombreClienteTxt();
//             $operacion = $entity->getId();
//             $entity->setEstado('ELIMINADO');
// //            $em->remove($entity);
//             $em->flush();
//             $this->addFlash('success', 'La nota #' . $operacion . ' de ' . $cliente . ' fue eliminada!');
//         }
//         catch (\Exception $ex) {
//             $this->addFlash('error', 'Error de eliminación. ' . $ex->getMessage());
//         }
//         return $this->redirectToRoute('ventas_notadebcred');
    }

}