<?php

namespace ComprasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ConfigBundle\Controller\UtilsController;
use Symfony\Component\HttpFoundation\JsonResponse;

class RetencionesController extends Controller {

    /**
     * @Route("/retencionesRentas", name="compras_retencionrentas")
     * @Method("GET")
     * @Template()
     */
    public function retencionesRentasAction(Request $request) {
        $periodo = $request->get('periodo');
        $unidneg = $this->get('session')->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();
        $resultado = null;
        if ($periodo) {
            $resultado = $this->resultadoRentas($periodo, $em, 'A');
        }
        return $this->render('ComprasBundle:Retenciones:informe-rentas.html.twig', array(
                'tipo' => 'Rentas', 'path' => $this->generateUrl('compras_retencionrentas'),
                'periodo' => $periodo, 'resultado' => $resultado
        ));
    }

    /**
     * @Route("/retencionesGanancias", name="compras_retencionganancias")
     * @Method("GET")
     * @Template()
     */
    public function retencionesGananciasAction(Request $request) {
        $periodo = $request->get('periodo');
        $em = $this->getDoctrine()->getManager();
        $resultado = array('retenciones' => null, 'sujetos' => null);
        if ($periodo) {
            $resultado = $this->resultadoGanancias($periodo, $em, 'A');
        }
        return $this->render('ComprasBundle:Retenciones:informe-ganancias.html.twig', array(
                'tipo' => 'Ganancias', 'path' => $this->generateUrl('compras_retencionganancias'),
                'periodo' => $periodo, 'resultado' => $resultado
        ));
    }

