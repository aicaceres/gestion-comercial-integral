<?php

namespace VentasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\Venta;
use VentasBundle\Form\VentaType;

/**
 * @Route("/venta")
 */
class VentaController extends Controller
{

    /**
     * @Route("/", name="ventas_venta")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_venta');
        $em = $this->getDoctrine()->getManager();        
        $puntoventaId = $request->get('puntoventaId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $puntos = $this->getUser()->getPuntosVenta($unidneg);        
        $entities = $em->getRepository('VentasBundle:Venta')->findByCriteria($unidneg,$puntoventaId, $desde, $hasta);
        return $this->render('VentasBundle:Venta:index.html.twig', array(
                    'entities' => $entities,
                    'puntos' => $puntos,
                    'puntoventaId' => $puntoventaId,                    
                    'desde' => $desde,
                    'hasta' => $hasta
        ));
    }

    /**
     * @Route("/{id}/show", name="ventas_venta_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_venta');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Venta')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Venta entity.');
        }
        return $this->render('VentasBundle:Venta:show.html.twig', array(
                    'entity' => $entity));
    }

    /**
     * @Route("/newVenta", name="ventas_venta_new")
     * @Method("GET")
     * @Template()
     */
    public function newVentaAction(Request $request)
    {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_venta');
        $puntosVenta = $this->getUser()->getPuntosVenta($session->get('unidneg_id'));
        if (!$puntosVenta) {
            $this->addFlash('error', 'No posee ningún punto de venta asigando.');
            $session->set('puntoVentaActual',  array('id' => 0, 'nombre' => ''));
            return $this->redirect($request->headers->get('referer'));
        }        

        $entity = new Venta();
        $entity->setFechaVenta( new \DateTime() );        
        $em = $this->getDoctrine()->getManager();
        // Set cliente segun parametrizacion
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->find(1);
        if($param){
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            $entity->setCliente($cliente);
            // set datos asociados al cliente con su definicion por defecto
            $entity->setFormaPago( $cliente->getFormaPago() );
            $entity->setPrecioLista( $cliente->getPrecioLista() );
            $entity->setTransporte( $cliente->getTransporte() );
        }  

        if (null === $session->get('puntoVentaActual')) {
            $session->set('puntoVentaActual', array('id' => 0, 'nombre' => ''));            
        }
        if ($session->get('puntoVentaActual')['id'] != 0) {
            $ptoActual = $em->getRepository('ConfigBundle:PuntoVenta')->find( $session->get('puntoVentaActual')['id']);
            $entity->setNroOperacion( $ptoActual->getUltimoNroOperacionVenta() + 1 );
        }                

        $form = $this->createCreateForm($entity,'new');     
        return $this->render('VentasBundle:Venta:new.html.twig', array(
            'puntosVenta' => $puntosVenta,
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/", name="ventas_venta_create")
     * @Method("POST")
     * @Template("VentasBundle:Venta:new.html.twig")
     */
    public function createAction(Request $request) {
        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_venta');
        $puntosVenta = $this->getUser()->getPuntosVenta($session->get('unidneg_id'));
       
        $entity = new Venta();
        $form = $this->createCreateForm($entity,'create');
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // set fecha de operacion
                $entity->setFechaVenta( new \DateTime() );  
                // set punto de venta desde sessionados
                $puntoVenta = $em->getRepository('ConfigBundle:PuntoVenta')->find( $session->get('puntoVentaActual')['id'] );
                $entity->setPuntoVenta( $puntoVenta );           
                $nroOperacion = $puntoVenta->getUltimoNroOperacionVenta() + 1;     
                $entity->setNroOperacion( $nroOperacion );
                // update ultimoNroOperacion en puntoventa
                $puntoVenta->setUltimoNroOperacionVenta($nroOperacion);

                $em->persist($entity);            
                $em->persist($puntoVenta);            
                $em->flush();                

                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('ventas_venta_new'));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        // Set cliente segun parametrizacion
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->find(1);
        if($param){
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            $entity->setCliente($cliente);            
        } 
        return $this->render('VentasBundle:Venta:new.html.twig', array(
            'puntosVenta' => $puntosVenta,
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Venta entity.
     * @param Venta $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Venta $entity,$type) {
        $form = $this->createForm(new VentaType(), $entity, array(
            'action' => $this->generateUrl('ventas_venta_create'),
            'method' => 'POST',
            'attr' => array('type'=>$type) ,
        ));
        return $form;
    }


    /**
     * @Route("/renderPuntosVenta", name="ventas_render_puntosventa")
     * @Method("GET")
     */
    public function renderPuntosVentaAction(Request $request)
    {
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta');
        $puntos = $this->getUser()->getPuntosVenta($unidneg_id);
        $partial = $this->renderView(
            'VentasBundle:Venta:_partial-set-puntoventa.html.twig',
            array('puntos' => $puntos)
        );
        return new Response($partial);
    }
    /**
     * @Route("/setPuntosVenta", name="ventas_set_puntosventa")
     * @Method("GET")
     */
    public function setPuntosVentaAction(Request $request)
    {
        $session = $this->get('session');
        $id = $request->get('puntoVenta');
        $em = $this->getDoctrine()->getManager();
        $punto = $em->getRepository('ConfigBundle:PuntoVenta')->find($id);
        $data = array('id' => 0, 'nombre' => '');
        $nro = null;
        if ($punto) {
            $data = array('id' => $id, 'nombre' => $punto->getNombre());
            $nro = $punto->getUltimoNroOperacionVenta()+1;
        }
        $session->set('puntoVentaActual', $data);
        return new Response($nro);
    }

}
