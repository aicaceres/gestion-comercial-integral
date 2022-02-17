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
class CajaAperturaController extends Controller
{

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
        if( $cajaId ){
            $caja = $em->getRepository('ConfigBundle:Caja')->find($cajaId); 
        }else{
            $caja = $cajas[0];
        }
    
        $entities = $em->getRepository('VentasBundle:CajaApertura')->findByCriteria($unidneg,$desde, $hasta, $cajaId);                           
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
    public function newAperturaAction(Request $request)
    {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_caja_apertura');

        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();  
        $caja = $em->getRepository('ConfigBundle:Caja')->find($id);  
        if($caja->getAbierta()){
            $this->addFlash('error', 'Esta caja ya se encuentra abierta.<br> Debe realizar el cierre primero!');
            $partial = $this->renderView('AppBundle::notificacion.html.twig');            
            return new Response($partial);
        }
        $entity = new CajaApertura();
        $entity->setCaja( $caja );                        
        $entity->setFechaApertura( new \DateTime() );                        
        $form = $this->createAperturaForm($entity);   
        return $this->render('VentasBundle:CajaApertura:new.html.twig', array(            
            'entity' => $entity,
            'form' => $form->createView(),
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
        // definir ruta
        $ruta = $this->redirect($this->generateUrl('ventas_apertura', array( 'cajaId' => $entity->getCaja()->getId()) ));        
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $entity->getCaja()->setAbierta(true);    
                $entity->setFechaApertura( new \DateTime() );
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();  
                $session->set('caja_abierta', true);
                if( $request->get('ir-a-cobro') ){
                    $ruta = $this->redirect($this->generateUrl('ventas_venta_new'));
                }              
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
    public function newCierreAction(Request $request)
    {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_caja_cierre');

        $caja = $request->get('id');
        $em = $this->getDoctrine()->getManager();          
        $apertura = $em->getRepository('VentasBundle:CajaApertura')->findAperturaSinCerrar($caja); 
        if(!$apertura){
            $this->addFlash('error', 'No se encuentra una apertura de caja para realizar el cierre.');
            $partial = $this->renderView('AppBundle::notificacion.html.twig');            
            return new Response($partial);
        }                
        $apertura->setFechaCierre( new \DateTime() );                        
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
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $entity->getCaja()->setAbierta(false);    
                $entity->setFechaCierre( new \DateTime() );
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();
                $session->set('caja_abierta', false);
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }

        }       
        return $this->redirect($this->generateUrl('ventas_apertura', array( 'cajaId' => $entity->getCaja()->getId()) ));
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

}
