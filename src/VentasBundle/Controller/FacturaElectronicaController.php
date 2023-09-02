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
use VentasBundle\Entity\FacturaElectronica;
use VentasBundle\Afip\src\Afip;
use Endroid\QrCode\QrCode;

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
        $dataTicket['pie'][1] = $ref . ' - Oper. ' . $this->getUser()->getNombre();

        // tipo comprobante
        // tcFactura_A = 1 // tcFactura_B = 2 // tcFactura_C = 3;
        // tcNota_Debito_A = 4 // tcNota_Debito_B = 5 // tcNota_Debito_C = 6;
        // tcNota_Credito_A = 7 // tcNota_Credito_B = 8 // tcNota_Credito_C = 9;
        $cliente = $comprobante->getCliente();
        $catIva = ($cliente->getCategoriaIva()) ? $cliente->getCategoriaIva()->getNombre() : 'C';
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
            $textoItem = "Cod:" . $item->getProducto()->getCodigo() . " " . $item->getProducto()->getNombre();
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
        if ($catIva == 'I') {
            // PercepcionIIBB = 7
            $alicuota = $this->getParameter('iibb_percent');
            $iibb = round(($neto * $alicuota / 100), 2);
            $dataTicket['iibb'] = array(7, 'Perc. IIBB ' . $alicuota . '%', $baseImp, $iibb, $alicuota);
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

}