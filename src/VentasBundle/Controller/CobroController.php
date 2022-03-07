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

use VentasBundle\Entity\Cobro;
use VentasBundle\Form\CobroType;

use VentasBundle\Afip\src\Afip;
// $afip = new Afip(array('CUIT'=> '30714151971'));

/**
 * @Route("/cobroVentas")
 */
class CobroController extends Controller
{

    /**
     * @Route("/", name="ventas_cobro")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        $user = $this->getUser();
        UtilsController::haveAccess($user, $unidneg, 'ventas_factura');
        $em = $this->getDoctrine()->getManager();
        $afip = new Afip(array('CUIT'=> $this->getParameter('cuit_afip')));
 $tipos = $afip->ElectronicBilling->getDocumentTypes();
echo '<pre>';
var_dump($afipTipos);
echo '</pre>';
die;

        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        if( $user->getAccess($unidneg, 'ventas_factura_own') && !$user->isAdmin($unidneg)){
            $id = $user->getId();
            $owns = true;
        }else{
            $id = $request->get('userId');
            $owns = false;
        }
        $entities = $em->getRepository('VentasBundle:Cobro')->findByCriteria($unidneg,$desde,$hasta,$id);

        $users = $em->getRepository('VentasBundle:Cobro')->getUsers();
        return $this->render('VentasBundle:Cobro:index.html.twig', array(
                    'entities' => $entities,
                    'id' => $id,
                    'owns' => $owns,
                    'users' => $users,
                    'desde' => $desde,
                    'hasta' => $hasta
        ));
    }

    /**
     * @Route("/newCobro", name="ventas_cobro_new")
     * @Method("GET")
     * @Template()
     */
    public function newCobroAction(Request $request)
    {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');
        $em = $this->getDoctrine()->getManager();
        // CAJA=1
        $caja = $em->getRepository('ConfigBundle:Caja')->find(1);
        if( !$caja->getAbierta()){
            $this->addFlash('error', 'La caja está cerrada. Debe realizar la apertura para iniciar cobros');
            return $this->redirect( $request->headers->get('referer') );
        }

        $id_venta = $request->get('id');
        $entity = new Cobro();
        $entity->setFechaCobro( new \DateTime() );
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if($param){
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            // ultimo nro de operacion de cobro
            $entity->setNroOperacion( $param->getUltimoNroOperacionCobro() + 1 );
        }
        if( $id_venta ){
            // cobro de la ventas
            $venta = $em->getRepository('VentasBundle:Venta')->find($id_venta);
            $entity->setVenta($venta);
            $entity->setCliente($venta->getCliente());
            $entity->setMoneda( $venta->getMoneda() );
            $entity->setFormaPago( $venta->getFormaPago() );
        }else{
            // otro cobro
            $entity->setCliente($cliente);
            // moneda
            $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneBy(array('byDefault'=>true ));
            $entity->setMoneda($moneda);
        }


        $form = $this->createCreateForm($entity,'new');
        return $this->render('VentasBundle:Cobro:new.html.twig', array(
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
                //$this->addFlash('success', 'Se ha registrado la venta:  <span class="notif_operacion"> #'.$entity->getNroOperacion().'</span>');
                // requiere login al volver a ingresar a venta
                $this->get('session')->set('checkrequired','1');
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
        // no requiere login si vuelve con error
        $this->get('session')->set('checkrequired','0');
        return $this->render('VentasBundle:Venta:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Venta entity.
     * @param Cobro $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Cobro $entity,$type) {
        $afip = new Afip(array('CUIT'=> $this->getParameter('cuit_afip')));
        $afipDocType = $afip->ElectronicBilling->getDocumentTypes();
        $docType = array_reduce( $afipDocType , function($array,$item){
            $array[ $item->Id ] = $item->Desc;
            return $array;
        });
        $form = $this->createForm(new CobroType(), $entity, array(
            'action' => $this->generateUrl('ventas_venta_create'),
            'method' => 'POST' ,
            'attr' => array('type'=>$type, 'docType'=> json_encode($docType) ) ,
        ));
        return $form;
    }

    /**
    * @Route("/ventasPorCobrar", name="ventas_por_cobrar")
    */
    public function ventasPorCobrarAction() {
        $em = $this->getDoctrine()->getManager();
        $ventas = $em->getRepository('VentasBundle:Venta')->findBy(array('estado' => 'PENDIENTE'),array('nroOperacion'=>'ASC'));

        return $this->render('VentasBundle:Cobro:_partial-ventas-por-cobrar.html.twig', array(
            'ventas' => $ventas
        ));
    }


}
