<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;
use ConfigBundle\Controller\UtilsController;
use Endroid\QrCode\QrCode;
use VentasBundle\Entity\Venta;
use VentasBundle\Entity\VentaDetalle;
use VentasBundle\Entity\Cobro;
use VentasBundle\Entity\CobroDetalle;
use VentasBundle\Entity\NotaDebCred;
use VentasBundle\Entity\NotaDebCredDetalle;
use VentasBundle\Entity\FacturaElectronica;

/**
 * @Route("/facturaElectronica")
 */
class FacturaElectronicaController extends Controller {
    // Pagos
    const CAMBIO = 0;
    const CHEQUE = 3;
    const CTACTE = 6;
    const EFECTIVO = 8;
    // tarjeta credito = 20
    // tarjeta debito = 21
    const TARJETA = 20;

    /**
     * @Route("/emitir", name="ventas_factura_emitir")
     * @Method("POST")
     * @Template()
     */
    public function emitirAction(Request $request) {
        $id = $request->get('id');
        $entity = $request->get('entity');

        $serviceFacturar = $this->get('factura_electronica_webservice');
        $result = $serviceFacturar->procesarComprobante($id, $entity, 'WS');

        if ($entity == 'NotaDebCred' && $result['res'] == 'OK') {
            $result = $this->notaCreditoProcesarStock($id);
        }

        if ($result['res'] == 'OK') {
            $result['urlprint'] = $this->generateUrl('ventas_factura_print', ['id' => $id, 'entity' => $entity]);
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/{id}/{entity}/printFacturaVentas.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="ventas_factura_print")
     * @Method("GET")
     */
    public function printFacturaVentasAction(Request $request, $id, $entity) {
        $em = $this->getDoctrine()->getManager();
        $esCobro = $entity === 'Cobro';
        $comprobante = $em->getRepository('VentasBundle:' . $entity)->find($id);

        $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);

        $logo = __DIR__ . '/../../../web/assets/images/logo_comprobante.png';
        $qr = __DIR__ . '/../../../web/assets/imagesafip/qr.png';
        $logoafip = __DIR__ . '/../../../web/assets/imagesafip/logoafip.png';

        $url = $this->getParameter('url_qr_afip');
        $cuit = $this->getParameter('cuit_afip');

        $fe = $esCobro ? $comprobante->getFacturaElectronica() : $comprobante->getNotaElectronica();
        $data = array(
            "ver" => 1,
            "fecha" => $fe->getCbteFchFormatted('Y-m-d'),
            "cuit" => $cuit,
            "ptoVta" => $fe->getPuntoVenta(),
            "tipoCmp" => $fe->getCodigoComprobante(),
            "nroCmp" => $fe->getNroComprobante(),
            "importe" => round($fe->getTotal(), 2),
            "moneda" => $fe->getMonId(),
            "ctz" => $fe->getMonCotiz(),
            "tipoDocRec" => 0,
            "nroDocRec" => 0,
            "tipoCodAut" => "E",
            "codAut" => $fe->getCae()
        );
        $base64 = base64_encode(json_encode($data));

        $qrCode = new QrCode();
        $qrCode
            ->setText($url . $base64)
            ->setSize(120)
            ->setPadding(5)
            ->setErrorCorrection('low')
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        $qrCode->render($qr);

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $duplicado = $esCobro && $comprobante->getCliente()->getCategoriaIva() == 'I';
        $fe->setDocTipoTxt($em->getRepository('ConfigBundle:Parametro')->findTipoDocumento($fe->getDocTipo()));

        $this->render(
            'VentasBundle:FacturaElectronica:comprobante.pdf.twig',
            array('fe' => $fe, 'cbte' => $comprobante, 'duplicado' => $duplicado, 'empresa' => $empresa, 'logo' => $logo, 'qr' => $qr, 'logoafip' => $logoafip), $response
        );

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        $filename = $fe->getComprobanteTxt() . '.pdf';
        if ($this->getParameter('billing_folder')) {
            $file = $this->getParameter('billing_folder') . $filename;
            if (!file_exists($file)) {
                file_put_contents($file, $content, FILE_APPEND);
            }
        }
        return new Response($content, 200, array(
            'content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=' . $filename
        ));
    }

