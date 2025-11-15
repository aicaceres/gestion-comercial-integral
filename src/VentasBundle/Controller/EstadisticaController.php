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
     * @Route("/", name="ventas_estadistica_ranking")
     * @Method("GET")
     * @Template()
     */
    public function rankingAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_estadistica');

        $em = $this->getDoctrine()->getManager();
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));

        $entities = $em->getRepository('VentasBundle:Venta')->findByCriteria($unidneg, $periodo['ini'], $periodo['fin']);

        return $this->render('VentasBundle:Estadistica:ranking.html.twig', array(
                'entities' => null,
                'desde' => $periodo['ini'],
                'hasta' => $periodo['fin']
        ));
    }
}