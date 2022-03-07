<?php

namespace VentasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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

        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta_new');
        $entity = new Venta();
        $entity->setFechaVenta( new \DateTime() );                
        $em = $this->getDoctrine()->getManager();
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
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
    }

    /**
     * @Route("/", name="ventas_venta_create")
     * @Method("POST")
     * @Template("VentasBundle:Venta:new.html.twig")
     */
    public function createAction(Request $request) {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta');
        
        $entity = new Venta();
        $form = $this->createCreateForm($entity,'create');
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // set fecha de operacion
                $entity->setFechaVenta( new \DateTime() );  
                // set unidad negocio desde session
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($unidneg_id);
                $entity->setUnidadNegocio($unidneg);    
                // set nro operacion                
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
                //$this->addFlash('success', 'Se ha registrado la venta:  <span class="notif_operacion"> #'.$entity->getNroOperacion().'</span>');
                // requiere login al volver a ingresar a venta
                $session->set('checkrequired','1');
                return $this->redirect($this->generateUrl('ventas_venta_new'));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        // Set cliente segun parametrizacion
        if($param){
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            $entity->setCliente($cliente);            
        } 
        // no requiere login si vuelve con error
        $session->set('checkrequired','0');
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
* LOGIN PARA VENTAS
 */

    /**
    * @Route("/login", name="ventas_login")
    */
    public function loginAction() {                        
        return $this->render('VentasBundle:Venta:login.html.twig');
    }

    /**
    * @Route("checkLogin", name="ventas_login_check")
     */
    public function checkLoginAction(Request $request){               
        $username = strtoupper($request->get('username')) ;
        $password = trim($request->get('password'))  ;
        $em = $this->get('doctrine')->getEntityManager();
        $reload = false;
        $user = $em->getRepository('ConfigBundle:Usuario')->findOneBy(array("username"=>$username));
        if ($user) {
            // Get the encoder for the users password
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);            
            if ($encoder->isPasswordValid($user->getPassword(), $password, null)) {
                // verificar si es el mismo usuario
                if( $this->getUser()->getUsername() !== $username ){
                    // loguear al nuevo usuario 
                    $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
                    $this->get('security.context')->setToken($token);
                    $this->get('session')->set('_security_secured_area', serialize($token));
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                    // RECARGAR LA PÁGINA PARA CAMBIAR USUARIO
                    $reload = true; 
                    // No requiere login al recargar    
                    $this->get('session')->set('checkrequired','0');               
                }               
                $result = array( 'msg' => 'OK', 'url' => $this->generateUrl('ventas_venta_new'), 'reload' => $reload);                
            }else{
                $result = array( 'msg' => 'Ingrese una contraseña válida!', 'field'=>'password' );
            }
        }else{
            $result = array( 'msg' => 'Ingrese un usuario válido!', 'field'=>'username' );
        }    
        return new Response( json_encode($result));
    }


}
