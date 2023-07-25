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

    /**
     * @Route("/", name="ventas_factura_emitir")
     * @Method("POST")
     * @Template()
     */
    public function emitirAction(Request $request) {
      $id = $request->get('id');
      $entity = $request->get('entity');

      $serviceFacturar = $this->get('factura_electronica_webservice');
      $result = $serviceFacturar->emitir($id, 'Cobro');

      if($result['res'] == 'OK'){
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

    $duplicado = $esCobro && $comprobante->getCliente()->getCategoriaIva()=='I';
    $fe->setDocTipoTxt( $em->getRepository('ConfigBundle:Parametro')->findTipoDocumento( $fe->getDocTipo() ) );

    $this->render(
        'VentasBundle:FacturaElectronica:comprobante.pdf.twig',
        array('fe'=> $fe, 'cbte' => $comprobante, 'duplicado' => $duplicado, 'empresa' => $empresa, 'logo' => $logo, 'qr' => $qr, 'logoafip' => $logoafip), $response
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