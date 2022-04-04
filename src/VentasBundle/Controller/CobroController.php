<?php

namespace VentasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use ConfigBundle\Controller\UtilsController;

use VentasBundle\Entity\Cobro;
use VentasBundle\Form\CobroType;
use VentasBundle\Entity\CobroDetalle;
use VentasBundle\Entity\CobroDetalleTarjeta;
use VentasBundle\Entity\FacturaElectronica;

use VentasBundle\Afip\src\Afip;
use Endroid\QrCode\QrCode;
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
                    'hasta' => $hasta,
                    'printpdf' => $request->get('printpdf')
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
        // Verificar si la caja está abierta CAJA=1
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
        //$facturaElectronica = new FacturaElectronica();
        //$facturaElectronica->setPuntoVenta( $param->getPuntoVentaFactura() );
        // definir tipo de factura segun cliente
        /*$categoriaIva = $entity->getCliente()->getCategoriaIva()->getNombre();
        $tipo = 'FAC-B';
        if( $categoriaIva =='I' || $categoriaIva == 'M' ){
            $tipo = 'FAC-A';
        }
        /*elseif( $categoriaIva == 'C' && $entity->getFormaPago()->getContado()){
            $tipo = 'TIQUE';
        }
        $tipoFactura = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByValor($tipo);
        $facturaElectronica->setTipoComprobante($tipoFactura);*/
        //$entity->setFacturaElectronica($facturaElectronica);

        $form = $this->createCreateForm($entity,'new');
        return $this->render('VentasBundle:Cobro:facturar-venta.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/", name="ventas_cobro_create")
     * @Method("POST")
     * @Template("VentasBundle:Cobro:new.html.twig")
     */
    public function createAction(Request $request) {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');
        $em = $this->getDoctrine()->getManager();
        // Verificar si la caja está abierta CAJA=1
        $caja = $em->getRepository('ConfigBundle:Caja')->find(1);
        if( !$caja->getAbierta()){
            $this->addFlash('error', 'La caja está cerrada. Debe realizar la apertura para iniciar cobros');
            return $this->redirect( $request->headers->get('referer') );
        }

        $entity = new Cobro();
        $form = $this->createCreateForm($entity,'create');
        $form->handleRequest($request);

// SACAR SI DESPUES SE HABILITA MODIFICAR CLIENTE, FORMA DE PAGO Y MONEDA
        $entity->setCliente( $entity->getVenta()->getCliente() );
        $entity->setFormaPago( $entity->getVenta()->getFormaPago() );
        $entity->setMoneda( $entity->getVenta()->getMoneda() );

////////////////////////////////

        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $facturaElectronica = new FacturaElectronica();

                // armar datos para webservice
                $docTipo = 99 ;
                $docNro = 0;
                if( $entity->getCliente()->getCuit() ){
                    $docTipo = 80 ;
                    $docNro = trim($entity->getCliente()->getCuit());
                }elseif ($entity->getTipoDocumentoCliente() ) {
                    $docTipo = $entity->getTipoDocumentoCliente()->getCodigo();
                    $docNro = $entity->getNroDocumentoCliente();
                }

                $catIva = ( $entity->getCliente()->getCategoriaIva() ) ? $entity->getCliente()->getCategoriaIva()->getNombre() : 'C';
                //$entity->setDescuentoRecargo($entity->getFormaPago()->getPorcentajeRecargo()) ;
                $iva = $tributos = array();
                if( $entity->getVenta()->getDetalles() ){
                    $impTotal = $impNeto = $impIVA = $impTrib = $impDtoRec = 0;
                    foreach( $entity->getVenta()->getDetalles() as $item ){
                        $alicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->findOneBy( array   ('valor'=>$item->getProducto()->getIva()));
                        $codigo = intval($alicuota->getCodigo());
                        $dtoRec = $item->getPrecio() * ($entity->getVenta()->getDescuentoRecargo()/100);
                        $baseImp = $item->getPrecio() + $dtoRec;
                        $importe = $baseImp * ( $alicuota->getValor() / 100 );
                        $key = array_search($codigo, array_column($iva, 'Id'));
                        // IVA
                        /*  array(
                            'Id' 		=> 5, // Id del tipo de IVA (ver tipos disponibles)
                            'BaseImp' 	=> 100, // Base imponible
                            'Importe' 	=> 21 // Importe
                        )*/
                        if( $key === false){
                            $iva[] = array( 'Id' => $codigo,
                                            'BaseImp' => round($baseImp, 2) ,
                                            'Importe' => round($importe,2) );
                        }else{
                            $iva[$key] = array( 'Id' => $codigo,
                                            'BaseImp' => round( $iva[$key]['BaseImp'] + $baseImp ,2) ,
                                            'Importe' => round( $iva[$key]['Importe'] + $importe ,2) );
                        }
                        // TOTALES
                        $impDtoRec += $dtoRec;
                        $impNeto += $baseImp;
                        $impIVA += $importe;
                        $impTotal += ($baseImp + $importe );
                        //$item->setDescuento($dtoRec);
                    }
                    // TRIBUTOS
                    /*array(
                        'Id' 		=>  99, // Id del tipo de tributo (ver tipos disponibles)
                        'Desc' 		=> 'Ingresos Brutos', // (Opcional) Descripcion
                        'BaseImp' 	=> 150, // Base imponible para el tributo
                        'Alic' 		=> 5.2, // Alícuota
                        'Importe' 	=> 7.8 // Importe del tributo
                    )*/
                    $impTrib = 0;
                    if( $catIva == 'I' ){
                        $neto = round($impNeto,2);
                        $iibb = round( ($neto * 0.035) ,2);
                        $impTrib = $iibb;
                        $tributos = array(
                            'Id' => 7,
                            'BaseImp' => $neto,
                            'Alic' => 3.5,
                            'Importe' => $iibb );
                    }
                    $impTotal += $impTrib;

                }

                // completar datos de detalles
                $efectivo = true;
                if( count($entity->getDetalles()) == 0 ){
                    if( $entity->getFormaPago()->getTipoPago() == 'CTACTE' ){
                    // insertar un detalle para ctacte
                        $detalle = new CobroDetalle();
                        $detalle->setTipoPago('CTACTE');
                        $detalle->setMoneda($entity->getMoneda());
                        $detalle->setImporte($impTotal);
                        $entity->addDetalle($detalle);
                        $efectivo = false;
                    }
                }else{
                    foreach( $entity->getDetalles() as $detalle ){
                        if(!$detalle->getMoneda()){
                            $detalle->setMoneda($entity->getMoneda());
                        }
                        $tipoPago = $detalle->getTipoPago();
                        if( $tipoPago != 'CHEQUE' ){
                            $detalle->setChequeRecibido(null);
                        }
                        if( $tipoPago != 'TARJETA' ){
                            $detalle->setDatosTarjeta(null);
                        }
                        if( $tipoPago != 'EFECTIVO' ){
                            $efectivo = false;
                        }
                    }
                }
                // definir tipo de factura segun cliente

                $tipoComprobante = 'FAC-B';
                $ptovta =$this->getParameter('ptovta_ws_factura');
                if( $catIva =='I' || $catIva == 'M' ){
                    $tipoComprobante = 'FAC-A';
                }
                if( $efectivo && $catIva =='C' && $entity->getMoneda()->getCodigoAfip()=='PES' ){
                    $ptovta =$this->getParameter('ptovta_ws_ticket');
                    //$tipoComprobante = 'TIQUE';
                }

                $tipoFactura = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByValor($tipoComprobante);
                $facturaElectronica->setTipoComprobante($tipoFactura);
                $facturaElectronica->setPuntoVenta( $ptovta );
                $entity->setFacturaElectronica($facturaElectronica);

                // emitir comprobante electronico si no es tique
                if( $tipoComprobante != 'TIQUE' ){
                    /**  INICIO EMISION FACTURA WEBSERVICE */
                    $afip = new Afip(array('CUIT'=> $this->getParameter('cuit_afip')));

                    $data = array(
                        'CantReg' 	=> 1,  // Cantidad de comprobantes a registrar
                        'PtoVta' 	=> $facturaElectronica->getPuntoVenta(),  // Punto de venta
                        'CbteTipo' 	=> $facturaElectronica->getCodigoComprobante(),  // Tipo de comprobante (ver tipos disponibles)
                        'Concepto' 	=> 1,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
                        'DocTipo' 	=> $docTipo, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
                        'DocNro' 	=> $docNro,  // Número de documento del comprador (0 consumidor final)
                        'CbteFch' 	=> intval( $entity->getFechaCobro()->format('Ymd') ), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
                        'ImpTotal' 	=> round($impTotal,2) , // Importe total del comprobante
                        'ImpTotConc' 	=> 0,   // Importe neto no gravado
                        'ImpNeto' 	=> round($impNeto,2) , // Importe neto gravado
                        'ImpOpEx' 	=> 0,   // Importe exento de IVA
                        'ImpIVA' 	=> round($impIVA,2),  //Importe total de IVA
                        'ImpTrib' 	=> round($impTrib,2),   //Importe total de tributos
                        'MonId' 	=> $entity->getMoneda()->getCodigoAfip(), //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos)
                        'MonCotiz' 	=> $entity->getMoneda()->getCotizacion(),     // Cotización de la moneda usada (1 para pesos argentinos)
                        'Tributos' => $tributos,
                        'Iva' 			=> $iva,
                    );
                    // si no hay tributos
                    if( empty($tributos) ){
                        unset( $data['Tributos'] );
                    }

                    $wsResult = $afip->ElectronicBilling->CreateNextVoucher($data);
                    $facturaElectronica->setCae($wsResult['CAE']);
                    $facturaElectronica->setCaeVto($wsResult['CAEFchVto']);
                    $facturaElectronica->setNroComprobante($wsResult['voucher_number']);
                    /**  FIN EMISION FACTURA WEBSERVICE */
                    $comprobante = $entity->getFacturaElectronica()->getComprobanteTxt();
                }else{
                    /** EMISIÓN A TIQUEADORA */
                    //$afip = new Afip(array('CUIT'=> $this->getParameter('cuit_afip')));

                    $facturaElectronica->setCae('CAE');
                    $facturaElectronica->setCaeVto('2022-01-01');
                    $facturaElectronica->setNroComprobante('voucher_number');
                    $comprobante = 'TICKET ';
                }
                //$entity->setIva($impIVA);
                //$entity->setPercIibb($impTrib);
                //$entity->setTotal($impTotal);
                //$entity->setSaldo($impTotal);
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
                $entity->setUnidadNegocio($unidneg);

                // Guardar datos en factura electronica
                $facturaElectronica->setCobro($entity);
                $facturaElectronica->setTotal($impTotal);
                $em->persist($facturaElectronica);

                // set numeracion
                $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
                if($param){
                    // cargar datos parametrizados por defecto
                    $entity->setNroOperacion( $param->getUltimoNroOperacionCobro() + 1 );
                    $param->setUltimoNroOperacionCobro( $entity->getNroOperacion() );
                    $em->persist($param);
                }

                $entity->getVenta()->setEstado('FACTURADO');
                $entity->setEstado('FINALIZADO');
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();

                $this->addFlash('success', 'Emitido el comprobante '. $comprobante );
                if( $tipoComprobante == 'TIQUE' ){
                    // EMITIDO TICKET NO FACTURA
                    return $this->redirect($this->generateUrl('ventas_cobro'));
                }
                return $this->redirect($this->generateUrl('ventas_cobro', array('printpdf' => $entity->getId())));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        $this->addFlash('error', 'invalid');
        return $this->render('VentasBundle:Cobro:facturar-venta.html.twig', array(
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
        $form = $this->createForm(new CobroType(), $entity, array(
            'action' => $this->generateUrl('ventas_cobro_create'),
            'method' => 'POST' ,
            'attr' => array('type'=>$type ) ,
        ));
        return $form;
    }

    /**
     * @Route("/{id}/showVenta", name="ventas_cobro_showventa")
     * @Method("GET")
     * @Template()
     */
    public function showVentaAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:FacturaElectronica')->find($id);
        $venta = $entity->getCobro()->getVenta();
        return $this->redirectToRoute('ventas_venta_show', array('id' => $venta->getId()));
    }
    /**
     * @Route("/{id}/show", name="ventas_cobro_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Cobro')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cobro entity.');
        }
        return $this->render('VentasBundle:Cobro:show.html.twig', array(
                    'entity' => $entity));
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

    /**
     * @Route("/{id}/printCobroVentas.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="ventas_cobro_print")
     * @Method("GET")
     */
    public function printCobroVentasAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $cobro = $em->getRepository('VentasBundle:Cobro')->find($id);
        $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);

        $logo = __DIR__.'/../../../web/assets/images/logo_comprobante.png';
        $qr = __DIR__.'/../../../web/assets/imagesafip/qr.png';
        $logoafip = __DIR__.'/../../../web/assets/imagesafip/logoafip.png';

        $url =$this->getParameter('url_qr_afip');
        $cuit =$this->getParameter('cuit_afip');
        $ptovta =$this->getParameter('ptovta_ws_factura');

        $data = array(
                "ver" => 1,
                "fecha" => $cobro->getFechaCobro()->format('Y-m-d'),
                "cuit" => $cuit,
                "ptoVta" => $ptovta,
                "tipoCmp" => $cobro->getFacturaElectronica()->getCodigoComprobante(),
                "nroCmp" => $cobro->getFacturaElectronica()->getNroComprobante(),
                "importe" => round($cobro->getVenta()->getMontoTotal(),2) ,
                "moneda" => $cobro->getMoneda()->getCodigoAfip(),
                "ctz" => $cobro->getCotizacion(),
                "tipoDocRec" => 0,
                "nroDocRec" => 0,
                "tipoCodAut" => "E",
                "codAut" => $cobro->getFacturaElectronica()->getCae() );
        $base64 = base64_encode( json_encode($data) );

        $qrCode = new QrCode();
        $qrCode
            ->setText($url.$base64)
            ->setSize(120)
            ->setPadding(5)
            ->setErrorCorrection('low')
            ->setImageType(QrCode::IMAGE_TYPE_PNG)
        ;
        $qrCode->render($qr);

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('VentasBundle:Cobro:comprobante.pdf.twig',
                      array( 'cobro' => $cobro, 'venta' => $cobro->getVenta(), 'empresa'=>$empresa, 'logo' => $logo, 'qr' => $qr, 'logoafip'=> $logoafip ), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition'=>'filename='.$cobro->getFacturaElectronica()->getComprobanteTxt().'.pdf'));
    }

    /**
     * @Route("/getAutocompleteFacturas", name="get_autocomplete_facturas")
     * @Method("GET")
     */
    public function getAutocompleteFacturasAction( Request $request) {
        $cliente = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('VentasBundle:Cobro')->filterByCliente($cliente);
        $facturas = array();
        if( $results){
            foreach($results as $row){
                $facturas[] = array('id' => $row->getId(), 'text' => $row->getSelectComprobanteTxt());
            }
        }
        return new JsonResponse($facturas);
    }

    /**
     * @Route("/getItemsComprobante", name="get_items_comprobante")
     * @Method("GET")
     *
     */
    public function getItemsComprobanteAction( Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $comprobante = $em->getRepository('VentasBundle:FacturaElectronica')->find($id);
        $items = array();
        if( $comprobante){
            $detalle = ( $comprobante->getCobro() )
                ? $comprobante->getCobro()->getVenta()->getDetalles()
                : $comprobante->getNotaDebCred()->getDetalles();

            foreach($detalle as $row){
                $items[] = array(
                    'id' => $row->getProducto()->getId(),
                    'text' => $row->getProducto()->getNombre(),
                    'cant' => $row->getCantidad());
            }
        }
        return new JsonResponse($items);
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
