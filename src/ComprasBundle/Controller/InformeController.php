<?php

namespace ComprasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ConfigBundle\Controller\UtilsController;

/**
 * @Route("/informe")
 */
class InformeController extends Controller {

    /**
     * @Route("/", name="compras_informe_economico")
     * @Method("GET")
     * @Template()
     */
    public function economicoAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_informe_economico');
        $em = $this->getDoctrine()->getManager();
        $tipo = ($request->get('tipo')) ? $request->get('tipo') : 'CC';
        $hoy = new \DateTime();
        $inicio = date("d-m-Y", strtotime('first day of january this year'));
        $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
        $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y');
        $periodo = array('desde' => $desde, 'hasta' => $hasta);

        $meses = $this->mesesPeriodo($periodo);
        $reporte = $em->getRepository('ComprasBundle:Factura')->reporteEconomico($tipo, $periodo);
        $resTotal = [];
        $rowTotal = [];
        if ($reporte) {

            $arrResultado = new \ArrayObject(['tipo' => '', 'nombre' => '']);
            foreach ($meses as $mes) {
                $arrResultado[$mes] = 0;
            }
            $rowTotal = $arrResultado->getArrayCopy();

            if ($tipo == 'PROV') {
                // PROVEEDOR / CENTRO DE COSTO
                $resTemp = [];
                $res = $arrResultado->getArrayCopy();
                $tot = $arrResultado->getArrayCopy();
                // valores iniciales
                $cc = $reporte[0]['centroCosto'];
                $prov = $reporte[0]['proveedor'];

                // recorro reporte
                foreach ($reporte as $rep) {

                    if ($prov != $rep['proveedor']) {
                        // totalizador de columnas
                        foreach ($tot as $k => $v) {
                            if (!in_array($k, ['tipo', 'nombre'])) {
                                $aux2 = array_key_exists($k, $rowTotal) ? floatval($rowTotal[$k]) : 0;
                                $rowTotal[$k] = $aux2 + $v;
                            }
                        }

                        // cargar prov totalizado
                        array_push($resTotal, $tot);
                        $tot = $arrResultado->getArrayCopy();
                        //cambiar prov
                        $prov = $rep['proveedor'];

                        // cargar linea detalle
                        array_push($resTemp, $res);
                        $res = $arrResultado->getArrayCopy();
                        array_push($resTotal, $resTemp);

                        $resTemp = [];
                        // cambiar prov
                        $cc = $rep['centroCosto'];
                    }

                    if ($cc != $rep['centroCosto']) {
                        // cargar linea detalle
                        array_push($resTemp, $res);
                        $res = $arrResultado->getArrayCopy();
                        // cambiar cc
                        $cc = $rep['centroCosto'];
                    }

                    $res['tipo'] = 'CC';
                    $res['nombre'] = $rep['centroCosto'];
                    $res[$rep['mesanio']] = floatval($rep['subtotal']);

                    // totalizador de CC
                    $tot['tipo'] = 'PROV';
                    $tot['nombre'] = $rep['proveedor'];
                    $aux = array_key_exists($rep['mesanio'], $tot) ? floatval($tot[$rep['mesanio']]) : 0;
                    $tot[$rep['mesanio']] = $aux + $res[$rep['mesanio']];
                }
            }
            else {
                // CENTRO DE COSTO / PROVEEDOR

                $resTemp = [];
                $res = $arrResultado->getArrayCopy();
                $tot = $arrResultado->getArrayCopy();
                // valores iniciales
                $cc = $reporte[0]['centroCosto'];
                $prov = $reporte[0]['proveedor'];

                // recorro reporte
                foreach ($reporte as $rep) {

                    if ($cc != $rep['centroCosto']) {
                        // totalizador de columnas
                        foreach ($tot as $k => $v) {
                            if (!in_array($k, ['tipo', 'nombre'])) {
                                $aux2 = array_key_exists($k, $rowTotal) ? floatval($rowTotal[$k]) : 0;
                                $rowTotal[$k] = $aux2 + $v;
                            }
                        }

                        // cargar cc totalizado
                        array_push($resTotal, $tot);
                        $tot = $arrResultado->getArrayCopy();
                        //cambiar cc
                        $cc = $rep['centroCosto'];

                        // cargar linea detalle
                        array_push($resTemp, $res);
                        $res = $arrResultado->getArrayCopy();
                        array_push($resTotal, $resTemp);

                        $resTemp = [];
                        // cambiar prov
                        $prov = $rep['proveedor'];
                    }

                    if ($prov != $rep['proveedor']) {
                        // cargar linea detalle
                        array_push($resTemp, $res);
                        $res = $arrResultado->getArrayCopy();
                        // cambiar prov
                        $prov = $rep['proveedor'];
                    }

                    $res['tipo'] = 'PROV';
                    $res['nombre'] = $rep['proveedor'];
                    $res[$rep['mesanio']] = floatval($rep['subtotal']);

                    // totalizador de CC
                    $tot['tipo'] = 'CC';
                    $tot['nombre'] = $rep['centroCosto'];
                    $aux = array_key_exists($rep['mesanio'], $tot) ? floatval($tot[$rep['mesanio']]) : 0;
                    $tot[$rep['mesanio']] = $aux + $res[$rep['mesanio']];
                }
            }
        }

        return $this->render('ComprasBundle:Informe:economico.html.twig', array(
                    'tipo' => $tipo,
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'meses' => $meses,
                    'reporte' => $resTotal,
                    'totales' => $rowTotal
        ));
    }

    /**
     * @Route("/", name="compras_informe_financiero")
     * @Method("GET")
     * @Template()
     */
    public function financieroAction(Request $request) {

    }

    private function mesesPeriodo($periodo) {
        $meses = [];
        $f1 = new \DateTime($periodo['desde']);
        $f2 = new \DateTime($periodo['hasta']);
        $Months = $f2->diff($f1);
        $monthsCount = (($Months->y) * 12) + ($Months->m);

        $impf = $f1;
        $meses[] = $impf->format('m-Y');
        for ($i = 1; $i <= $monthsCount; $i++) {
            // despliega los meses
            $impf->add(new \DateInterval('P1M'));
            $meses[] = $impf->format('m-Y');
        }

        return $meses;
    }





}