    /**
     * @Route("/retencionExportTxt", name="retencion_export_txt")
     * @Method("GET")
     * @Template()
     */
    public function retencionExportTxt(Request $request) {
        $periodo = $request->get('periodo');
        $file = $request->get('file');
        $tipo = $request->get('tipo');

        $em = $this->getDoctrine()->getManager();

        if ($tipo == 'Rentas') {
            $resultado = $this->resultadoRentas($periodo, $em, 'T');
            $fileContent = $resultado;
            $hoy = new \DateTime();
            $filename = $hoy->format('YmdHi') . '.txt';
        }
        else {
            $resultado = $this->resultadoGanancias($periodo, $em, 'T');
            $fileContent = ( $file == 'RET' ) ? $resultado['retenciones'] : $resultado['sujetos'];
            $filename = $file . str_replace('-', '', $periodo) . '.txt';
        }

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

    /**
     * Devuelve las retenciones de ganancias segun periodo
     */
    private function resultadoGanancias($periodo, $em, $format = 'A') {
        $desde = UtilsController::toAnsiDate('01-' . $periodo);
        $ini = new \DateTime($desde);
        $hasta = $ini->format('Y-m-t');
        $pagos = $em->getRepository('ComprasBundle:PagoProveedor')->findRetencionesGanancias($desde, $hasta);
        $sujetos = ($format == 'A') ? array() : '';
        $retenciones = $sujetos;
        foreach ($pagos as $pago) {
            $proveedor = $pago->getProveedor();

            //retenciones
            $nrocomp = str_pad(str_replace('-', '', $pago->getNroPago()), 16, "0", STR_PAD_LEFT);
            $codimpuesto = str_pad($proveedor->getActividadComercial()->getCodigoImpuesto(), 4, "0", STR_PAD_LEFT);
            $codregimen = str_pad($proveedor->getActividadComercial()->getCodigoRegimen(), 3, "0", STR_PAD_LEFT);
            $importecomp = str_replace(',', '', str_pad(number_format($pago->getImporte(), 2), 16, "0", STR_PAD_LEFT));
            $basecalculo = str_replace(',', '', str_pad(number_format($pago->getBaseImponibleRentas(), 2), 14, "0", STR_PAD_LEFT));
            $importeret = str_replace(',', '', str_pad(number_format($pago->getMontoGanancias(), 2), 14, "0", STR_PAD_LEFT));
            $docret = substr(str_pad($proveedor->getCuit(), 20, " ", STR_PAD_RIGHT), -20, 20);
            // sujetos
            $cuit = substr(str_pad($proveedor->getCuit(), 11, " ", STR_PAD_LEFT), -11, 11);
            $nombre = substr(str_pad(UtilsController::sanear_string($proveedor->getNombre()), 20, " ", STR_PAD_RIGHT), -20, 20);
            $domicilio = substr(str_pad(UtilsController::sanear_string($proveedor->getDireccion()), 20, " ", STR_PAD_RIGHT), -20, 20);
            $localidad = substr(str_pad(UtilsController::sanear_string($proveedor->getLocalidad()->getName()), 20, " ", STR_PAD_RIGHT), -20, 20);
            $cp = substr(str_pad(UtilsController::sanear_string($proveedor->getLocalidad()->getCodPostal()), 8, " ", STR_PAD_RIGHT), -8, 8);
            if ($format == 'A') {
                $retenciones[] = array(
                    'codcomp' => '06',
                    'fechaemision' => $pago->getFecha()->format('d/m/Y'),
                    'nrocomp' => $nrocomp,
                    'importecomp' => $importecomp,
                    'codimpuesto' => $codimpuesto,
                    'codregimen' => $codregimen,
                    'codoperc' => '1',
                    'basecalculo' => $basecalculo,
                    'fecharetencion' => $pago->getFecha()->format('d/m/Y'),
                    'codcondicion' => '02',
                    'retsujsusp' => ' ',
                    'importe' => $importeret,
                    'porcexclusion' => '',
                    'fechaboletin' => '',
                    'tipodoc' => '80',
                    'nrodoc' => $docret,
                    'nrocert' => ''
                );
                $sujetos[] = array(
                    'nrodoc' => $cuit,
                    'razonsocial' => $nombre,
                    'domicilio' => $domicilio,
                    'localidad' => $localidad,
                    'provincia' => $proveedor->getLocalidad()->getProvincia()->getCodSicore(),
                    'cp' => $cp,
                    'tipodoc' => '80'
                );
            }
            else {
                $txtret = '06' .
                    $pago->getFecha()->format('d/m/Y') .
                    $nrocomp .
                    $importecomp .
                    $codimpuesto .
                    $codregimen .
                    '1' .
                    $basecalculo .
                    $pago->getFecha()->format('d/m/Y') .
                    '02' .
                    ' ' .
                    $importeret .
                    str_pad('', 6, " ", STR_PAD_LEFT) .
                    str_pad('', 10, " ", STR_PAD_LEFT) .
                    '80' .
                    $docret .
                    str_pad('', 14, " ", STR_PAD_LEFT);
                $retenciones = ( $retenciones == '') ? $txtret : $retenciones . PHP_EOL . $txtret;


                $txtsuj = substr(str_pad($pago->getProveedor()->getCuit(), 11, " ", STR_PAD_LEFT), -11, 11) .
                    $nombre .
                    $domicilio .
                    $localidad .
                    $proveedor->getLocalidad()->getProvincia()->getCodSicore() .
                    $cp .
                    '80';
                $sujetos = ( $sujetos == '') ? $txtsuj : $sujetos . PHP_EOL . $txtsuj;
            }
        }
        if ($format == 'T') {
            $retenciones = $retenciones . PHP_EOL;
            $sujetos = $sujetos . PHP_EOL;
        }
        return array('retenciones' => $retenciones, 'sujetos' => $sujetos);
    }

    /**
     * Devuelve las retenciones de rentas segun periodo
     */
    private function resultadoRentas($periodo, $em, $format = 'A') {
        $desde = UtilsController::toAnsiDate('01-' . $periodo);
        $ini = new \DateTime($desde);
        $hasta = $ini->format('Y-m-t');
        $pagos = $em->getRepository('ComprasBundle:PagoProveedor')->findRetencionesRentas($desde, $hasta);
        $retenciones = ($format == 'A') ? array() : '';
        $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);
        foreach ($pagos as $pago) {
            $proveedor = $pago->getProveedor();
            $cuitempresa = substr(str_pad(str_replace('-', '', $empresa->getCuit()), 11, " ", STR_PAD_LEFT), -11, 11);
            $sucursal = substr(str_pad($pago->getPrefijoNro(), 2, "0", STR_PAD_LEFT), -2, 2);
            $nrocomp = str_pad($pago->getPagoNro(), 6, "0", STR_PAD_LEFT);
            $montoret = number_format($pago->getMontoRetencionRentas(), 2, '', '');
            $codconcepto = str_pad($pago->getProveedor()->getCategoriaRentas()->getCodigoAtp(), 2, "0", STR_PAD_LEFT);
            $imponible = number_format($pago->getBaseImponibleRentas(), 2, '', '');
            $alicuota = number_format($pago->getRetencionRentas(), 2, '', '');

            $cuit = substr(str_pad($pago->getProveedor()->getCuit(), 11, " ", STR_PAD_LEFT), -11, 11);
            $nombre = substr(str_pad(UtilsController::sanear_string($proveedor->getNombre()), 30, " ", STR_PAD_RIGHT), -30, 30);
            $domicilio = substr(str_pad(UtilsController::sanear_string($proveedor->getDireccion()), 50, " ", STR_PAD_RIGHT), -50, 50);

            if ($format == 'A') {
                $retenciones[] = array(
                    'cuitempresa' => $cuitempresa,
                    'sucursal' => $sucursal,
                    'nrocomp' => $nrocomp,
                    'cuit' => $cuit,
                    'razonsocial' => $nombre,
                    'domicilio' => $domicilio,
                    'fecha' => $pago->getFecha()->format('dmY'),
                    'montoret' => str_pad($montoret, 11, "0", STR_PAD_LEFT),
                    'codconcepto' => $codconcepto,
                    'imponible' => str_pad($imponible, 11, "0", STR_PAD_LEFT),
                    'alicuota' => str_pad($alicuota, 11, "0", STR_PAD_LEFT)
                );
            }
            else {
                $txtret = $cuitempresa .
                    $sucursal .
                    $nrocomp .
                    $cuit .
                    $nombre .
                    $domicilio .
                    $pago->getFecha()->format('dmY') .
                    str_pad($montoret, 11, "0", STR_PAD_LEFT) .
                    $codconcepto .
                    str_pad($imponible, 11, "0", STR_PAD_LEFT) .
                    '0' .
                    str_pad(str_replace('.', ',', $pago->getRetencionGanancias()), 17, "0", STR_PAD_LEFT) .
                    str_pad($alicuota, 11, "0", STR_PAD_LEFT);
                $retenciones = ( $retenciones == '') ? $txtret : $retenciones . PHP_EOL . $txtret;
            }
        }
        if ($format == 'T') {
            $retenciones = $retenciones . PHP_EOL;
        }
        return $retenciones;
    }

}