    /**
     * @Route("/getTicket", name="ventas_factura_getticket")
     * @Method("GET")
     * @Template()
     */
    public function getTicketAction(Request $request) {
        // setear la info para el ticket
        $id = $request->get('id');
        $entity = $request->get('entity');
        $dataTicket = array('res' => 'OK');
        $em = $this->getDoctrine()->getManager();
        $comprobante = $em->getRepository('VentasBundle:' . $entity)->find($id);
        $detallesPago = $entity == 'Cobro' ? $comprobante->getDetalles() : $comprobante->getCobroDetalles();
        $operador = ($entity == 'Cobro') ? $comprobante->getVenta()->getCreatedBy()->getNombre() : $comprobante->getCreatedBy()->getNombre();
        $feWs = $this->get('factura_electronica_webservice');
        // datos del cliente
        $dataCliente = $feWs->setDatosClienteTicket($comprobante);
        if ($dataCliente) {
            $dataTicket['cliente'] = $dataCliente;
        }
        // encabezado
        $dataTicket['encabezado'] = 'Condicion Venta ' . $comprobante->getFormapago()->getNombre();
        // detalle al pie
        $dataTicket['pie'][0] = 'Condicion Venta ' . $comprobante->getFormapago()->getNombre();
        $ref = $entity == 'Cobro' ? $comprobante->getRefVenta() : '';
        $dataTicket['pie'][1] = $ref . ' - Oper. ' . $operador;

        // tipo comprobante
        // tcFactura_A = 1 // tcFactura_B = 2 // tcFactura_C = 3;
        // tcNota_Debito_A = 4 // tcNota_Debito_B = 5 // tcNota_Debito_C = 6;
        // tcNota_Credito_A = 7 // tcNota_Credito_B = 8 // tcNota_Credito_C = 9;
        $cliente = $comprobante->getCliente();
        $catIva = ($cliente->getCategoriaIva()) ? $cliente->getCategoriaIva()->getNombre() : 'C';
        $percRentas = $cliente->getPercepcionRentas();
        if ($entity == 'Cobro') {
            $dataTicket['tipo'] = ($catIva == 'I' || $catIva == 'M') ? 1 : 2;
            $detalles = $comprobante->getVenta()->getDetalles();
        }
        else {
            $valor = explode('-', $comprobante->getTipoComprobante()->getValor());
            if ($valor[0] == 'CRE') {
                $tipo = $valor[1] == 'A' ? 7 : 8;
            }
            else {
                $tipo = $valor[1] == 'A' ? 4 : 5;
            }
            $dataTicket['tipo'] = $tipo;
            $detalles = $comprobante->getDetalles();
        }

        // descuento
        $tipo = $comprobante->getDescuentoRecargo() > 0 ? 'Rec ' : 'Desc ';
        $monto = number_format(abs($comprobante->getDescuentoRecargo()), 2, '.', ' ');
        $dataTicket['porcdto'] = $tipo . strval($monto) . "%";
        $dataTicket['montodto'] = $comprobante->getTotalDescuentoRecargo() * -1;

        // items
        // descripcion, cantidad, precio, iva, impuestosInternos,g2CondicionIVA, g2TipoImpuestoInterno, g2UnidadReferencia, g2CodigoProducto, g2CodigoInterno, g2UnidadMedida
        $dataTicket['items'] = null;
        $baseImp = 0;

        foreach ($detalles as $item) {
            $dtoRec = $item->getTotalDtoRecItem() / $comprobante->getCotizacion();
            $baseImp += $item->getBaseImponibleItem() + $dtoRec;
            $textoItem = "Cod:" . $item->getProducto()->getCodigo();
            $dataTicket['items'][] = array(
                $textoItem,
                $item->getCantidad(),
                $item->getPrecioUnitarioItem(),
                $item->getAlicuota(),
                0, //impuestosInternos
                7, //Gravado
                0, //tiFijo
                1,
                $item->getProducto()->getCodigo(),
                '',
                7 //Unidad
            );
            $lineas = explode('$', wordwrap($item->getNombreProducto(), 30, "$"));
            $dataTicket['adicItem'][] = $lineas;
        }

        foreach ($detallesPago as $pago) {
//            $montoPagos += $pago->getImporte();
            switch ($pago->getTipoPago()) {
                case 'EFECTIVO':
                    $item = array('Efectivo', $pago->getImporte(), '', self::EFECTIVO, 1, '', '');
                    break;
                case 'CHEQUE':
                    $item = array('Cheque', $pago->getImporte(), '', self::CHEQUE, 1, '', '');
                    break;
                case 'CTACTE':
                    $item = array('Cta.Cte.', $pago->getImporte(), '', self::CTACTE, 1, '', '');
                    break;
                case 'TARJETA':
                    $cupon = $pago->getDatosTarjeta()->getCupon();
                    $cuotas = $pago->getDatosTarjeta()->getCuota();
                    $tarjeta = 'Tarjeta ' . $pago->getDatosTarjeta()->getTarjeta()->getNombre();
                    $item = array($tarjeta, $pago->getImporte(), '', self::TARJETA, $cuotas, $cupon, '');
                    break;
                default:
                    break;
            }
            $dataTicket['pagos'][] = $item;
        }

        // tributos (iibb)
        $neto = round($baseImp, 2);

        if ($percRentas > 0) {
            // PercepcionIIBB = 7
            $iibb = round(($neto * $percRentas / 100), 2);
            $dataTicket['iibb'] = array(7, 'Perc. IIBB ' . $percRentas . '%', $baseImp, $iibb, $percRentas);
        }
        return new JsonResponse($dataTicket);
    }

