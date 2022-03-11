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
     * @Route("/facturarVenta/{id}", name="ventas_cobro_facturar")
     * @Method("GET")
     * @Template()
     */
    public function facturarVentaAction(Request $request, $id)
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

        $entity = new Cobro();
        $entity->setFechaCobro( new \DateTime() );
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if($param){
            // ultimo nro de operacion de cobro
            $entity->setNroOperacion( $param->getUltimoNroOperacionCobro() + 1 );
        }
        $venta = $em->getRepository('VentasBundle:Venta')->find($id);
        if (!$venta) {
            throw $this->createNotFoundException('No se encuentra la venta.');
        }
        $entity->setVenta($venta);
        $entity->setCliente($venta->getCliente());
        $entity->setMoneda( $venta->getMoneda() );
        $entity->setFormaPago( $venta->getFormaPago() );

        $form = $this->createCreateForm($entity,'new');
        return $this->render('VentasBundle:Cobro:facturar-venta.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/newCobro", name="ventas_cobro_new")
     * @Method("GET")
     * @Template()
     */
    public function newCobroAction(Request $request)
    {
        return false;
    }

/****

VIEJO DE VENTA - CAMBIAR POR PROCESOS PARA COBROS

 */


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


/*
$afip = new Afip(array('CUIT'=> $this->getParameter('cuit_afip')));
$data = array(
	'CantReg' 	=> 1,  // Cantidad de comprobantes a registrar
	'PtoVta' 	=> 1,  // Punto de venta
	'CbteTipo' 	=> 6,  // Tipo de comprobante (ver tipos disponibles)
	'Concepto' 	=> 1,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
	'DocTipo' 	=> 99, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
	'DocNro' 	=> 0,  // Número de documento del comprador (0 consumidor final)
	'CbteDesde' 	=> 0,  // Número de comprobante o numero del primer comprobante en caso de ser mas de uno
	'CbteHasta' 	=> 0,  // Número de comprobante o numero del último comprobante en caso de ser mas de uno
	'CbteFch' 	=> intval(date('Ymd')), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
	'ImpTotal' 	=> 121, // Importe total del comprobante
	'ImpTotConc' 	=> 0,   // Importe neto no gravado
	'ImpNeto' 	=> 100, // Importe neto gravado
	'ImpOpEx' 	=> 0,   // Importe exento de IVA
	'ImpIVA' 	=> 21,  //Importe total de IVA
	'ImpTrib' 	=> 0,   //Importe total de tributos
	'MonId' 	=> 'PES', //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos)
	'MonCotiz' 	=> 1,     // Cotización de la moneda usada (1 para pesos argentinos)
	'Iva' 		=> array( // (Opcional) Alícuotas asociadas al comprobante
		array(
			'Id' 		=> 5, // Id del tipo de IVA (5 para 21%)(ver tipos disponibles)
			'BaseImp' 	=> 100, // Base imponible
			'Importe' 	=> 21 // Importe
		)
	),
);
$res = $afip->ElectronicBilling->CreateNextVoucher($data);


 $tipos = $afip->ElectronicBilling->GetVoucherTypes();
echo '<pre>';
var_dump($tipos);
echo '</pre>';


die;
*/

}
