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
use VentasBundle\Entity\FacturaElectronica;
use VentasBundle\Afip\src\Afip;
use Endroid\QrCode\QrCode;
/**
 * @Route("/facturaElectronica")
 */
class FacturaElectronicaController extends Controller {

    public $webserviceError = array(
                                      10013 => "Para comprobantes clase A y M el tipo de documento debe ser CUIT",
                                      10015 => "Debe ingresar tipo y nro de documento del cliente.",
                                      10016 => "Se está intentando autorizar un comprobante con fecha anterior al último informado.",
                                      10024 => "Si ImpTrib es mayor a 0 el objeto Tributos y Tributo son obligatorios.",
                                      10047 => "El campo ImpIVA (Importe de IVA) para comprobantes tipo C debe ser igual a cero (0)",
                                      10048 => "El campo  'Importe Total' ImpTotal, debe ser igual  a la  suma de ImpNeto + ImpTrib. Donde ImpNeto es igual al Sub Total",
                                      10071 => "Para comprobantes tipo C el objeto IVA no debe informarse.",
                                    );

    /**
     * @Route("/", name="ventas_factura_emitir")
     * @Method("POST")
     * @Template()
     */
    public function emitirAction(Request $request) {
      $id = $request->get('id');
      $entity = $request->get('entity');
      $em = $this->getDoctrine()->getManager();
      $comprobante = $em->getRepository('VentasBundle:'.$entity)->find($id);
      $cliente = $comprobante->getCliente();
      // Cobro o NotaDebCred
      $esCobro = $entity === 'Cobro';
      $detalles = $esCobro ? $comprobante->getVenta()->getDetalles() : $comprobante->getDetalles() ;
      $fecha = new \DateTime();
      // $fecha = $esCobro ? $comprobante->getFechaCobro() : $comprobante->getFecha();
      try {
        $em->getConnection()->beginTransaction();
        $fe = new FacturaElectronica();

        // armar datos para webservice
        $docTipo = 99;
        $docNro = 0;
        if ($cliente->getCuit()) {
          $docTipo = 80;
          $docNro = trim($cliente->getCuit());
        } elseif ($comprobante->getTipoDocumentoCliente()) {
          $docTipo = $comprobante->getTipoDocumentoCliente()->getCodigo();
          $docNro = $comprobante->getNroDocumentoCliente();
        }

        $catIva = ($cliente->getCategoriaIva()) ? $cliente->getCategoriaIva()->getNombre() : 'C';

        $cbtesAsoc = $periodoAsoc = array();
        if($esCobro){
          //* COBRO
          $valor = ($catIva == 'I' || $catIva == 'M') ? 'FAC-A' : 'FAC-B';
          $tipoComprobante = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByValor($valor);
        }else{
          //* NOTA DEBITO CREDITO
          if ($comprobante->getComprobanteAsociado()) {
            /* array(
                'Tipo' 		=> 6, // Tipo de comprobante (ver tipos disponibles)
                'PtoVta' 	=> 1, // Punto de venta
                'Nro' 		=> 1 // Numero de comprobante
                )
            )*/
            $factura = $comprobante->getComprobanteAsociado();
            $cbtesAsoc[] = array(
              'Tipo'   =>  $factura->getCodigoComprobante(),
              'PtoVta' =>  $factura->getPuntoVenta(),
              'Nro'    =>  $factura->getNroComprobante()
            );
          } else {
            /* array(
                'FchDesde' => Ymd
                'FchHasta'  => Ymd
                )
            */
            $periodoAsoc = array('FchDesde' => intval($comprobante->getPeriodoAsocDesde()->format('Ymd')), 'FchHasta' => intval($comprobante->getPeriodoAsocHasta()->format('Ymd')));
          }
          $tipoComprobante = $comprobante->getTipoComprobante()->getCodigo();
        }

        $fe->setTipoComprobante($tipoComprobante);
        $ptovta = $this->getParameter('ptovta_ws_factura');
        $fe->setPuntoVenta($ptovta);

        $iva = $tributos = array();
        if($detalles){
          $impTotal = $impNeto = $impIVA = $impTrib = $impDtoRec = 0;
          foreach($detalles as $item){
            $alicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->findOneBy(array('valor' => $item->getProducto()->getIva()));
            $codigo = intval($alicuota->getCodigo());
            $dtoRec = $item->getTotalDtoRecItem() /  $comprobante->getCotizacion();
            $baseImp = $item->getBaseImponibleItem() + $dtoRec;
            $importe = $item->getTotalIvaItem() /  $comprobante->getCotizacion();
            $key = array_search($codigo, array_column($iva, 'Id'));
            // IVA
            /*  array(
                'Id' 		=> 5, // Id del tipo de IVA (ver tipos disponibles)
                'BaseImp' 	=> 100, // Base imponible
                'Importe' 	=> 21 // Importe
            )*/
            if ($importe > 0) {
              if ($key === false) {
                $iva[] = array(
                  'Id' => $codigo,
                  'BaseImp' => round($baseImp, 2),
                  'Importe' => round($importe, 2)
                );
              } else {
                $iva[$key] = array(
                  'Id' => $codigo,
                  'BaseImp' => round($iva[$key]['BaseImp'] + $baseImp, 2),
                  'Importe' => round($iva[$key]['Importe'] + $importe, 2)
                );
              }
              // TOTALES
              $impDtoRec += $dtoRec;
              $impNeto += $baseImp;
              $impIVA += $importe;
              $impTotal += ($baseImp + $importe);
            }
          }
        }

        // TRIBUTOS
          /*array(
              'Id' 		=>  99, // Id del tipo de tributo (ver tipos disponibles)
              'Desc' 		=> 'Ingresos Brutos', // (Opcional) Descripcion
              'BaseImp' 	=> 150, // Base imponible para el tributo
              'Alic' 		=> 5.2, // Alícuota
              'Importe' 	=> 7.8 // Importe del tributo
          )*/
        $impTrib = 0;
        if ($catIva == 'I') {
          $neto = round($impNeto, 2);
          $iibb = round(($neto * $this->getParameter('iibb_percent')/100 ), 2);
          $impTrib = $iibb;
          $tributos = array(
            'Id' => 7,
            'BaseImp' => $neto,
            'Alic' => $this->getParameter('iibb_percent'),
            'Importe' => $iibb
          );
        }
        $impTotal += $impTrib;

        $afip = new Afip(array('CUIT' => $this->getParameter('cuit_afip')));

        $data = array(
          'CantReg'   => 1,  // Cantidad de comprobantes a registrar
          'PtoVta'   => $fe->getPuntoVenta(),  // Punto de venta
          'CbteTipo'   => $fe->getCodigoComprobante(),  // Tipo de comprobante (ver tipos disponibles)
          'Concepto'   => 1,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
          'DocTipo'   => $docTipo, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
          'DocNro'   => $docNro,  // Número de documento del comprador (0 consumidor final)
          'CbteFch'   => intval($fecha->format('Ymd')), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
          'ImpTotal'   => round($impTotal, 2), // Importe total del comprobante
          'ImpTotConc'   => 0,   // Importe neto no gravado
          'ImpNeto'   => round($impNeto, 2), // Importe neto gravado
          'ImpOpEx'   => 0,   // Importe exento de IVA
          'ImpIVA'   => round($impIVA, 2),  //Importe total de IVA
          'ImpTrib'   => round($impTrib, 2),   //Importe total de tributos
          'MonId'   => $comprobante->getMoneda()->getCodigoAfip(), //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos)
          'MonCotiz'   => $comprobante->getMoneda()->getCotizacion(),     // Cotización de la moneda usada (1 para pesos argentinos)
          'Tributos' => $tributos,
          'CbtesAsoc'   => $cbtesAsoc,
          'PeriodoAsoc' => $periodoAsoc,
          'Iva'       => $iva,
        );

        // si no hay combrobante asociado
        if (empty($cbtesAsoc)) {
          unset($data['CbtesAsoc']);
        }
        if (empty($periodoAsoc)) {
          unset($data['PeriodoAsoc']);
        }
        // si no hay tributos
        if (empty($tributos)) {
          unset($data['Tributos']);
        }

        // create voucher
        $wsResult = $afip->ElectronicBilling->CreateNextVoucher($data);

        //* completar datos del registro factura electronica
        $fe->setCae($wsResult['CAE']);
        $fe->setCaeVto($wsResult['CAEFchVto']);
        $fe->setNroComprobante($wsResult['voucher_number']);
        $fe->setFechaEmision(new \DateTime());
        $esCobro ? $fe->setCobro($comprobante) : $fe->setNotaDebCred($comprobante);
        $fe->setTotal(round($impTotal, 2));
        $saldofe = $esCobro ? $impTotal : ($fe->getTipoComprobante()->getClase() === 'CRE') ? 0 : $impTotal;
        $fe->setSaldo(round($saldofe, 2));
        $em->persist($fe);

        //* Marcar como finalizado
        $esCobro ? $comprobante->setEstado('FINALIZADO') : $comprobante->setEstado('ACREDITADO');
        $em->persist($comprobante);

        $em->flush();

        $em->getConnection()->commit();
        $response['res'] = 'OK';
        $response['msg'] = 'Factura emitida correctamente!';
        $response['id'] = $id;
        $response['urlprint'] = $this->generateUrl('ventas_factura_print', ['id' => $id, 'entity' => $entity]);
        return new JsonResponse($response);


      } catch (\Exception $ex) {
        $em->getConnection()->rollback();

        $msg =  array_key_exists($ex->getCode(), $this->webserviceError) ? $this->webserviceError[$ex->getCode()] : $ex->getMessage() ;

        $response['res'] = 'ERROR';
        $response['msg'] = $msg;

        return new JsonResponse($response);
      }
    }


