<?php

namespace VentasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ConfigBundle\Controller\UtilsController;

/**
 * @Route("/estadistica")
 */
class EstadisticaController extends Controller {

    /**
     * @Route("/ventas", name="ventas_estadistica_ranking")
     * @Method("GET")
     * @Template()
     */
    public function rankingAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_venta');

        $em = $this->getDoctrine()->getManager();
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $desde = UtilsController::toAnsiDate($periodo['ini']);
        $hasta = UtilsController::toAnsiDate($periodo['fin']);
        $orden = $request->get('orden') ?: 'importe';
        $prodId = $request->get('prodId') ?: null;
        $productos = $em->getRepository('AppBundle:Producto')->findBy(array('activo' => 1), array('nombre' => 'ASC'));

        $ventas = $em->getRepository('VentasBundle:FacturaElectronica')
          ->findEstadisticaVentasByUnidadNegocio($unidneg, $desde, $hasta, $prodId, $orden);

        return $this->render('VentasBundle:Estadistica:ranking.html.twig', array(
                'ventas' => $ventas,
                'desde' => $periodo['ini'],
                'hasta' => $periodo['fin'],
                'productos' => $productos,
                'prodId' => $prodId,
                'orden' => $orden
        ));
    }

    /**
     * @Route("/printRankingVentas.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="ventas_estadistica_ranking_print")
     * @Method("POST")
     */
    public function printRankingAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_venta');

        $em = $this->getDoctrine()->getManager();
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $desde = UtilsController::toAnsiDate($periodo['ini']);
        $hasta = UtilsController::toAnsiDate($periodo['fin']);
        $orden = $request->get('orden') ?: 'importe';
        $prodId = $request->get('prodId') ?: null;
        $producto = $prodId ? $em->getRepository('AppBundle:Producto')->find($prodId) : '';

        $ventas = $em->getRepository('VentasBundle:FacturaElectronica')
          ->findEstadisticaVentasByUnidadNegocio($unidneg, $desde, $hasta, $prodId, $orden);

        $textoFiltro = array(
          'producto' => $producto ? $producto->getNombre() : 'Todos',
          'desde' => $desde ? $desde : '',
          'hasta' => $hasta ? $hasta : '',
          'orden' => $orden) ;

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render(
            'VentasBundle:Estadistica:ranking.pdf.twig',
            array(
                'ventas' => $ventas,
                'filtro' => $textoFiltro
            ),
            $response
        );

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array(
            'content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_ventas_notasdebcred_' . $hoy->format('dmY_Hi') . '.pdf'
        ));
    }
}