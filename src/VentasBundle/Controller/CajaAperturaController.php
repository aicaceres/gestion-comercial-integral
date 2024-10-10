<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\CajaApertura;
use VentasBundle\Form\CajaAperturaType;
use VentasBundle\Form\CajaCierreType;

/**
 * @Route("/cajaApertura")
 */
class CajaAperturaController extends Controller {

    /**
     * @Route("/", name="ventas_apertura")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        $user = $this->getUser();
        UtilsController::haveAccess($user, $unidneg, 'ventas_caja_apertura');
        $em = $this->getDoctrine()->getManager();

        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $cajaId = $request->get('cajaId');

        $cajas = $em->getRepository('ConfigBundle:Caja')->findAll();
        if ($cajaId) {
            $caja = $em->getRepository('ConfigBundle:Caja')->find($cajaId);
        }
        else {
            $caja = $cajas[0];
        }

        $entities = $em->getRepository('VentasBundle:CajaApertura')->findByCriteria($unidneg, $desde, $hasta, $cajaId);
        return $this->render('VentasBundle:CajaApertura:index.html.twig', array(
                'entities' => $entities,
                'cajaId' => $cajaId,
                'caja' => $caja,
                'cajas' => $cajas,
                'desde' => $desde,
                'hasta' => $hasta
        ));
    }

    /**
     * @Route("/newApertura", name="ventas_apertura_new")
     * @Method("POST")
     * @Template()
     */
    public function newAperturaAction(Request $request) {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_caja_apertura');

        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $caja = $em->getRepository('ConfigBundle:Caja')->find($id);
        $apertura = $em->getRepository('VentasBundle:CajaApertura')->findAperturaSinCerrar($caja->getId());
        if ($caja->getAbierta() || $apertura) {
            $this->addFlash('error', 'Esta caja ya se encuentra abierta.<br> Debe realizar el cierre primero!');
            $partial = $this->renderView('AppBundle::notificacion.html.twig');
            return new Response($partial);
        }
        $entity = new CajaApertura();
        $entity->setCaja($caja);
        $entity->setFechaApertura(new \DateTime());
        $form = $this->createAperturaForm($entity);
        $form->get('referer')->setData($request->headers->get('referer'));
        return $this->render('VentasBundle:CajaApertura:new.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView()
        ));
    }

    /**
     * @Route("/createApertura", name="ventas_apertura_create")
     * @Method("POST")
     * @Template("VentasBundle:CajaApertura:new.html.twig")
     */
    public function createAction(Request $request) {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_caja_apertura');
        $entity = new CajaApertura();
        $form = $this->createAperturaForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $caja = $entity->getCaja();
        // definir ruta
        $ruta = $this->redirect($this->generateUrl('ventas_apertura', array('cajaId' => $caja->getId())));
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $referer = $request->get('ventasbundle_apertura')['referer'];
                // verificar que no exista caja abierta
                $apertura = $em->getRepository('VentasBundle:CajaApertura')->findAperturaSinCerrar($caja->getId());
                if ($apertura) {
                    $this->addFlash('error', 'Esta caja ya se encuentra abierta!');
                    return $ruta;
                }

                $entity->getCaja()->setAbierta(true);
                $entity->setFechaApertura(new \DateTime());
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();
                $session->set('caja_abierta', true);
                $ruta = $this->redirect($referer);
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $ruta;
    }

    /**
     * Creates a form to create a Venta entity.
     * @param CajaApertura $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createAperturaForm(CajaApertura $entity) {
        $form = $this->createForm(new CajaAperturaType(), $entity, array(
            'action' => $this->generateUrl('ventas_apertura_create'),
            'method' => 'POST'
        ));
        return $form;
    }

    /**
     * @Route("/newCierre", name="ventas_cierre_new")
     * @Method("POST")
     * @Template()
     */
    public function newCierreAction(Request $request) {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_caja_cierre');

        $cajaId = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $apertura = $em->getRepository('VentasBundle:CajaApertura')->findAperturaSinCerrar($cajaId);
        if (!$apertura) {
            $this->addFlash('error', 'No se encuentra una apertura de caja para realizar el cierre.');
            $partial = $this->renderView('AppBundle::notificacion.html.twig');
            return new Response($partial);
        }
        $apertura->setFechaCierre(new \DateTime());
        $form = $this->createCierreForm($apertura);
        return $this->render('VentasBundle:CajaApertura:cierre.html.twig', array(
                'entity' => $apertura,
                'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/createCierre", name="ventas_cierre_create")
     * @Method("POST")
     * @Template("VentasBundle:CajaApertura:cierre.html.twig")
     */
    public function createCierreAction(Request $request) {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_caja_cierre');

        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:CajaApertura')->find($id);
        $form = $this->createCierreForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $entity->getCaja()->setAbierta(false);
                $entity->setFechaCierre(new \DateTime());
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();
                $session->set('caja_abierta', false);
                // url arqueo
                $url_arqueo = $this->generateUrl('ventas_apertura_arqueo', array('id' => $entity->getId()));
                if (COUNT($entity->getMovimientos()) == 0) {
                    $this->addFlash('warning', 'No se registraron movimientos!');
                    $url_arqueo = null;
                }
                $logger->info($url_arqueo);
                return new Response(json_encode(['message' => 'URL', 'url' => $url_arqueo]));
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                return new Response(json_encode(['message' => 'ERROR', 'error' => $ex->getMessage()]));
            }
        }
        return new Response(json_encode(['message' => 'ERROR', 'error' => 'invalid form!']));
    }

    /**
     * Creates a form to create a Venta entity.
     * @param CajaApertura $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCierreForm(CajaApertura $entity) {
        $form = $this->createForm(new CajaCierreType(), $entity, array(
            'action' => $this->generateUrl('ventas_cierre_create'),
            'method' => 'POST'
        ));
        return $form;
    }

    /**
     * @Route("/{id}/arqueoCajaApertura.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="ventas_apertura_arqueo")
     * @Method("GET")
     */
    public function arqueoCajaAperturaAction($id) {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_caja_cierre');
        $em = $this->getDoctrine()->getManager();
        $apertura = $em->getRepository('VentasBundle:CajaApertura')->find($id);
        $movimientos = $em->getRepository('VentasBundle:CajaApertura')->getMovimientosById($id);
        $bancosRetencion = $em->getRepository('ConfigBundle:Banco')->findBancosRetencion();
        $logo = __DIR__ . '/../../../web/assets/images/logo_comprobante_bn.png';

        $resumen = $resumenMov = array();
        foreach ($movimientos as $mov) {
            if (!$mov->getIncluirEnArqueo()) {
                continue;
            }
            $tipoPago = $mov->getTipoPago();
            if ($tipoPago == 'CHEQUE') {
                $banco = $mov->getChequeRecibido()->getBanco() ? $mov->getChequeRecibido()->getBanco()->getNombre() : '///';
                if (strpos($banco, 'RETENCION') !== false) {
                    $tipoPago = 'RETENCION';
                }
            }

            if ($tipoPago == 'TARJETA') {
                $tarjeta = $mov->getDatosTarjeta()->getTarjeta()->getNombre();
                if ($tarjeta == 'TRANSFERENCIA') {
                    $tipoPago = 'TRANSFERENCIA';
                }
            }
            $monto = $mov->getImporte() * $mov->getMoneda()->getCotizacion() * $mov->getSignoCaja();
            $key = array_search($mov->getComprobanteTxt(), array_column($resumen, 'nroComprobante'));

            if ($key) {
                // cargar en el mismo item
                $resumen[$key][$tipoPago] += $monto;
                // actualizar el vuelto si corresponde
                if ($tipoPago !== 'CTACTE') {
                    // si tiene importe el movimiento
                    $resumen[$key]['vuelto'] -= $mov->getImporte();
                }
            }
            else {
                $resumenMov['id'] = $mov->getId();
                $resumenMov['tipoComprobante'] = $mov->getTipoComprobante();
                $resumenMov['nroComprobante'] = $mov->getComprobanteTxt();
                $resumenMov['fecha'] = $mov->getFecha()->format('d/m/Y');
                $resumenMov['cliente'] = $mov->getCliente();
                $resumenMov['moneda'] = $mov->getMoneda()->getSimbolo();
                $resumenMov['cotiz'] = $mov->getMoneda()->getCotizacion();
                $resumenMov['montoComp'] = $mov->getMontoComprobante();
                // armar los tipos para el array
                $resumenMov['EFECTIVO'] = 0;
                $resumenMov['CHEQUE'] = 0;
                $resumenMov['TARJETA'] = 0;
                $resumenMov['TRANSFERENCIA'] = 0;
                $resumenMov['CTACTE'] = 0;
                $resumenMov['RETENCION'] = 0;
                $resumenMov['vuelto'] = 0;
                // asignar el monto en el que corresponda
                $resumenMov[$tipoPago] = $monto;
                if ($tipoPago !== 'CTACTE') {
                    // si tiene importe el movimiento
                    $resumenMov['vuelto'] = $mov->getMontoComprobante() - $mov->getImporte();
                }
                array_push($resumen, $resumenMov);
            }
            $monto = 0;
        }

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('VentasBundle:CajaApertura:informe-arqueo.pdf.twig',
            array('apertura' => $apertura, 'arqueo' => $resumen, 'movimientos' => $movimientos,
                'bancosRetencion' => array_column($bancosRetencion, 'nombre'), 'logo' => $logo), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);

        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=arqueo_' . $apertura->getId() . '.pdf'));
    }

}