  /**
   * @Route("/{id}/{entity}/printFacturaVentas.{_format}",
   * defaults = { "_format" = "pdf" },
   * name="ventas_factura_print")
   * @Method("GET")
   */
  public function printFacturaVentasAction(Request $request, $id, $entity)
  {
    $em = $this->getDoctrine()->getManager();
    $esCobro = $entity === 'Cobro';
    $comprobante = $em->getRepository('VentasBundle:'.$entity)->find($id);

    $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);

    $logo = __DIR__ . '/../../../web/assets/images/logo_comprobante.png';
    $qr = __DIR__ . '/../../../web/assets/imagesafip/qr.png';
    $logoafip = __DIR__ . '/../../../web/assets/imagesafip/logoafip.png';

    $url = $this->getParameter('url_qr_afip');
    $cuit = $this->getParameter('cuit_afip');
    $ptovta = $this->getParameter('ptovta_ws_factura');

    $fe = $esCobro ? $comprobante->getFacturaElectronica() : $comprobante->getNotaElectronica();
    $fechaEmision = $fe->getFechaEmision() ? $fe->getFechaEmision() : ( $esCobro ? $comprobante->getFechaCobro() : $comprobante->getFecha() );
    $data = array(
      "ver" => 1,
      "fecha" => $fechaEmision->format('Y-m-d'),
      "cuit" => $cuit,
      "ptoVta" => $ptovta,
      "tipoCmp" => $fe->getCodigoComprobante(),
      "nroCmp" => $fe->getNroComprobante(),
      "importe" => round($fe->getTotal(), 2),
      "moneda" => $comprobante->getMoneda()->getCodigoAfip(),
      "ctz" => $comprobante->getCotizacion(),
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

    $esCobro
      ? $this->render(
        'VentasBundle:Cobro:comprobante.pdf.twig',
          array('cobro' => $comprobante, 'venta' => $comprobante->getVenta(), 'empresa' => $empresa, 'logo' => $logo, 'qr' => $qr, 'logoafip' => $logoafip), $response
        )
      : $this->render(
        'VentasBundle:NotaDebCred:comprobante.pdf.twig',
        array('nota' => $comprobante, 'empresa' => $empresa, 'logo' => $logo, 'qr' => $qr, 'logoafip' => $logoafip), $response
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


}