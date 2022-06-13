<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ConfigBundle\Controller\UtilsController;
use Symfony\Component\HttpFoundation\JsonResponse;
class ImpuestoController extends Controller {

    /**
     * @Route("/ivaCompras", name="compras_libroiva")
     * @Method("GET")
     * @Template()
     */
    public function ivaComprasAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $desde = UtilsController::toAnsiDate($request->get('fecha_desde'));
        $hasta = UtilsController::toAnsiDate($request->get('fecha_hasta'));

        $facturas = $em->getRepository('ComprasBundle:Factura')->findByUnidadNegocio($this->get('session')->get('unidneg_id'));
        $notas = $em->getRepository('ComprasBundle:NotaDebCred')->findByUnidadNegocio($this->get('session')->get('unidneg_id'));

        $items = array();
        foreach ($facturas as $fact) {
            if ($fact->getFechaFactura()->format('Y-m-d') >= $desde && $fact->getFechaFactura()->format('Y-m-d') <= $hasta && $fact->getEstado() != 'CANCELADO') {
                $nro = explode('-', $fact->getNroComprobante());
                $item = array('fecha' => $fact->getFechaFactura(), 'tipoComprobante' => 'FC', 'tipo' => $fact->getTipoFactura(),
                    'tipofact' => $nro[0], 'nrocomp' => (isset($nro[1])) ? $nro[1] : '0', 'cuit' => $fact->getProveedor()->getCuit(),
                    'razon' => $fact->getProveedor()->getNombre(), 'iibb' => $fact->getProveedor()->getIibb(),
                    'neto' => $fact->getSubtotalNeto(),
                    'iva' => $fact->getIva(),
                    'nograv' => $fact->getTmc(),
                    'impuestos' => $fact->getImpuestoInterno(),
                    'retIVA' => $fact->getPercepcionIva(),
                    'percDgr' => $fact->getPercepcionDgr(),
                    'percMuni' => $fact->getPercepcionMunicipal(),
                    'total' => $fact->getTotal());
                array_push($items, $item);
            }
        }
        foreach ($notas as $nota) {
            if ($nota->getFecha()->format('Y-m-d') >= $desde && $nota->getFecha()->format('Y-m-d') <= $hasta) {
                $nro = explode('-', $nota->getNroComprobante());
                if ($nota->getSigno() == '-') {
                    $i = -1;
                    $tipo = 'NC';
                }
                else {
                    $i = 1;
                    $tipo = 'ND';
                }
                $item = array('fecha' => $nota->getFecha(), 'tipoComprobante' => $tipo, 'tipo' => $nota->getTipoNota(),
                    'tipofact' => $nro[0], 'nrocomp' => (isset($nro[1])) ? $nro[1] : '0', 'cuit' => $nota->getProveedor()->getCuit(),
                    'razon' => $nota->getProveedor()->getNombre(), 'iibb' => $nota->getProveedor()->getIibb(),
                    'neto' => $nota->getSubtotalNeto() * $i,
                    'iva' => $nota->getIva() * $i,
                    'nograv' => $nota->getTmc() * $i,
                    'impuestos' => $nota->getImpuestoInterno() * $i,
                    'retIVA' => $nota->getPercepcionIva() * $i,
                    'percDgr' => $nota->getPercepcionDgr() * $i,
                    'percMuni' => $nota->getPercepcionMunicipal() * $i,
                    'total' => $nota->getTotal() * $i);
                array_push($items, $item);
            }
        }