    /**
     * @Route("/procesarTicket", name="ventas_factura_procesarticket")
     * @Method("POST")
     * @Template()
     */
    public function procesarTicketAction(Request $request) {
        $id = $request->get('id');
        $entity = $request->get('entity');
        $nroTicket = $request->get('nroticket');
        $serviceFacturar = $this->get('factura_electronica_webservice');
        $result = $serviceFacturar->procesarComprobante($id, $entity, 'TF', $nroTicket);

        if ($entity == 'NotaDebCred' && $result['res'] == 'OK') {
            $result = $this->notaCreditoProcesarStock($id);
        }
        return new JsonResponse($result);
    }

    private function notaCreditoProcesarStock($id) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            // Reponer stock si es credito
            $comprobante = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
            $tipo = $comprobante->getTipoComprobante();
            if ($tipo->getClase() == 'CRE') {
                $cbteAsoc = $comprobante->getComprobanteAsociado();
                $deposito = $em->getRepository('AppBundle:Deposito')->findOneByPordefecto(1);
                if ($cbteAsoc) {
                    if ($cbteAsoc->getCobro()) {
                        $deposito = $cbteAsoc->getCobro()->getVenta()->getDeposito();
                    }
                    // ajuste del comprobante asociado
                    $nuevoSaldo = $cbteAsoc->getSaldo() - $comprobante->getTotal();
                    $cbteAsoc->setSaldo($nuevoSaldo >= 0 ? $nuevoSaldo : 0 );
                }
                if ($comprobante->getDetalles()) {
                    // ajuste del stock
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
            }

            $em->getConnection()->commit();
            $result['res'] = 'OK';

            return $result;
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $result['res'] = 'ERROR';
            $result['msg'] = $ex->getMessage();
            return $result;
        }
    }

    /** probar webservice produccion
     * @Route("/getTiposComprobantesValidos", name="ws_get_comprobantes_validos")
     * @Method("GET")
     * @Template()
     */
    public function getTiposComprobantesValidosAction(Request $request) {
        $serviceFacturar = $this->get('factura_electronica_webservice');
        $result = $serviceFacturar->getTiposComprobantesValidos($request->get('modo'));
        return new JsonResponse($result);
    }

    /**
     * @Route("/renderImportacion", name="ventas_importacion")
     * @Method("GET")
     * @Template()
     */
    public function renderImportacionAction() {
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_importacion');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:ImportFacturasAfip')->findByProcesado(false);

        return $this->render('VentasBundle:FacturaElectronica:importacion.html.twig', array(
                'entity' => $entity,
        ));
    }

    /**
     * @Route("/processImportacion", name="ventas_procesar_importacion")
     * @Method("POST")
     * @Template()
     */
    public function processImportacionAction(Request $request) {
        die;
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_importacion');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($unidneg_id);
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        $entity = null;
        $count = 0;
        try {
            $op = $request->get('op');
            switch ($op) {
                case 'IMPORTAR':
                    $file = $request->files->get('csv');
                    $data = UtilsController::convertCsvToArray($file->getPathName());
                    $columns = array('fecha', 'tipo_comprobante', 'punto_venta', 'nro_comprobante', 'cae',
                        'doc_tipo', 'doc_nro', 'nombre_cliente', 'tipo_cambio', 'moneda',
                        'imp_neto', 'imp_tot_conc', 'imp_op_ex', 'imp_iva', 'total', 'en_ctacte',
                        'imp_trib', 'base105', 'iva105', 'tasa105', 'base21', 'iva21', 'tasa21');
                    UtilsController::loadCsvToTable($em, $data, 'import_facturas_afip', $columns);
                    $entity = $em->getRepository('ConfigBundle:ImportFacturasAfip')->findByProcesado(false);
                    $this->addFlash('success', 'Facturas preparadas para importacion : ' . count($entity));
                    break;
                case 'PROCESAR':
                    die;
                    $precioLista = $em->getRepository('AppBundle:PrecioLista')->findOneByPrincipal(1);
                    $comodin = $em->getRepository('AppBundle:Producto')->findOneByComodin(1);
                    $deposito = $em->getRepository('AppBundle:Deposito')->findOneByPordefecto(1);
                    $entity = $em->getRepository('ConfigBundle:ImportFacturasAfip')->findByProcesado(false);
                    foreach ($entity as $item) {
                        $fecha = new \DateTime(str_replace('/', '-', $item->getFecha()));
                        $tipo = str_pad(trim($item->getTipoComprobante()), 3, "0", STR_PAD_LEFT);
                        $tipoComprobante = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByCodigo($tipo);
                        if (!$tipoComprobante) {
                            continue;
                        }
                        $count += 1;
                        $tipoDoc = $em->getRepository('ConfigBundle:Parametro')->findTipoDocumentoByNombre(trim($item->getDocTipo()));
                        $cliente = $em->getRepository('VentasBundle:Cliente')->findOneByConsumidorFinal(true);
                        $nombreCliente = trim($item->getNombreCliente());
                        if ($item->getEnCtacte()) {
                            $cliente = $em->getRepository('VentasBundle:Cliente')->find($item->getEnCtacte());
                            $formaPago = $em->getRepository('ConfigBundle:FormaPago')->find(25);
                            $tipoPago = 'CTACTE';
                            $saldo = $item->getTotal();
                        }
                        else {
                            $formaPago = $em->getRepository('ConfigBundle:FormaPago')->findOneByContado(true);
                            $tipoPago = 'EFECTIVO';
                            $saldo = 0;
                        }
                        $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneBySimbolo(trim($item->getMoneda()));
                        $fe = new FacturaElectronica();

                        // generar cobroDetalle
                        $detCobro = new CobroDetalle();
                        $detCobro->setMoneda($moneda);
                        $detCobro->setTipoPago($tipoPago);
                        $detCobro->setImporte($item->getTotal());

                        // generar cobro o nota
                        $prefijoTipo = split('-', $tipoComprobante->getValor());
                        if (in_array($prefijoTipo[0], array('FAC', 'TICK'))) {
                            $venta = new Venta();
                            $venta->setFechaVenta($fecha);
                            $venta->setCliente($cliente);
                            $venta->setUnidadNegocio($unidneg);
                            $venta->setFormaPago($formaPago);
                            $venta->setPrecioLista($precioLista);
                            $venta->setMoneda($moneda);
                            $venta->setDeposito($deposito);
                            $venta->setNombreCliente($nombreCliente);
                            $venta->setEstado('FACTURADO');
                            $nroVenta = $param->getUltimoNroOperacionVenta() + 1;
                            $venta->setNroOperacion($nroVenta);
                            $param->setUltimoNroOperacionVenta($nroVenta);
                            $venta->setDescuentaStock(0);
                            $venta->setCategoriaIva($cliente->getCategoriaIva());
                            $venta->setPercepcionRentas($cliente->getPercepcionRentas());
                            $venta->setCotizacion($item->getTipoCambio());
                            // agregar un detalle x alicuota
                            if ($item->getBase21()) {
                                // item al 21%
                                $detVta = new VentaDetalle();
                                $detVta->setProducto($comodin);
                                $detVta->setTextoComodin('///');
                                $detVta->setCantidad(1);
                                $detVta->setAlicuota(21);
                                $detVta->setPrecio($item->getBase21());
                                $venta->addDetalle($detVta);
                            }
                            if ($item->getBase105()) {
                                // item al 10.5%
                                $detVta = new VentaDetalle();
                                $detVta->setProducto($comodin);
                                $detVta->setTextoComodin('///');
                                $detVta->setCantidad(1);
                                $detVta->setAlicuota(10.50);
                                $detVta->setPrecio($item->getBase105());
                                $venta->addDetalle($detVta);
                            }
                            $em->persist($venta);

                            $cobro = new Cobro();
                            $cobro->setFechaCobro($fecha);
                            $cobro->setCliente($cliente);
                            $cobro->setCotizacion($item->getTipoCambio());
                            $cobro->setMoneda($moneda);
                            $cobro->setFormaPago($formaPago);
                            $cobro->setEstado('FINALIZADO');
                            $cobro->setTipoDocumentoCliente($tipoDoc);
                            $cobro->setNombreCliente($nombreCliente);
                            $cobro->setNroDocumentoCliente($item->getDocNro());
                            $cobro->setUnidadNegocio($unidneg);
                            $nroCobro = $param->getUltimoNroOperacionCobro() + 1;
                            $cobro->setNroOperacion($nroCobro);
                            $param->setUltimoNroOperacionCobro($nroCobro);
                            $cobro->setVenta($venta);
                            $cobro->addDetalle($detCobro);
                            $em->persist($cobro);
                            $fe->setCobro($cobro);
                        }
                        else {
                            $nota = new NotaDebCred();
                            $nota->setFecha($fecha);
                            $nota->setCliente($cliente);
                            $nota->setCotizacion($item->getTipoCambio());
                            $nota->setMoneda($moneda);
                            $nota->setTipoComprobante($tipoComprobante);
                            $nota->setFormaPago($formaPago);
                            $nota->setCategoriaIva($cliente->getCategoriaIva());
                            $nota->setPercepcionRentas($cliente->getPercepcionRentas());
                            $nota->setPrecioLista($precioLista);
                            $nota->setUnidadNegocio($unidneg);
                            $nota->setTipoDocumentoCliente($tipoDoc);
                            $nota->setNombreCliente($nombreCliente);
                            $nota->setNroDocumentoCliente($item->getDocNro());
                            $nota->setTotal($item->getTotal());
                            $nota->setEstado('ACREDITADO');
                            $signo = $prefijoTipo[0] == 'CRE' ? '-' : '+';
                            $nota->setSigno($signo);
                            $nota->addCobroDetalle($detCobro);
                            // agregar un detalle x alicuota
                            if ($item->getBase21()) {
                                // item al 21%
                                $det = new NotaDebCredDetalle();
                                $det->setProducto($comodin);
                                $det->setTextoComodin('///');
                                $det->setCantidad(1);
                                $det->setAlicuota(21);
                                $det->setPrecio($item->getBase21());
                                $nota->addDetalle($det);
                            }
                            if ($item->getBase105()) {
                                // item al 21%
                                $det = new NotaDebCredDetalle();
                                $det->setProducto($comodin);
                                $det->setTextoComodin('///');
                                $det->setCantidad(1);
                                $det->setAlicuota(10.50);
                                $det->setPrecio($item->getBase105());
                                $nota->addDetalle($det);
                            }

                            $em->persist($nota);
                            $fe->setNotaDebCred($nota);
                        }
                        $em->persist($param);
                        // tributos - IIBB
                        $tributos = [];
                        if (floatval($item->getImpTrib()) > 0) {
                            $tributos = array(
                                'Id' => 7,
                                'BaseImp' => $item->getImpNeto(),
                                'Alic' => round((($item->getImpTrib() * 100) / $item->getImpNeto()), 2),
                                'Importe' => $item->getImpTrib()
                            );
                        }
                        // iva - alicuotas
                        $ivas = [];
                        if ($item->getBase21()) {
                            $ivas[] = array(
                                'Id' => 5,
                                'BaseImp' => round($item->getBase21(), 2),
                                'Importe' => round($item->getIva21(), 2)
                            );
                        }
                        if ($item->getBase105()) {
                            $ivas[] = array(
                                'Id' => 4,
                                'BaseImp' => round($item->getBase105(), 2),
                                'Importe' => round($item->getIva105(), 2)
                            );
                        }

                        // generar factura electronica
                        $fe->setUnidadNegocio($unidneg);
                        $fe->setTipoComprobante($tipoComprobante);
                        $fe->setPuntoVenta($item->getPuntoVenta());
                        $fe->setNroComprobante($item->getNroComprobante());
                        $fe->setCae($item->getCae());
                        $fe->setCaeVto($item->getCae() ? $fecha->format('Y-m-d') : '');
                        $fe->setTotal($item->getTotal());
                        $fe->setSaldo($saldo);
                        $fe->setConcepto(1);
                        $fe->setDocTipo($tipoDoc->getCodigo());
                        $fe->setDocNro($item->getDocNro());
                        $fe->setNombreCliente($nombreCliente);
                        $fe->setCbteFch($fecha->format('Ymd'));
                        $fe->setImpTotConc(0);
                        $fe->setImpNeto($item->getImpNeto());
                        $fe->setImpOpEx(0);
                        $fe->setImpIva($item->getImpIva());
                        $fe->setImpTrib($item->getImpTrib());
                        $fe->setMonId($moneda->getCodigoAfip());
                        $fe->setMonCotiz($item->getTipoCambio());
                        $fe->setTributos(json_encode($tributos));
                        $fe->setCbtesAsoc('[]');
                        $fe->setPeriodoAsoc('[]');
                        $fe->setIva(json_encode($ivas));
                        $fe->setCliente($cliente);
                        $em->persist($fe);

                        $item->setProcesado(1);
                        $item->setFacturaElectronica($fe);
                        $em->persist($item);

                        $em->flush();
                    }
                    $this->addFlash('success', 'Se ingresaron ' . $count . ' comprobantes.');
                    $entity = $em->getRepository('ConfigBundle:ImportFacturasAfip')->findByProcesado(false);
            }
        }
        catch (Exception $exc) {
            $this->addFlash('error', 'Hubo un error en la importacion ' . $exc->getMessage());
        }

        return $this->render('VentasBundle:FacturaElectronica:importacion.html.twig', array(
                'entity' => $entity
        ));
    }

}