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
        return $this->render('AppBundle:Impuesto:libroiva.html.twig', array(
                    'tipo' => 'COMPRAS', 'path' => $this->generateUrl('compras_libroiva'),
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
                    'comprobantes' => $resultado['comprobantes'], 'periodo' => $periodo,
                    'alicuotas' => $resultado['alicuotas']
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
        $comprobantes = $alicuotas = $valinic;

        /*
         * FACTURAS
         */
        $facturas = $em->getRepository('ComprasBundle:Factura')->findFacturasByPeriodoUnidadNegocio($desde, $hasta, $unidneg);
        foreach ($facturas as $fact) {
            $operacionesExentas = $cantAlicuotas = 0;
            $error = array();
            //if ($fact->getFechaFactura()->format('Y-m-d') >= $desde && $fact->getFechaFactura()->format('Y-m-d') <= $hasta && $fact->getEstado() != 'CANCELADO') {
            // completa tipo de comprobante según afip si no posee
            $save = false;
            if (!$fact->getAfipComprobante()) {
                $afipComp = $em->getRepository('ConfigBundle:AfipComprobante')->getIdByTipo('FAC-' . $fact->getTipoFactura());
                $fact->setAfipComprobante($afipComp);
                $save = true;
            }
            // completa punto de venta y nro de comprobante afip
            if (!$fact->getAfipPuntoVenta()) {
                if (strpos($fact->getNroComprobante(), '-') === false) {
                    $error[] = 'COMPROBANTE';
                    $fact->setAfipNroComprobante($fact->getNroComprobante());
                }
                else {
                    $data = split('-', $fact->getNroComprobante());
                    $fact->setAfipPuntoVenta(str_pad($data[0], 5, "0", STR_PAD_LEFT));
                    $fact->setAfipNroComprobante(str_pad($data[1], 20, "0", STR_PAD_LEFT));
                    $save = true;
                }
            }
            // grabar las correcciones de la factura
            if ($save) {
                $em->persist($fact);
                $em->flush();
            }

            if (!UtilsController::validarCuit($fact->getProveedor()->getCuit())) {
                $error[] = 'CUIT';
            }
            $cuit = str_replace('-', '', $fact->getProveedor()->getCuit());
            $proveedor = substr($fact->getProveedor()->getNombre(), 0, 30);
            // contabilizar la cantidad de alicuotas de los items de la factura
            $cantAlicuotas = $em->getRepository('ComprasBundle:Factura')->getCantidadAlicuotas($fact->getId());

            /*
             * ALICUOTAS FACTURAS
             */
            if (count($cantAlicuotas) == 1) {
                // una sola alicuota, se usa valores generales de la factura
                $codAlicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->find($cantAlicuotas[0]['id']);
                if ($codAlicuota->getValor() == 0) {
                    $operacionesExentas = $fact->getSubtotalNeto();
                }

                if ($format == 'A') {
                    $alic = array(
                        'tipoComprobante' => $fact->getAfipComprobante()->getCodigo(),
                        'puntoVenta' => str_pad($fact->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                        'nroComprobante' => str_pad($fact->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                        'cuit' => $cuit,
                        'netoGravado' => str_pad(number_format($fact->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                        'codAlicuota' => $codAlicuota->getCodigo(),
                        'liquidado' => str_pad(number_format($fact->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                        'error' => $error
                    );
                    array_push($alicuotas, $alic);
                }
                else {
                    $txtalic = $fact->getAfipComprobante()->getCodigo() .
                            str_pad($fact->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                            str_pad($fact->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                            '80' .
                            str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                            str_pad(number_format($fact->getSubtotalNeto(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                            $codAlicuota->getCodigo() .
                            str_pad(number_format($fact->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT);
                    $alicuotas = ( $alicuotas == '') ? $txtalic : $alicuotas . PHP_EOL . $txtalic;
                }
            }
            else {
                // más de una alicuota, se calculan los valores
                $totneto = $totiva = 0;
                $alic = array();
                foreach ($cantAlicuotas as $item) {
                    $neto = $liq = 0;
                    $codAlicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->find($item['id']);

                    foreach ($fact->getDetalles() as $det) {
                        if ($det->getAfipAlicuota()->getId() == $codAlicuota->getId()) {
                            $neto += $det->getPrecio() * $det->getCantidad();
                        }
                    }
                    if ($codAlicuota->getValor() == 0) {
                        $operacionesExentas = $neto;
                    }
                    $liq = $neto * ($codAlicuota->getValor() / 100);
                    $alic[] = array(
                        'tipoComprobante' => $fact->getAfipComprobante()->getCodigo(),
                        'puntoVenta' => str_pad($fact->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                        'nroComprobante' => str_pad($fact->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                        'cuit' => $cuit,
                        'netoGravado' => str_pad(number_format($neto, 2, '', ''), 15, "0", STR_PAD_LEFT),
                        'codAlicuota' => $codAlicuota->getCodigo(),
                        'liquidado' => str_pad(number_format($liq, 2, '', ''), 15, "0", STR_PAD_LEFT),
                        'error' => $error
                    );
                    $totneto += $neto;
                    $totiva += $liq;
                }
                if ($totneto <> $fact->getTotal() || $totiva <> $fact->getIva()) {
                    $error[] = 'ALICUOTA';
                }
                foreach ($alic as $i) {
                    if ($format == 'A') {
                        $i['error'] = $error;
                        array_push($alicuotas, $i);
                    }
                    else {
                        $txtalic = $fact->getAfipComprobante()->getCodigo() .
                                str_pad($fact->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                                str_pad($fact->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                                '80' .
                                str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                                str_pad(number_format($fact->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                                $codAlicuota->getCodigo() .
                                str_pad(number_format($fact->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT);
                        $alicuotas = ($alicuotas == '') ? $txtalic : $alicuotas . PHP_EOL . $txtalic;
                    }
                }
            }

            /*
             * COMPROBANTES FACTURAS
             */
            $codOperacion = ($fact->getIva() == 0 ) ? 'A' : '0';
            if ($format == 'A') {
                $comp = array(
                    'fecha' => $fact->getFechaFactura()->format('Ymd'),
                    'tipoComprobante' => $fact->getAfipComprobante()->getCodigo(),
                    'puntoVenta' => str_pad($fact->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                    'nroComprobante' => str_pad($fact->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                    'despacho' => str_pad("0", 16, "0"),
                    'cuit' => $cuit,
                    'proveedor' => $proveedor,
                    'total' => str_pad(number_format($fact->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'nograv' => str_pad(number_format($fact->getTmc(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'exe' => str_pad(number_format($operacionesExentas, 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percIva' => str_pad(number_format($fact->getPercepcionIva(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percImpNac' => str_pad(number_format('0', 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percIIBB' => str_pad(number_format($fact->getPercepcionDgr(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percMuni' => str_pad(number_format($fact->getPercepcionMunicipal(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'impInterno' => str_pad(number_format($fact->getImpuestoInterno(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'moneda' => 'PES',
                    'tipoCambio' => str_pad("0001000000", 10, "0"),
                    'cantAlicuotas' => count($cantAlicuotas),
                    'codOperacion' => $codOperacion,
                    'credFiscalComp' => str_pad(number_format($fact->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'otrosTributos' => str_pad("0", 15, "0"),
                    'cuitEmisor' => str_pad(" ", 11, " "),
                    'nombreEmisor' => str_pad(" ", 30, " "),
                    'ivaComision' => str_pad("0", 15, "0"),
                    'error' => $error
                );
                array_push($comprobantes, $comp);
            }
            else {
                $comp = $fact->getFechaFactura()->format('Ymd') .
                        $fact->getAfipComprobante()->getCodigo() .
                        str_pad($fact->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                        str_pad($fact->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                        str_pad("0", 16, "0") .
                        '80' .
                        str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                        str_pad($proveedor, 30, " ", STR_PAD_RIGHT) .
                        str_pad(number_format($fact->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($fact->getTmc(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($operacionesExentas, 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($fact->getPercepcionIva(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format('0', 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($fact->getPercepcionDgr(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($fact->getPercepcionMunicipal(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($fact->getImpuestoInterno(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        'PES' .
                        str_pad("0001000000", 10, "0") .
                        count($cantAlicuotas) .
                        $codOperacion .
                        str_pad(number_format($fact->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad("0", 15, "0") .
                        str_pad(" ", 11, " ") .
                        str_pad(" ", 30, " ") .
                        str_pad("0", 15, "0")
                ;
                $comprobantes = ($comprobantes == '') ? $comp : $comprobantes . PHP_EOL . $comp;
            }
        }

        /*
         * NOTAS DE DEBITO Y CREDITO
         */
        $notas = $em->getRepository('ComprasBundle:NotaDebCred')->findNotasByPeriodoUnidadNegocio($desde, $hasta, $unidneg);
        foreach ($notas as $nota) {
            $operacionesExentas = $cantAlicuotas = 0;
            $error = array();
            $save = false;
            if (!$nota->getAfipComprobante()) {
                $pre = ($nota->getSigno() == '+' ) ? 'DEB-' : 'CRE-';
                $afipComp = $em->getRepository('ConfigBundle:AfipComprobante')->getIdByTipo($pre . $nota->getTipoNota());
                $nota->setAfipComprobante($afipComp);
                $save = true;
            }
            // completa punto de venta y nro de comprobante afip
            if (!$nota->getAfipPuntoVenta()) {
                if (strpos($nota->getNroComprobante(), '-') === false) {
                    $error[] = 'COMPROBANTE';
                    $nota->setAfipNroComprobante($nota->getNroComprobante());
                }
                else {
                    $data = split('-', $nota->getNroComprobante());
                    $nota->setAfipPuntoVenta(str_pad($data[0], 5, "0", STR_PAD_LEFT));
                    $nota->setAfipNroComprobante(str_pad($data[1], 20, "0", STR_PAD_LEFT));
                    $save = true;
                }
            }
            // grabar las correcciones de la nota
            if ($save) {
                $em->persist($nota);
                $em->flush();
            }

            if (!UtilsController::validarCuit($nota->getProveedor()->getCuit())) {
                $error[] = 'CUIT';
            }
            $cuit = str_replace('-', '', $nota->getProveedor()->getCuit());
            $proveedor = substr($nota->getProveedor()->getNombre(), 0, 30);
            // contabilizar la cantidad de alicuotas de los items de la factura
            $cantAlicuotas = $em->getRepository('ComprasBundle:NotaDebCred')->getCantidadAlicuotas($nota->getId());

            /*
             * ALICUOTAS NOTADEBCRED
             */
            if (count($cantAlicuotas) == 1) {
                // una sola alicuota, se usa valores generales de la nota
                $codAlicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->find($cantAlicuotas[0]['id']);
                if ($codAlicuota->getValor() == 0) {
                    $operacionesExentas = $nota->getSubtotalNeto();
                }

                if ($format == 'A') {
                    $alic = array(
                        'tipoComprobante' => $nota->getAfipComprobante()->getCodigo(),
                        'puntoVenta' => str_pad($nota->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                        'nroComprobante' => str_pad($nota->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                        'cuit' => $cuit,
                        'netoGravado' => str_pad(number_format($nota->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                        'codAlicuota' => $codAlicuota->getCodigo(),
                        'liquidado' => str_pad(number_format($nota->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                        'error' => $error
                    );
                    array_push($alicuotas, $alic);
                }
                else {
                    $alic = $fact->getAfipComprobante()->getCodigo() .
                            str_pad($nota->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                            str_pad($nota->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                            '80' .
                            str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                            str_pad(number_format($nota->getSubtotalNeto(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                            $codAlicuota->getCodigo() .
                            str_pad(number_format($nota->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT);
                    $alicuotas = ($alicuotas == '') ? $alic : $alicuotas . PHP_EOL . $alic;
                }
            }
            else {
                // más de una alicuota, se calculan los valores
                $totneto = $totiva = 0;
                $alic = array();
                foreach ($cantAlicuotas as $item) {
                    $neto = $liq = 0;
                    $codAlicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->find($item['id']);

                    foreach ($nota->getDetalles() as $det) {
                        if ($det->getAfipAlicuota()->getId() == $codAlicuota->getId()) {
                            $neto += $det->getPrecio() * $det->getCantidad();
                        }
                    }
                    if ($codAlicuota->getValor() == 0) {
                        $operacionesExentas = $neto;
                    }
                    $liq = $neto * ($codAlicuota->getValor() / 100);
                    $alic[] = array(
                        'tipoComprobante' => $nota->getAfipComprobante()->getCodigo(),
                        'puntoVenta' => str_pad($nota->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                        'nroComprobante' => str_pad($nota->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                        'cuit' => $cuit,
                        'netoGravado' => str_pad(number_format($neto, 2, '', ''), 15, "0", STR_PAD_LEFT),
                        'codAlicuota' => $codAlicuota->getCodigo(),
                        'liquidado' => str_pad(number_format($liq, 2, '', ''), 15, "0", STR_PAD_LEFT),
                        'error' => $error
                    );
                    $totneto += $neto;
                    $totiva += $liq;
                }
                if ($totneto <> $nota->getTotal() || $totiva <> $nota->getIva()) {
                    $error[] = 'ALICUOTA';
                }
                foreach ($alic as $i) {
                    if ($format == 'A') {
                        $i['error'] = $error;
                        array_push($alicuotas, $i);
                    }
                    else {
                        $txtalic = $nota->getAfipComprobante()->getCodigo() .
                                str_pad($nota->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                                str_pad($nota->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                                '80' .
                                str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                                str_pad(number_format($nota->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                                $codAlicuota->getCodigo() .
                                str_pad(number_format($nota->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT);
                        $alicuotas = ($alicuotas == '') ? $alic : $alicuotas . PHP_EOL . $txtalic;
                    }
                }
            }

            /*
             * COMPROBANTES NOTAS
             */
            $codOperacion = ($nota->getIva() == 0 ) ? 'A' : '0';
            if ($format == 'A') {
                $comp = array(
                    'fecha' => $nota->getFecha()->format('Ymd'),
                    'tipoComprobante' => $nota->getAfipComprobante()->getCodigo(),
                    'puntoVenta' => str_pad($nota->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT),
                    'nroComprobante' => str_pad($nota->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT),
                    'despacho' => str_pad("0", 16, "0"),
                    'cuit' => $cuit,
                    'proveedor' => $proveedor,
                    'total' => str_pad(number_format($nota->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'nograv' => str_pad(number_format($nota->getTmc(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'exe' => str_pad(number_format($operacionesExentas, 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percIva' => str_pad(number_format($nota->getPercepcionIva(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percImpNac' => str_pad(number_format('0', 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percIIBB' => str_pad(number_format($nota->getPercepcionDgr(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'percMuni' => str_pad(number_format($nota->getPercepcionMunicipal(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'impInterno' => str_pad(number_format($nota->getImpuestoInterno(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'moneda' => 'PES',
                    'tipoCambio' => str_pad("0001000000", 10, "0"),
                    'cantAlicuotas' => count($cantAlicuotas),
                    'codOperacion' => $codOperacion,
                    'credFiscalComp' => str_pad(number_format($nota->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT),
                    'otrosTributos' => str_pad("0", 15, "0"),
                    'cuitEmisor' => str_pad(" ", 11, " "),
                    'nombreEmisor' => str_pad(" ", 30, " "),
                    'ivaComision' => str_pad("0", 15, "0"),
                    'error' => $error
                );
                array_push($comprobantes, $comp);
            }
            else {
                $comp = $nota->getFecha()->format('Ymd') .
                        $nota->getAfipComprobante()->getCodigo() .
                        str_pad($nota->getAfipPuntoVenta(), 5, "0", STR_PAD_LEFT) .
                        str_pad($nota->getAfipNroComprobante(), 20, "0", STR_PAD_LEFT) .
                        str_pad("0", 16, "0") .
                        '80' .
                        str_pad($cuit, 20, "0", STR_PAD_LEFT) .
                        str_pad($proveedor, 30, " ", STR_PAD_RIGHT) .
                        str_pad(number_format($nota->getTotal(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($nota->getTmc(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($operacionesExentas, 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($nota->getPercepcionIva(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format('0', 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($nota->getPercepcionDgr(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($nota->getPercepcionMunicipal(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad(number_format($nota->getImpuestoInterno(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        'PES' .
                        str_pad("0001000000", 10, "0") .
                        count($cantAlicuotas) .
                        $codOperacion .
                        str_pad(number_format($nota->getIva(), 2, '', ''), 15, "0", STR_PAD_LEFT) .
                        str_pad("0", 15, "0") .
                        str_pad(" ", 11, " ") .
                        str_pad(" ", 30, " ") .
                        str_pad("0", 15, "0")
                ;
                $comprobantes = ($comprobantes == '') ? $comp : $comprobantes . PHP_EOL . $comp;
            }
        }

        /*
         * MERGE FACTURAS Y NOTAS
         */

        $resultado = array('comprobantes' => $comprobantes, 'alicuotas' => $alicuotas);

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
        $unidneg = $this->get('session')->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();
        $resultado = $this->getReginfoCompras($em, $unidneg, $periodo, 'T');

        if ($file == 'CBTE') {
            $filename = 'REGINFO_CV_COMPRAS_CBTE';
            // The dinamically created content of the file
            $fileContent = $resultado['comprobantes'];
        }
        else {
            $filename = 'REGINFO_CV_COMPRAS_ALICUOTAS';
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