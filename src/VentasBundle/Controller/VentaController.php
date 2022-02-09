<?php

namespace VentasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;
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
        $user = $this->getUser();
        UtilsController::haveAccess($user, $unidneg, 'ventas_venta');
        $em = $this->getDoctrine()->getManager();                
        
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        
        if( $user->getAccess($unidneg, 'ventas_venta_own') && !$user->isAdmin($unidneg)){
            $id = $user->getId();
            $owns = true;
        }else{
            $id = $request->get('userId');
            $owns = false;
        }        
        $entities = $em->getRepository('VentasBundle:Venta')->findByCriteria($unidneg, $desde, $hasta, $id);
        $users = $em->getRepository('VentasBundle:Venta')->getUsers();                
        return $this->render('VentasBundle:Venta:index.html.twig', array(
                    'entities' => $entities,
                    'id' => $id,
                    'owns' => $owns,
                    'users' => $users,                    
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
//var_dump(gethostname());

        $session = $this->get('session');
        UtilsController::haveAccess($this->getUser(), $session->get('unidneg_id'), 'ventas_venta');
        $entity = new Venta();
        $entity->setFechaVenta( new \DateTime() );                
        $em = $this->getDoctrine()->getManager();
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->find(1);
        if($param){
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            $entity->setCliente($cliente);
            $deposito = $em->getRepository('AppBundle:Deposito')->find($param->getVentasDepositoBydefault());
            $entity->setDeposito($deposito);
            // set datos asociados al cliente con su definicion por defecto           
            $entity->setFormaPago( $cliente->getFormaPago() );
            $entity->setPrecioLista( $cliente->getPrecioLista() );
            $entity->setTransporte( $cliente->getTransporte() );
            // ultimo nro de operacion de venta
            $entity->setNroOperacion( $param->getUltimoNroOperacionVenta() + 1 );
        }  
        $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneByByDefault(1);
        $entity->setMoneda( $moneda );
        $form = $this->createCreateForm($entity,'new');     
        return $this->render('VentasBundle:Venta:new.html.twig', array(            
            'entity' => $entity,
            'form' => $form->createView(),
        ));


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
            $deposito = $em->getRepository('AppBundle:Deposito')->find($param->getVentasDepositoBydefault());
            $entity->setDeposito($deposito);
            // set datos asociados al cliente con su definicion por defecto           
            $entity->setFormaPago( $cliente->getFormaPago() );
            $entity->setPrecioLista( $cliente->getPrecioLista() );
            $entity->setTransporte( $cliente->getTransporte() );
        }  
        $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneByByDefault(1);
        $entity->setMoneda( $moneda );

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
        
        $entity = new Venta();
        $form = $this->createCreateForm($entity,'create');
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // set fecha de operacion
                $entity->setFechaVenta( new \DateTime() );  
                // set unidad negocio desde session
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($session->get('unidneg_id'));
                $entity->setUnidadNegocio($unidneg);    
                // set nro operacion
                $param = $em->getRepository('ConfigBundle:Parametrizacion')->find(1);
                $nroOperacion = $param->getUltimoNroOperacionVenta() + 1;
                $entity->setNroOperacion( $nroOperacion );                    
                // update ultimoNroOperacion en parametrizacion
                $param->setUltimoNroOperacionVenta($nroOperacion);

                $em->persist($entity);            
                $em->persist($param);            
                $em->flush();            

                // Descuento de stock 
                $deposito = $entity->getDeposito();
                foreach ($entity->getDetalles() as $detalle){
                    $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                    if ($stock) {
                        $stock->setCantidad($stock->getCantidad() - $detalle->getCantidad());
                    }else {
                        $stock = new Stock();
                        $stock->setProducto($detalle->getProducto());
                        $stock->setDeposito($deposito);
                        $stock->setCantidad( 0 - $detalle->getCantidad());
                    }
                    $em->persist($stock);

    // Cargar movimiento
                    $movim = new StockMovimiento();
                    $movim->setFecha($entity->getFechaVenta());
                    $movim->setTipo('ventas_venta');
                    $movim->setSigno('-');
                    $movim->setMovimiento($entity->getId());
                    $movim->setProducto($detalle->getProducto());
                    $movim->setCantidad($detalle->getCantidad());
                    $movim->setDeposito($deposito);
                    $em->persist($movim);
                    $em->flush();

                }                                                            

                $em->getConnection()->commit();
                $this->addFlash('success', 'Se ha registrado la venta:  <span class="notif_operacion"> #'.$entity->getNroOperacion().'</span>');
                return $this->redirect($this->generateUrl('ventas_venta'));
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

    /**
    * @Route("/login", name="ventas_login")
    */
    public function loginAction() {                        
        return $this->render('VentasBundle:Venta:login.html.twig');
    }

    /**
    * @Route("checkLogin", name="ventas_login_check")
     */
    public function checkLoginAction(){
        $msg = 'OK';
        $partial = $this->renderView('VentasBundle:Venta:login.html.twig');
        $result = array('msg'=>$msg, 'partial' => $partial );
        return new Response( json_encode($result));
    }


}
