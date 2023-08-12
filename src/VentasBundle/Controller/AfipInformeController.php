<?php

namespace VentasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ConfigBundle\Controller\UtilsController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AfipInformeController extends Controller {


    /**
     * @Route("/afipInformeVentas", name="ventas_afipinforme")
     * @Method("GET")
     * @Template()
     */
    public function afipInformeVentasAction(Request $request) {
        $periodo = $request->get('periodo');
        $unidneg = $this->get('session')->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();

        $resultado = $this->getReginfoVentas($em, $unidneg, $periodo, 'A');

        return $this->render('VentasBundle:Impuesto:informe-afip.html.twig', array(
                    'path' => $this->generateUrl('ventas_afipinforme'),'tipo' => 'VENTAS',
                    'resultado' => $resultado, 'periodo' => $periodo
        ));
    }

    private function getReginfoVentas($em, $unidneg, $mes, $format = 'A') {
        if ($mes) {
            $fchdesde = UtilsController::toAnsiDate('01-' . $mes);
            $ini = new \DateTime($fchdesde);
            //* periodo formato integer
            $desde = $ini->format('Ym01');
            $hasta = $ini->format('Ymt');
        }
        else {
            $desde = $hasta = '';
        }

        $toterrores = array('CUIT' => 0, 'COMPROBANTE' => 0, 'ALICUOTA' => 0);
        $valinic = ($format == 'A') ? array() : '';
        $reginfoCbtes = $reginfoAlicuotas = $valinic;
        /*
         * FACTURA ELECTRONICA
         */
        $facturas = $em->getRepository('VentasBundle:Factura')->findByFeventasPeriodoUnidadNegocio($desde, $hasta, $unidneg);
        foreach ($facturas as $fe) {
            $operacionesExentas = 0;
            $error = array();
            $comprobante = $fe->getCobro() ? $fe->getCobro() : $fe->getNotaDebCred();
            //* cliente
            $cliente = $comprobante->getCliente();
            $clienteId = $cliente->getId();
            $clienteNombre = UtilsController::sanear_string( $fe->getNombreCliente() );
            //* DATOS FACTURA ELECTRONICA
            $ptovta = str_pad($fe->getPuntoVenta(), 5, "0", STR_PAD_LEFT);
            $nrocomp = str_pad($fe->getNroComprobante(), 20, "0", STR_PAD_LEFT);
            //$nrocompHasta = str_pad("0", 20, "0", STR_PAD_LEFT);
            $total = str_pad(number_format($fe->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT);
            //* ALICUOTAS
            $alicuotas = json_decode($fe->getIva());
            foreach( $alicuotas as $alicuota){
              $netoGravado = number_format($alicuota->BaseImp, 2, '', '');
              $liquidado = number_format($alicuota->Importe, 2, '', '');

              if ($format == 'A') {
                $alic = array(
                            'tipoComprobante' => $fe->getTipoComprobante()->getCodigo(),
                            'puntoVenta' => $ptovta,
                            'nroComprobante' => $nrocomp,
                            'netoGravado' => str_pad($netoGravado, 15, "0", STR_PAD_LEFT),
                            'codAlicuota' => str_pad($alicuota->Id, 4, "0", STR_PAD_LEFT),
                            'liquidado' => str_pad($liquidado, 15, "0", STR_PAD_LEFT),
                            'error' => $error
                        );
                array_push($reginfoAlicuotas, $alic);
              }else{
                $txtalic = $fe->getTipoComprobante()->getCodigo() .
                                $ptovta .
                                $nrocomp .
                                str_pad($netoGravado, 15, "0", STR_PAD_LEFT) .
                                str_pad($alicuota->Id, 4, "0", STR_PAD_LEFT) .
                                str_pad($liquidado, 15, "0", STR_PAD_LEFT);
                $reginfoAlicuotas = ( $reginfoAlicuotas == '') ? $txtalic : $reginfoAlicuotas . PHP_EOL . $txtalic;
              }
            }
            //* TRIBUTOS - IIBB
            $tributo = json_decode($fe->getTributos());
            $iibb = array_key_exists('Importe',$tributo) ? $tributo->Importe : 0  ;
            $percIIBB = str_pad(number_format($iibb, 2, '', ''), 15, "0", STR_PAD_LEFT);

            //* COMPROBANTES
            $codOperacion = ($fe->getImpIva() == 0 ) ? 'A' : ' ';
            $pagovto = '00000000';
            $strpad15 = str_pad("0", 15, "0");
            $nroDoc = str_pad($fe->getDocNro(), 20, "0", STR_PAD_LEFT);
            $cotiz = str_pad(number_format($fe->getMonCotiz(), 6, '', ''), 10, "0", STR_PAD_LEFT)  ;

            if ($format == 'A') {
                $comp = array(
                    'fecha' => $fe->getCbteFch(),
                    'tipoComprobante' => $fe->getTipoComprobante()->getCodigo(),
                    'puntoVenta' => $ptovta,
                    'nroComprobante' => $nrocomp,
                    'nroComprobanteHasta' => $nrocomp,
                    'tipoDoc' => $fe->getDocTipo(),
                    'nroDoc' => $nroDoc,
                    'cliente' => $clienteNombre,
                    'total' => $total,
                    'nograv' => $strpad15,
                    'nocateg' => $strpad15,
                    'exe' => str_pad(number_format($operacionesExentas, 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percImpNac' => $strpad15,
                    'percIIBB' => $percIIBB,
                    'percMuni' => $strpad15,
                    'impInterno' => $strpad15,
                    'moneda' => $fe->getMonId(),
                    'tipoCambio' => $cotiz,
                    'cantAlicuotas' => count($alicuotas),
                    'codOperacion' => $codOperacion,
                    'otrosTributos' => $strpad15,
                    'pagoVto' => $pagovto,
                    'error' => $error,
                    'clienteId' => $clienteId,
                    'id' => $comprobante->getId()
                );
                array_push($reginfoCbtes, $comp);
            }
            else {

                $comp = $fe->getCbteFch() .
                        $fe->getTipoComprobante()->getCodigo() .
                        $ptovta .
                        $nrocomp .
                        $nrocomp .
                        $fe->getDocTipo() .
                        $nroDoc .
                        UtilsController::mb_str_pad($clienteNombre, 30) .
                        $total .
                        $strpad15 .
                        $strpad15 .
                        str_pad(number_format($operacionesExentas, 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        $strpad15 .
                        $percIIBB .
                        $strpad15 .
                        $strpad15 .
                        $fe->getMonId() .
                        $cotiz .
                        count($alicuotas) .
                        $codOperacion .
                        $strpad15 .
                        $pagovto
                ;
                $reginfoCbtes = ($reginfoCbtes == '') ? $comp : $reginfoCbtes . PHP_EOL . $comp;
            }
        }
        /*
         * RESULTADOS
         */
        $resultado = array('comprobantes' => $reginfoCbtes, 'alicuotas' => $reginfoAlicuotas, 'errores' => $toterrores);

        return $resultado;
    }

    /**
     * @Route("/ventasAfipExportTxt", name="ventas_reginfo_export_txt")
     * @Method("GET")
     * @Template()
     */
    public function ventasAfipExportTxt(Request $request) {
        $periodo = $request->get('periodo');
        $file = $request->get('file');
        $tipo = $request->get('tipo');

        $unidneg = $this->get('session')->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();

        $resultado = ($tipo == 'COMPRAS') ? $this->getReginfoCompras($em, $unidneg, $periodo, 'T') :
                $this->getReginfoVentas($em, $unidneg, $periodo, 'T');
        /* if ($resultado['errores']['CUIT'] > 0 || $resultado['errores']['COMPROBANTE'] > 0 || $resultado['errores']['ALICUOTA'] > 0) {
          return $this->redirect($this->generateUrl(strtolower($tipo) . '_informeafip') . '?periodo=' . $periodo);
          } */
        $name = (strpos($request->headers->get('referer'), 'ivaCompras')) ? 'LIBRO_IVA_DIGITAL_' : 'REGINFO_CV_';
        $UnidadNegegocio = $em->getRepository('ConfigBundle:UnidadNegocio')->find($unidneg);
        $nombreUnidadNegocio = strtoupper(trim(preg_replace('/[^A-Za-z0-9-]+/', '', UtilsController::sanear_string($UnidadNegegocio))));
        if ($file == 'CBTE') {
            $filename = $name . $tipo . '_' . $nombreUnidadNegocio . '_CBTE';
            // The dinamically created content of the file
            $fileContent = $resultado['comprobantes'];
        }
        else {
            $filename = $name . $tipo . '_' . $nombreUnidadNegocio . '_ALICUOTAS';
            // The dinamically created content of the file
            $fileContent = $resultado['alicuotas'];
        }
        $filename = $filename . '_' . $periodo . '.txt';
        // Return a response with a specific content
        $response = new Response($fileContent);

        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
        );

        // Set the content disposition
        $response->headers->set('Content-Disposition', $disposition);

        // Dispatch request
        return $response;
    }


}