        $ord = usort($items, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Y-m-d'));
            $value2 = strtotime($a2['fecha']->format('Y-m-d'));
            return $value1 - $value2;
        });

        // verificar que sea mes completo para mostrar descarga
        $fecha1 = new \DateTime($desde);
        $fecha2 = new \DateTime($hasta);
        $periodo = '';
        if ($fecha1->format('d') == '01' && $fecha1->format('t') == $fecha2->format('d')) {
            $periodo = $fecha1->format('m') . '-' . $fecha1->format('Y');
        }

        return $this->render('AppBundle:Impuesto:libroiva.html.twig', array(
                    'tipo' => 'COMPRAS', 'path' => $this->generateUrl('compras_libroiva'), 'periodo' => $periodo,
                    'items' => $items, 'desde' => $request->get('fecha_desde'), 'hasta' => $request->get('fecha_hasta')
        ));
    }

    /**
     * @Route("/ivaVentas", name="ventas_libroiva")
     * @Method("GET")
     * @Template()
     */
    public function ivaVentasAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $desde = UtilsController::toAnsiDate($request->get('fecha_desde'));
        $hasta = UtilsController::toAnsiDate($request->get('fecha_hasta'));
        $unidneg = $this->get('session')->get('unidneg_id');

        $facturas = $em->getRepository('VentasBundle:Factura')->findByUnidadNegocio($unidneg);
        $notas = $em->getRepository('VentasBundle:NotaDebCred')->findByUnidadNegocio($unidneg);
        if (in_array($unidneg, array(1, 2))) {
            $aux = ($unidneg == 1) ? 2 : 1;
            // 1-> Gastronomía - 2-> Lavandería
            // buscar y agregar facturas y notas de la otra unidad de negocio que comparte libro de IVA
            $facturas2 = $em->getRepository('VentasBundle:Factura')->findByUnidadNegocio($aux);
            $notas2 = $em->getRepository('VentasBundle:NotaDebCred')->findByUnidadNegocio($aux);
            $facturas = array_merge($facturas, $facturas2);
            $notas = array_merge($notas, $notas2);
        }

        $items = array();
        foreach ($facturas as $fact) {
            if ($fact->getFechaFactura()->format('Y-m-d') >= $desde && $fact->getFechaFactura()->format('Y-m-d') <= $hasta) {
                $nro = explode('-', $fact->getNroFactura());
                if ($fact->getTotalIva() == 0) {
                    $neto = 0;
                    $nograv = $fact->getSubTotal();
                }
                else {
                    $neto = $fact->getSubTotal();
                    $nograv = 0;
                }
                $item = array('fecha' => $fact->getFechaFactura(), 'tipoComprobante' => 'FC', 'tipo' => $fact->getTipoFactura(),
                    'tipofact' => $nro[0], 'nrocomp' => (isset($nro[1])) ? $nro[1] : '0', 'cuit' => $fact->getCliente()->getCuit(),
                    'razon' => $fact->getCliente()->getNombre(), 'iibb' => '',
                    'impuestos' => '0', 'retIVA' => '0',
                    'neto' => $neto, 'iva' => $fact->getTotalIva(), 'nograv' => $nograv, 'total' => $fact->getTotal());
                array_push($items, $item);
            }
        }
        foreach ($notas as $nota) {
            if ($nota->getFecha()->format('Y-m-d') >= $desde && $nota->getFecha()->format('Y-m-d') <= $hasta) {
                $nro = explode('-', $nota->getNroComprobante());
                if ($nota->getSigno() == '-') {
                    $i = -1;
                    $tipo = 'NC';
                }
                else {
                    $i = 1;
                    $tipo = 'ND';
                }
                if ($nota->getTotalIva() == 0) {
                    $neto = 0;
                    $nograv = $nota->getSubTotal();
                }
                else {
                    $neto = $nota->getSubTotal();
                    $nograv = 0;
                }

                $item = array('fecha' => $nota->getFecha(), 'tipoComprobante' => $tipo, 'tipo' => $nota->getTipoNota(),
                    'tipofact' => $nro[0], 'nrocomp' => (isset($nro[1])) ? $nro[1] : '0', 'cuit' => $nota->getCliente()->getCuit(),
                    'razon' => $nota->getCliente()->getNombre(), 'iibb' => '',
                    'impuestos' => '0' * $i, 'retIVA' => '0' * $i,
                    'neto' => $neto * $i, 'iva' => $nota->getTotalIva() * $i, 'nograv' => $nograv * $i, 'total' => $nota->getTotal() * $i);
                array_push($items, $item);
            }
        }

        $ord = usort($items, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Y-m-d'));
            $value2 = strtotime($a2['fecha']->format('Y-m-d'));
            return $value1 - $value2;
        });


        return $this->render('AppBundle:Impuesto:libroiva.html.twig', array(
                    'tipo' => 'VENTAS', 'path' => $this->generateUrl('ventas_libroiva'),
                    'items' => $items, 'desde' => $request->get('fecha_desde'), 'hasta' => $request->get('fecha_hasta')
        ));
    }

    /**
     * @Route("/afipCompras", name="compras_informeafip")
     * @Method("GET")
     * @Template()
     */
    public function afipComprasAction(Request $request) {
        $periodo = $request->get('periodo');
        $unidneg = $this->get('session')->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();

        $resultado = $this->getReginfoCompras($em, $unidneg, $periodo, 'A');

        return $this->render('AppBundle:Impuesto:informe-afip.html.twig', array(
                    'tipo' => 'COMPRAS', 'path' => $this->generateUrl('compras_informeafip'),
                    'resultado' => $resultado, 'periodo' => $periodo
        ));
    }

    /*
     * FUNCION PARA OBTENER LOS DATOS DE COMPRAS -
     * FORMAT => A (ARRAY) / T (TXT)
     */

    private function getReginfoCompras($em, $unidneg, $mes, $format = 'A') {
        if ($mes) {
            $desde = UtilsController::toAnsiDate('01-' . $mes);
            $ini = new \DateTime($desde);
            $hasta = $ini->format('Y-m-t');
        }
        else {
            $desde = $hasta = '';
        }
        $valinic = ($format == 'A') ? array() : '';
        $reginfoCbtes = $reginfoAlicuotas = $valinic;
        $toterrores = array('CUIT' => 0, 'COMPROBANTE' => 0, 'ALICUOTA' => 0);
        /*
         * COMPROBANTES
         */
        $comprobantes = $em->getRepository('ComprasBundle:Factura')->findComprobantesByPeriodoUnidadNegocio($desde, $hasta, $unidneg);
        foreach ($comprobantes as $comprob) {
            $operacionesExentas = $cantAlicuotas = 0;
            $error = array();
            $save = false;
            if ($comprob['tipocomp'] == 'FAC-') {
                $objComprob = $em->getRepository('ComprasBundle:Factura')->find($comprob['id']);
                $repo = 'ComprasBundle:Factura';
                $fecha = $objComprob->getFechaFactura()->format('Ymd');
            }
            else {
                $objComprob = $em->getRepository('ComprasBundle:NotaDebCred')->find($comprob['id']);
                $repo = 'ComprasBundle:NotaDebCred';
                $fecha = $objComprob->getFecha()->format('Ymd');
            }
            // completa tipo de comprobante según afip si no posee
            if (!$objComprob->getAfipComprobante()) {
                $afipComp = $em->getRepository('ConfigBundle:AfipComprobante')->getIdByTipo($comprob['tipocomp'] . $comprob['tipo']);
                $objComprob->setAfipComprobante($afipComp);
                $save = true;
            }
            // completa punto de venta y nro de comprobante afip
            if (!$objComprob->getAfipPuntoVenta() || !$objComprob->getAfipNroComprobante() ||
                    intval($objComprob->getAfipPuntoVenta()) == 0 || intval($objComprob->getAfipNroComprobante()) == 0 ||
                    !is_numeric($objComprob->getAfipPuntoVenta()) || !is_numeric($objComprob->getAfipNroComprobante())) {
                if (strpos($objComprob->getNroComprobante(), '-') === false) {
                    $error[] = 'COMPROBANTE';
                    $toterrores['COMPROBANTE'] ++;
                    $objComprob->setAfipNroComprobante($objComprob->getNroComprobante());
                    $objComprob->setAfipPuntoVenta('');
                }
                else {
                    $data = explode('-', $objComprob->getNroComprobante());
                    if (intval($data[0]) == 0 || intval($data[1]) == 0 || count($data) !== 2) {
                        $error[] = 'COMPROBANTE';
                        $toterrores['COMPROBANTE'] ++;
                        $objComprob->setAfipNroComprobante($objComprob->getNroComprobante());
                        $objComprob->setAfipPuntoVenta('');
                    }
                    else {
                        $ptovta = substr(str_pad($data[0], 5, "0", STR_PAD_LEFT), -5, 5);
                        $nrocomp = substr(str_pad($data[1], 20, "0", STR_PAD_LEFT), -20, 20);
                        $objComprob->setAfipPuntoVenta($ptovta);
                        $objComprob->setAfipNroComprobante($nrocomp);
                    }
                    $save = true;
                }
            }

            // grabar las correcciones de la factura
            if ($save) {
                $em->persist($objComprob);
                $em->flush();
            }
            // Cuit y Proveedor
            if (!UtilsController::validarCuit($objComprob->getProveedor()->getCuit())) {
                $error[] = 'CUIT';
                $toterrores['CUIT'] ++;
            }
            $cuit = str_replace('-', '', $objComprob->getProveedor()->getCuit());
            //$proveedor = substr($objComprob->getProveedor()->getNombre(), 0, 30);
            $auxstring = substr($objComprob->getProveedor()->getNombre(), 0, 30);
            $proveedor = UtilsController::sanear_string($auxstring);
            $proveedorId = $objComprob->getProveedor()->getId();

            $cantAlicuotas = $em->getRepository($repo)->getCantidadAlicuotas($comprob['id']);
            $totaliva = 0;
            $totalneto = 0;
            /*
             * ALICUOTAS
             */
            if (count($cantAlicuotas) == 1) {
                // una sola alicuota, se usa valores generales de la factura
                $codAlicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->find($cantAlicuotas[0]['id']);

                if ($codAlicuota->getValor() == 0) {
                    $operacionesExentas = number_format($objComprob->getSubtotalNeto(), 2, '', '');
                    $cantAlicuotas = NULL;
                }
                else {

                    $totaliva = number_format($objComprob->getIva(), 2, '', '');
                    $totalneto = number_format($objComprob->getSubtotalNeto(), 2, '', '');
                    if ($format == 'A') {
                        $alic = array(
                            'tipoComprobante' => $objComprob->getAfipComprobante()->getCodigo(),
                            'puntoVenta' => str_pad($objComprob->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                            'nroComprobante' => str_pad($objComprob->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                            'cuit' => $cuit,
                            'netoGravado' => str_pad($totalneto, 15, "0", STR_PAD_LEFT),
                            'codAlicuota' => $codAlicuota->getCodigo(),
                            'liquidado' => str_pad($totaliva, 15, "0", STR_PAD_LEFT),
                            'error' => $error,
                            'id' => $objComprob->getId()
                        );
                        array_push($reginfoAlicuotas, $alic);
                    }
                    else {
                        $txtalic = $objComprob->getAfipComprobante()->getCodigo() .
                                str_pad($objComprob->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                                str_pad($objComprob->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                                '80' .
                                str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                                str_pad(number_format($objComprob->getSubtotalNeto(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                                $codAlicuota->getCodigo() .
                                str_pad(number_format($objComprob->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT);
                        $reginfoAlicuotas = ( $reginfoAlicuotas == '') ? $txtalic : $reginfoAlicuotas . PHP_EOL . $txtalic;
                    }
                }
            }
            else {
                // más de una alicuota, se calculan los valores
                $totneto = $totiva = 0;
                $alic = array();
                foreach ($cantAlicuotas as $item) {
                    $neto = $liq = 0;
                    $codAlicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->find($item['id']);

                    foreach ($objComprob->getDetalles() as $det) {
                        if ($det->getAfipAlicuota()->getId() == $codAlicuota->getId()) {
                            $neto += $det->getPrecio() * $det->getCantidad();
                        }
                    }
                    $auxliq = $neto * ($codAlicuota->getValor() / 100);
                    $liq = number_format($auxliq, 2, '', '');
                    $neto = number_format($neto, 2, '', '');
                    if ($codAlicuota->getValor() == 0) {
                        $operacionesExentas = $neto;
                    }
                    else {
                        $alic[] = array(
                            'tipoComprobante' => $objComprob->getAfipComprobante()->getCodigo(),
                            'puntoVenta' => str_pad($objComprob->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                            'nroComprobante' => str_pad($objComprob->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                            'cuit' => $cuit,
                            'netoGravado' => str_pad($neto, 15, "0", STR_PAD_LEFT),
                            'codAlicuota' => $codAlicuota->getCodigo(),
                            'liquidado' => str_pad($liq, 15, "0", STR_PAD_LEFT),
                            'error' => $error,
                            'id' => $objComprob->getId()
                        );
                    }
                    $totneto += $neto;
                    $totiva += $liq;
                }
                // totales
                $totaliva = $totiva;
                $totalneto = $totneto;
                if ($totaliva <> number_format($objComprob->getIva(), 2, '', '') || $totalneto <> number_format($objComprob->getSubtotalNeto(), 2, '', '')) {
                    $dif1 = $totaliva - number_format($objComprob->getIva(), 2, '', '');
                    $dif2 = $totalneto - number_format($objComprob->getSubtotalNeto(), 2, '', '');

                    if (abs($dif1) > 1 || abs($dif2) > 1) {
                        $error[] = 'ALICUOTA';
                        $toterrores['ALICUOTA'] ++;
                    }
                }
                foreach ($alic as $i) {
                    if ($format == 'A') {
                        $i['error'] = $error;
                        array_push($reginfoAlicuotas, $i);
                    }
                    else {
                        $txtalic = $objComprob->getAfipComprobante()->getCodigo() .
                                str_pad($objComprob->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                                str_pad($objComprob->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                                '80' .
                                str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                                str_pad($i['netoGravado'], 15, "0", STR_PAD_LEFT) .
                                $i['codAlicuota'] .
                                str_pad($i['liquidado'], 15, "0", STR_PAD_LEFT);
                        $reginfoAlicuotas = ($reginfoAlicuotas == '') ? $txtalic : $reginfoAlicuotas . PHP_EOL . $txtalic;
                    }
                }
            }

            /*
             * COMPROBANTES
             */
            $codOperacion = ($objComprob->getIva() == 0 ) ? 'A' : ' ';
            $perciva = number_format($objComprob->getPercepcionIva(), 2, '', '');
            $perciibb = number_format($objComprob->getPercepcionDgr(), 2, '', '');
            $percmuni = number_format($objComprob->getPercepcionMunicipal(), 2, '', '');
            $impint = number_format($objComprob->getImpuestoInterno(), 2, '', '');
            $totaloperacion = $totalneto + $totaliva + $perciva + $perciibb + $percmuni + $impint;
            if ($format == 'A') {
                $comp = array(
                    'fecha' => $fecha,
                    'tipoComprobante' => $objComprob->getAfipComprobante()->getCodigo(),
                    'puntoVenta' => str_pad($objComprob->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                    'nroComprobante' => str_pad($objComprob->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                    'despacho' => str_pad(" ", 16, " "),
                    'cuit' => $cuit,
                    'proveedor' => UtilsController::mb_str_pad($proveedor, 30, " ", STR_PAD_RIGHT),
                    'total' => str_pad($totaloperacion, 15, "0", STR_PAD_LEFT),
                    'nograv' => str_pad(number_format($objComprob->getTmc(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'exe' => str_pad($operacionesExentas, 15, "0", STR_PAD_LEFT),
                    'percIva' => str_pad($perciva, 15, "0", STR_PAD_LEFT),
                    'percImpNac' => str_pad(number_format('0', 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percIIBB' => str_pad($perciibb, 15, "0", STR_PAD_LEFT),
                    'percMuni' => str_pad($percmuni, 15, "0", STR_PAD_LEFT),
                    'impInterno' => str_pad($impint, 15, "0", STR_PAD_LEFT),
                    'moneda' => 'PES',
                    'tipoCambio' => str_pad("0001000000", 10, "0"),
                    'cantAlicuotas' => count($cantAlicuotas),
                    'codOperacion' => $codOperacion,
                    //'credFiscalComp' => str_pad(number_format($objComprob->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'credFiscalComp' => str_pad($totaliva, 15, "0", STR_PAD_LEFT),
                    'otrosTributos' => str_pad("0", 15, "0"),
                    'cuitEmisor' => str_pad("0", 11, "0"),
                    'nombreEmisor' => str_pad(" ", 30, " "),
                    'ivaComision' => str_pad("0", 15, "0"),
                    'error' => $error,
                    'proveedorId' => $proveedorId,
                    'id' => $objComprob->getId()
                );
                array_push($reginfoCbtes, $comp);
            }
            else {
                $comp = $fecha .
                        $objComprob->getAfipComprobante()->getCodigo() .
                        str_pad($objComprob->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                        str_pad($objComprob->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                        str_pad(" ", 16, " ") .
                        '80' .
                        str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                        UtilsController::mb_str_pad($proveedor, 30) .
                        str_pad(number_format($objComprob->getTotalsinBonificacion(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($objComprob->getTmc(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad($operacionesExentas, 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($objComprob->getPercepcionIva(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format('0', 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($objComprob->getPercepcionDgr(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($objComprob->getPercepcionMunicipal(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($objComprob->getImpuestoInterno(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        'PES' .
                        str_pad("0001000000", 10, "0") .
                        count($cantAlicuotas) .
                        $codOperacion .
                        str_pad($totaliva, 15, "0", STR_PAD_LEFT) .
                        str_pad("0", 15, "0") .
                        str_pad("0", 11, "0") .
                        str_pad(" ", 30, " ") .
                        str_pad("0", 15, "0")
                ;
                $reginfoCbtes = ($reginfoCbtes == '') ? $comp : $reginfoCbtes . PHP_EOL . $comp;
            }
        }


        /*
         * MERGE FACTURAS Y NOTAS
         */
        $resultado = array('comprobantes' => $reginfoCbtes, 'alicuotas' => $reginfoAlicuotas, 'errores' => $toterrores);

        return $resultado;
    }

    /**
     * @Route("/afipVentas", name="ventas_informeafip")
     * @Method("GET")
     * @Template()
     */
    public function afipVentasAction(Request $request) {
        $periodo = $request->get('periodo');
        $unidneg = $this->get('session')->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();

        $resultado = $this->getReginfoVentas($em, $unidneg, $periodo, 'A');

        return $this->render('AppBundle:Impuesto:informe-afip.html.twig', array(
                    'tipo' => 'VENTAS', 'path' => $this->generateUrl('ventas_informeafip'),
                    'resultado' => $resultado, 'periodo' => $periodo
        ));
    }

    /*
     * FUNCION PARA OBTENER LOS DATOS DE VENTAS -
     * FORMAT => A (ARRAY) / T (TXT)
     */

    private function getReginfoVentas($em, $unidneg, $mes, $format = 'A') {
        if ($mes) {
            $desde = UtilsController::toAnsiDate('01-' . $mes);
            $ini = new \DateTime($desde);
            $hasta = $ini->format('Y-m-t');
        }
        else {
            $desde = $hasta = '';
        }
        $toterrores = array('CUIT' => 0, 'COMPROBANTE' => 0, 'ALICUOTA' => 0);
        $valinic = ($format == 'A') ? array() : '';
        $reginfoCbtes = $reginfoAlicuotas = $valinic;
        /*
         * COMPROBANTES
         */
        $comprobantes = $em->getRepository('VentasBundle:Factura')->findComprobantesByPeriodoUnidadNegocio($desde, $hasta, $unidneg);
        foreach ($comprobantes as $comprob) {

            $operacionesExentas = $cantAlicuotas = 0;
            $error = array();
            $tipo = explode('-', $comprob['tipocomp']);
            if ($tipo[0] == 'FAC' || $tipo[0] == 'TICK') {
                $objComprob = $em->getRepository('VentasBundle:Cobro')->find($comprob['id']);
                $fecha = $objComprob->getFechaCobro()->format('Ymd');
                $id = $objComprob->getVenta()->getId();
                $facturaElectronica = $objComprob->getFacturaElectronica();
            }
            else {
                $objComprob = $em->getRepository('VentasBundle:NotaDebCred')->find($comprob['id']);
                $fecha = $objComprob->getFecha()->format('Ymd');
                $id = $objComprob->getId();
                $facturaElectronica = $objComprob->getNotaElectronica();
            }

            // Cuit y Proveedor
            /*if (!UtilsController::validarCuit($objComprob->getCliente()->getCuit())) {
                $error[] = 'CUIT';
                $toterrores['CUIT'] ++;
            }*/
            $cuit = str_replace('-', '', $objComprob->getCliente()->getCuit());
            $auxstring = substr($objComprob->getCliente()->getNombre(), 0, 30);
            $cliente = UtilsController::sanear_string($auxstring);
            $clienteId = $objComprob->getCliente()->getId();

            $cantAlicuotas = $em->getRepository('VentasBundle:FacturaElectronica')->getCantidadAlicuotas($tipo[0],$id);
            $totaliva = 0;
            $totalneto = 0;
            /*
             * ALICUOTAS
             */
            if ( $tipo[0] == 'FAC' || $tipo[0] == 'TICK') {
                // usar ventas para las alicuotas
                $objComprob = $objComprob->getVenta();
            }
            if (count($cantAlicuotas) == 1) {
                // una sola alicuota, se usa valores generales de la factura
                $codAlicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->findOneByValor( number_format( $cantAlicuotas[0]['alicuota'],2));
                if ($codAlicuota->getValor() == 0) {
                    $operacionesExentas = $objComprob->getTotal();
                    $cantAlicuotas = NULL;
                }
                else {
                    $totaliva = number_format($objComprob->getTotalIva(), 2, '', '');
                    $totalneto = number_format($objComprob->getMontoTotal() - $objComprob->getTotalIva(), 2, '', '');
                    if ($format == 'A') {
                        $alic = array(
                            'tipoComprobante' => $facturaElectronica->getTipoComprobante()->getCodigo(),
                            'puntoVenta' => str_pad($facturaElectronica->getPuntoVenta(), 5, "0", STR_PAD_LEFT),
                            'nroComprobante' => str_pad($facturaElectronica->getNroComprobante(), 20, "0", STR_PAD_LEFT),
                            'netoGravado' => str_pad($totalneto, 15, "0", STR_PAD_LEFT),
                            'codAlicuota' => $codAlicuota->getCodigo(),
                            'liquidado' => str_pad($totaliva, 15, "0", STR_PAD_LEFT),
                            'error' => $error
                        );
                        array_push($reginfoAlicuotas, $alic);
                    }
                    else {
                        $txtalic = $facturaElectronica->getTipoComprobante()->getCodigo() .
                                str_pad($facturaElectronica->getPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                                str_pad($facturaElectronica->getNroComprobante(), 20, "0", STR_PAD_LEFT) .
                                str_pad($totalneto, 15, "0", STR_PAD_LEFT) .
                                $codAlicuota->getCodigo() .
                                str_pad($totaliva, 15, "0", STR_PAD_LEFT);
                        $reginfoAlicuotas = ( $reginfoAlicuotas == '') ? $txtalic : $reginfoAlicuotas . PHP_EOL . $txtalic;
                    }
                }
            }
            else {
                // más de una alicuota, se calculan los valores
                $totneto = $totiva = 0;
                $alic = array();
                foreach ($cantAlicuotas as $item) {
                    $alicuota = number_format( $item['alicuota'],2);
                    $neto = $liq = 0;
                    $codAlicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->findOneByValor( $alicuota );

                    foreach ($objComprob->getDetalles() as $det) {
                        $alicdet = number_format( $det->getAlicuota(),2);
                        if ($alicdet == $codAlicuota->getValor()) {
                            $neto += $det->getPrecio() * $det->getCantidad();
                            $liq += $det->getIvaItem();
                        }
                    }
                    $liq = number_format($liq, 2, '', '');
                    $neto = number_format($neto, 2, '', '');
                    if ($codAlicuota->getValor() == 0) {
                        $operacionesExentas = $neto;
                    }
                    else {
                        $alic[] = array(
                            'tipoComprobante' => $facturaElectronica->getTipoComprobante()->getCodigo(),
                            'puntoVenta' => str_pad($facturaElectronica->getPuntoVenta(), 5, "0", STR_PAD_LEFT),
                            'nroComprobante' => str_pad($facturaElectronica->getNroComprobante(), 20, "0", STR_PAD_LEFT),
                            'netoGravado' => str_pad($neto, 15, "0", STR_PAD_LEFT),
                            'codAlicuota' => $codAlicuota->getCodigo(),
                            'liquidado' => str_pad($liq, 15, "0", STR_PAD_LEFT),
                            'error' => $error
                        );
                    }
                    $totneto += $neto;
                    $totiva += $liq;
                }
                // totales
                $totaliva = $totiva;
                $totalneto = $totneto;
                if ($totaliva <> number_format($objComprob->getTotalIva(), 2, '', '') || $totalneto <> number_format($objComprob->getMontoTotal() - $objComprob->getTotalIva(), 2, '', '')) {
                    $dif1 = $totaliva - number_format($objComprob->getTotalIva(), 2, '', '');
                    $dif2 = $totalneto - number_format($objComprob->getMontoTotal() - $objComprob->getTotalIva(), 2, '', '');

                    if (abs($dif1) > 1 || abs($dif2) > 1) {
                        $error[] = 'ALICUOTA';
                        $toterrores['ALICUOTA'] ++;
                    }
                }
                foreach ($alic as $i) {
                    if ($format == 'A') {
                        $i['error'] = $error;
                        array_push($reginfoAlicuotas, $i);
                    }
                    else {
                        $txtalic = $facturaElectronica->getTipoComprobante()->getCodigo() .
                                str_pad($facturaElectronica->getPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                                str_pad($facturaElectronica->getNroComprobante(), 20, "0", STR_PAD_LEFT) .
                                str_pad($i['netoGravado'], 15, "0", STR_PAD_LEFT) .
                                $codAlicuota->getCodigo() .
                                str_pad($i['liquidado'], 15, "0", STR_PAD_LEFT);
                        $reginfoAlicuotas = ($reginfoAlicuotas == '') ? $txtalic : $reginfoAlicuotas . PHP_EOL . $txtalic;
                    }
                }
            }

            /*
             * COMPROBANTES
             */
            $codOperacion = ($objComprob->getTotalIva() == 0 ) ? 'A' : ' ';
            //$pagovto = UtilsController::toAnsiDate($objComprob->getPagoVto(), false);
            $pagovto = '';
            if ($format == 'A') {
                $comp = array(
                    'fecha' => $fecha,
                    'tipoComprobante' => $facturaElectronica->getTipoComprobante()->getCodigo(),
                    'puntoVenta' => str_pad($facturaElectronica->getPuntoVenta(), 5, "0", STR_PAD_LEFT),
                    'nroComprobante' => str_pad($facturaElectronica->getNroComprobante(), 20, "0", STR_PAD_LEFT),
                    'nroComprobanteHasta' => str_pad(number_format('0', 2, '', ''), 20, "0", STR_PAD_LEFT),
                    'cuit' => str_pad($cuit, 11, "0", STR_PAD_LEFT),
                    'cliente' => $cliente,
                    'total' => str_pad(number_format($objComprob->getMontoTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'nograv' => str_pad("0", 15, "0"),
                    'nocateg' => str_pad("0", 15, "0"),
                    'exe' => str_pad(number_format($operacionesExentas, 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percImpNac' => str_pad("0", 15, "0"),
                    'percIIBB' => str_pad("0", 15, "0"),
                    'percMuni' => str_pad("0", 15, "0"),
                    'impInterno' => str_pad("0", 15, "0"),
                    'moneda' => 'PES',
                    'tipoCambio' => str_pad("0001000000", 10, "0"),
                    'cantAlicuotas' => count($cantAlicuotas),
                    'codOperacion' => $codOperacion,
                    'otrosTributos' => str_pad("0", 15, "0"),
                    'pagoVto' => $pagovto,
                    'error' => $error,
                    'clienteId' => $clienteId,
                    'id' => $objComprob->getId()
                );
                array_push($reginfoCbtes, $comp);
            }
            else {

                $comp = $fecha .
                        $facturaElectronica->getTipoComprobante()->getCodigo() .
                        str_pad($facturaElectronica->getPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                        str_pad($facturaElectronica->getNroComprobante(), 20, "0", STR_PAD_LEFT) .
                        str_pad("0", 20, "0") .
                        '80' .
                        str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                        UtilsController::mb_str_pad($cliente, 30) .
                        str_pad(number_format($objComprob->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad("0", 15, "0") .
                        str_pad("0", 15, "0") .
                        str_pad(number_format($operacionesExentas, 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad("0", 15, "0") .
                        str_pad("0", 15, "0") .
                        str_pad("0", 15, "0") .
                        str_pad("0", 15, "0") .
                        'PES' .
                        str_pad("0001000000", 10, "0") .
                        count($cantAlicuotas) .
                        $codOperacion .
                        str_pad("0", 15, "0") .
                        $pagovto
                ;
                $reginfoCbtes = ($reginfoCbtes == '') ? $comp : $reginfoCbtes . PHP_EOL . $comp;
            }
        }

        /*
         * MERGE FACTURAS Y NOTAS
         */

        $resultado = array('comprobantes' => $reginfoCbtes, 'alicuotas' => $reginfoAlicuotas, 'errores' => $toterrores);

        return $resultado;
    }

    /**
     * @Route("/afipExportTxt", name="reginfo_export_txt")
     * @Method("GET")
     * @Template()
     */
    public function afipExportTxt(Request $request) {
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

    /**
     * @Route("/updateNrocomprobante", name="update_nrocomprobante")
     * @Method("GET")
     */
    public function updateNrocomprobanteAction(Request $request) {
        $id = $request->get('id');
        $bundle = $request->get('bundle');
        $tipocomp = $request->get('tipocomp');
        $valor = explode('-', $request->get('valor'));
        $em = $this->getDoctrine()->getManager();
        $afipComp = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByCodigo($tipocomp);
        if (strpos($afipComp->getValor(), 'FAC') > -1) {
            $objComprob = $em->getRepository($bundle . 'Bundle:Factura')->find($id);
        }
        else {
            $objComprob = $em->getRepository($bundle . 'Bundle:NotaDebCred')->find($id);
        }

        $ptovta = substr(str_pad($valor[0], 5, "0", STR_PAD_LEFT), -5, 5);
        $nrocomp = substr(str_pad($valor[1], 20, "0", STR_PAD_LEFT), -20, 20);
        if (intval($ptovta) == 0 || intval($nrocomp) == 0) {
            return new JsonResponse('ERROR');
        }
        $objComprob->setAfipPuntoVenta($ptovta);
        $objComprob->setAfipNroComprobante($nrocomp);
        $nuevoComp = $ptovta . '-' . $nrocomp;
        if ($bundle == 'Compras') {
            $objComprob->setNroComprobante($nuevoComp);
        }
        else {
            if (strpos($afipComp->getValor(), 'FAC') > -1) {
                $objComprob->setNroFactura($nuevoComp);
            }
            else {
                $objComprob->setNroNotaDebCred($nuevoComp);
            }
        }
        $em->persist($objComprob);
        $em->flush();
        return new JsonResponse($ptovta . '-' . $nrocomp);
    }

    /**
     * @Route("/editComprobanteCompras", name="edit_comprobante_compras")
     * @Method("GET")
     */
    public function editComprobanteComprasAction(Request $request) {
        $id = $request->get('id');
        $tipocomp = $request->get('tipocomp');
        $em = $this->getDoctrine()->getManager();

        $createData = $this->createEditForm($em, $id, $tipocomp);

        return $this->render($createData['partial'], array(
                    'entity' => $createData['entity'],
                    'form' => $createData['form']->createView(),
                    'totales' => $createData['totales']
        ));
    }

    /**
     * @Route("updateComprobanteCompras/{id}", name="update_comprobante_compras")
     * @Method("PUT")
     */
    public function updateComprobanteCompras(Request $request, $id) {
        $data = ($request->get('comprasbundle_factura')) ? $request->get('comprasbundle_factura') : $request->get('comprasbundle_notadebcred');
        $tipocomp = $data['afipComprobante'];

        $em = $this->getDoctrine()->getManager();

        $arrayData = $this->createEditForm($em, $id, $tipocomp);
        $form = $arrayData['form'];
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('compras_informeafip'));
        }

        return $this->render($arrayData['partial'], array(
                    'entity' => $arrayData['entity'],
                    'form' => $form->createView()
        ));
    }

    private function createEditForm($em, $id, $tipocomp) {
        $codigo = str_pad($tipocomp, 3, "0", STR_PAD_LEFT);
        $afipComp = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByCodigo($codigo);

        if (strpos($afipComp->getValor(), 'FAC') > -1) {
            UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura_edit');
            $objComprob = $em->getRepository('ComprasBundle:Factura')->find($id);
            $editForm = $this->createForm(new \ComprasBundle\Form\FacturaType(), $objComprob, array(
                'action' => $this->generateUrl('update_comprobante_compras', array('id' => $objComprob->getId())),
                'method' => 'PUT',
            ));
            $partial = 'ComprasBundle:Factura:edit-admin.html.twig';
        }
        else {
            UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_notadebcred');
            $objComprob = $em->getRepository('ComprasBundle:NotaDebCred')->find($id);
            $editForm = $this->createForm(new \ComprasBundle\Form\NotaDebCredType(), $objComprob, array(
                'action' => $this->generateUrl('update_comprobante_compras', array('id' => $objComprob->getId())),
                'method' => 'PUT',
            ));
            $partial = 'ComprasBundle:NotaDebCred:edit-admin.html.twig';
        }
        $totneto[] = $totliq[] = array();
        foreach ($objComprob->getDetalles() as $det) {
            $idx = $det->getAfipAlicuota()->getValor();
            $neto = $det->getPrecio() * $det->getCantidad();
            $liq = $neto * ($idx / 100);
            $totneto[$idx] = (array_key_exists($idx, $totneto)) ? $totneto[$idx] + $neto : $neto;
            $totliq[$idx] = (array_key_exists($idx, $totliq)) ? $totliq[$idx] + $liq : $liq;
        }

        return array('entity' => $objComprob, 'form' => $editForm, 'partial' => $partial, 'totales' => array('neto' => $totneto, 'liq' => $totliq));
    }

}