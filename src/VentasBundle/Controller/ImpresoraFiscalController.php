<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use VentasBundle\Entity\ImpresoraFiscal;

/**
 * @Route("/impresoraFiscal")
 */
class ImpresoraFiscalController extends Controller {
    const cmCierreX = 3;
    const cmCierreZ = 4;
    const cmObtenerDatosDeInicializacion = 18;
    const cmObtenerPrimerBloqueReporteElectronico = 33;

    /**
     * @Route("/registrarMovimiento", name="ifu_registrar_movimiento")
     * @Method("POST")
     */
    public function registrarMovimientoAction(Request $request) {
        $cajaId = $request->get('caja');
        $comando = $request->get('comando');
        $error = $request->get('error');

        $result = $error != 'null' ? $error : $request->get('result');

        $salida = array('res' => 'OK', 'msg' => '');
        $unidneg_id = $this->get('session')->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($unidneg_id);
        $caja = $em->getRepository('ConfigBundle:Caja')->find($cajaId);

        try {
            $if = new ImpresoraFiscal();
            $if->setUnidadNegocio($unidneg);
            $if->setCaja($caja);
            $if->setComando($comando);
            $if->setData(json_encode($result));
            if ($comando == 'cmObtenerPrimerBloqueReporteElectronico') {
                $rango = $request->get('fechas');
                $if->setFechaDesde(\DateTime::createFromFormat('dmy', $rango['desde']));
                $if->setFechaHasta(\DateTime::createFromFormat('dmy', $rango['hasta']));
            }
            if ($error != 'null')
                $if->setError(true);

            $em->persist($if);
            $em->flush();
        }
        catch (\Exception $ex) {
            $salida['res'] = 'ERROR';
            $salida['msg'] = $ex->getMessage();
        }
        return new JsonResponse($salida);
    }

    /**
     * REPORTE SEMANAL DE IMPRESORA FISCAL
     */

    /**
     * @Route("/getDatosReporteSemanal", name="get_datos_reporte_semanal")
     * @Method("GET")
     * @Template()
     */
    public function getDatosReporteSemanalAction(Request $request) {
        $cajaId = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $hasta = date("dmy");

        $repAnterior = $em->getRepository('VentasBundle:ImpresoraFiscal')->findUltimotReporte($cajaId);
        $desde = $repAnterior['fechaHasta'] ? $repAnterior['fechaHasta']->format("dmy") : date("dmy", strtotime($hasta . "- 7 days"));
        $file = 'M:\\Cierres\\semanal' . $desde . 'al' . $hasta . '.zip';
        $rango = array('desde' => $desde, 'hasta' => $hasta, 'file' => $file);
        return new JsonResponse($rango);
    }

}