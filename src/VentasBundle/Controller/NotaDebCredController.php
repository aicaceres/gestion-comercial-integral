<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\NotaDebCred;
use VentasBundle\Form\NotaDebCredType;
use VentasBundle\Entity\FacturaElectronica;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;
use VentasBundle\Afip\src\Afip;

use Endroid\QrCode\QrCode;
/**
 * @Route("/notadebcredVentas")
 */
class NotaDebCredController extends Controller {

    /**
     * @Route("/", name="ventas_notadebcred")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $cliId = $request->get('cliId');
        $cliente = null;
        if($cliId){
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($cliId);
        }
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        $entities = $em->getRepository('VentasBundle:NotaDebCred')->findByCriteria($unidneg, $cliId, $desde, $hasta);

        return $this->render('VentasBundle:NotaDebCred:index.html.twig', array(
                    'entities' => $entities,
                    'cliente' => $cliente,
                    'cliId' => $cliId,
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'printpdf' => $request->get('printpdf')
        ));
    }

    /**
     * @Route("/new", name="ventas_notadebcred_new")
     * @Method("GET")
     * @Template("VentasBundle:NotaDebCred:new.html.twig")
     */
    public function newAction(Request $request) {
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_notadebcred');
        $entity = new NotaDebCred();
        $entity->setFecha( new \DateTime() );
        $em = $this->getDoctrine()->getManager();
        $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if($param){
            // cargar datos parametrizados por defecto
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
            $entity->setCliente($cliente);
            $entity->setFormaPago( $cliente->getFormaPago() );
            $entity->setDescuentoRecargo( $cliente->getFormaPago()->getPorcentajeRecargo() );
            $entity->setPrecioLista( $cliente->getPrecioLista() );
            $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneBy(array('byDefault' =>1));
            $entity->setMoneda( $moneda );
            $entity->setCotizacion( $moneda->getCotizacion() );
            $notaElectronica = new FacturaElectronica();
            $notaElectronica->setPuntoVenta( $param->getPuntoVentaFactura() );
            $entity->setNotaElectronica($notaElectronica);
        }

        $form = $this->createCreateForm($entity,'new');
        return $this->render('VentasBundle:NotaDebCred:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a NotaCredito entity.
     * @param NotaCredito $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(NotaDebCred $entity,$type) {
        $afip = new Afip(array('CUIT'=> $this->getParameter('cuit_afip')));
        $afipDocType = $afip->ElectronicBilling->getDocumentTypes();
        $docType = array_reduce( $afipDocType , function($array,$item){
            $array[ $item->Id ] = $item->Desc;
            return $array;
        });
        $form = $this->createForm(new NotaDebCredType(), $entity, array(
            'action' => $this->generateUrl('ventas_notadebcred_create'),
            'method' => 'POST' ,
            'attr' => array('type'=>$type, 'docType'=> json_encode($docType) ) ,
        ));
        return $form;
    }

    /**
     * @Route("/", name="ventas_notadebcred_create")
     * @Method("POST")
     * @Template("VentasBundle:NotaDebCred:new.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        // credito - (descuenta de la deuda con proveedor)
        // debito + (aumenta la deuda con el proveedor)
        $entity = new NotaDebCred();
        $form = $this->createCreateForm($entity,'create');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $notaElectronica = $entity->getNotaElectronica();
            $docTipo = 99 ;
            $docNro = 0;
            if( $entity->getCliente()->getCuit() ){
                $docTipo = 80 ;
                $docNro = trim($entity->getCliente()->getCuit());
            }elseif ($entity->getTipoDocumentoCliente() ) {
                $docTipo = $entity->getTipoDocumentoCliente();
                $docNro = $entity->getNroDocumentoCliente();
            }
            $cbtesAsoc = array();
            if( $entity->getFacturas() ){
                /* array(
                    'Tipo' 		=> 6, // Tipo de comprobante (ver tipos disponibles)
                    'PtoVta' 	=> 1, // Punto de venta
                    'Nro' 		=> 1 // Numero de comprobante
                    )
                )*/
                foreach( $entity->getFacturas() as $factura ){
                    $cbtesAsoc[] = array( 'Tipo' => $factura->getCodigoComprobante(),
                                          'PtoVta' => $factura->getPuntoVenta(),
                                          'Nro' => $factura->getNroComprobante() );
                }
            }

            $catIva = ( $entity->getCliente()->getCategoriaIva() ) ? $entity->getCliente()->getCategoriaIva()->getNombre() : 'C';
            $entity->setDescuentoRecargo($entity->getFormaPago()->getPorcentajeRecargo()) ;
            $iva = $tributos = array();
            if( $entity->getDetalles() ){
                $impTotal = $impNeto = $impIVA = $impTrib = $impDtoRec = 0;
                foreach( $entity->getDetalles() as $item ){
                    $alicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->findOneBy( array   ('valor'=>$item->getProducto()->getIva()));
                    $codigo = intval($alicuota->getCodigo());
                    $dtoRec = $item->getPrecio() * ($entity->getDescuentoRecargo()/100);
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
                    $item->setDescuento($dtoRec);
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

            try {
                // realizar nota electronica
                $afip = new Afip(array('CUIT'=> $this->getParameter('cuit_afip')));

                $data = array(
                    'CantReg' 	=> 1,  // Cantidad de comprobantes a registrar
                    'PtoVta' 	=> $notaElectronica->getPuntoVenta(),  // Punto de venta
                    'CbteTipo' 	=> $notaElectronica->getCodigoComprobante(),  // Tipo de comprobante (ver tipos disponibles)
                    'Concepto' 	=> 1,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
                    'DocTipo' 	=> $docTipo, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
                    'DocNro' 	=> $docNro,  // Número de documento del comprador (0 consumidor final)
                    'CbteFch' 	=> intval( $entity->getFecha()->format('Ymd') ), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
                    'ImpTotal' 	=> round($impTotal,2) , // Importe total del comprobante
                    'ImpTotConc' 	=> 0,   // Importe neto no gravado
                    'ImpNeto' 	=> round($impNeto,2) , // Importe neto gravado
                    'ImpOpEx' 	=> 0,   // Importe exento de IVA
                    'ImpIVA' 	=> round($impIVA,2),  //Importe total de IVA
                    'ImpTrib' 	=> round($impTrib,2),   //Importe total de tributos
                    'MonId' 	=> $entity->getMoneda()->getCodigoAfip(), //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos)
                    'MonCotiz' 	=> $entity->getMoneda()->getCotizacion(),     // Cotización de la moneda usada (1 para pesos argentinos)
                    'Tributos' => $tributos,
                    'CbtesAsoc' 	=> $cbtesAsoc,
                    'Iva' 			=> $iva,
                );
                // si no hay tributos
                if( empty($tributos) ){
                    unset( $data['Tributos'] );
                }

                $wsResult = $afip->ElectronicBilling->CreateNextVoucher($data);

                $entity->setIva($impIVA);
                $entity->setPercIibb($impTrib);
                $entity->setTotal($impTotal);
                $entity->setSaldo($impTotal);
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
                $entity->setUnidadNegocio($unidneg);
                // signo
                $signo = ( substr($notaElectronica->getTipoComprobante()->getValor(),0,3) == 'DEB'  ) ? '+' : '-';
                $entity->setSigno($signo);
                // Guardar datos en factura electronica
                $notaElectronica->setNotaDebCred($entity);
                $notaElectronica->setCae($wsResult['CAE']);
                $notaElectronica->setCaeVto($wsResult['CAEFchVto']);
                $notaElectronica->setNroComprobante($wsResult['voucher_number']);

                $em->persist($notaElectronica);
                $em->persist($entity);
                $em->flush();

                if( $entity->getSigno() == '-' && $entity->getDetalles()){
                        // Reponer de stock si es credito
                    $deposito = $deposito = $em->getRepository('AppBundle:Deposito')->findOneByPordefecto(1);
                    foreach ($entity->getDetalles() as $detalle){
                        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
                        if ($stock) {
                            $stock->setCantidad($stock->getCantidad() + $detalle->getCantidad());
                        }else {
                            $stock = new Stock();
                            $stock->setProducto($detalle->getProducto());
                            $stock->setDeposito($deposito);
                            $stock->setCantidad( 0 - $detalle->getCantidad());
                        }
                        $em->persist($stock);

                // Cargar movimiento
                        $movim = new StockMovimiento();
                        $movim->setFecha($entity->getFecha());
                        $movim->setTipo('ventas_notadebcred');
                        $movim->setSigno('+');
                        $movim->setMovimiento($entity->getId());
                        $movim->setProducto($detalle->getProducto());
                        $movim->setCantidad($detalle->getCantidad());
                        $movim->setDeposito($deposito);
                        $em->persist($movim);
                        $em->flush();
                    }
                }

                $em->getConnection()->commit();
                $this->addFlash('success', 'El comprobante fue registrado correctamente.');

                return $this->redirectToRoute('ventas_notadebcred', array('printpdf' => $entity->getId()));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }

        return $this->render('VentasBundle:NotaDebCred:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/show", name="ventas_notadebcred_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota de Débito/Crédito.');
        }
        return $this->render('VentasBundle:NotaDebCred:show.html.twig', array(
                    'entity' => $entity));
    }

    /**
     * @Route("/{id}/edit", name="ventas_notadebcred_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota.');
        }
        $editForm = $this->createEditForm($entity);
        // if($entity->getEstado()=='NUEVO')
        //     $this->get('session')->getFlashBag()->add('alert','Los productos serán reingresados al stock cuando se marque el envío al Proveedor' );
        return $this->render('VentasBundle:NotaDebCred:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a NotaCredito entity.
     * @param NotaCredito $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(NotaDebCred $entity) {
        $form = $this->createForm(new NotaDebCredType(), $entity, array(
            'action' => $this->generateUrl('ventas_notadebcred_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="ventas_notadebcred_update")
     * @Method("PUT")
     * @Template("VentasBundle:NotaDebCred:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $modificaStock = ($request->get('modificaStock') == 'SI') ? true : false;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota.');
        }
        $original = new ArrayCollection();
        // Create an ArrayCollection of the current objects in the database
        foreach ($entity->getDetalles() as $item) {
            $original->add($item);
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // remove the relationship between the item and the pedido
                foreach ($original as $item) {
                    if (false === $entity->getDetalles()->contains($item)) {
                        $em->remove($item);
                    }
                }
                $em->flush();


                $em->getConnection()->commit();
            }
            catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('VentasBundle:NotaCredito:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
    }

    public function acreditarAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaCredito')->find($id);
        $entity->setEstado('ACREDITADO');
        $em->persist($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('ventas_notacredito'));
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/{id}/printNotaDebCredVentas.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_notadebcred_ventas")
     * @Method("GET")
     */
    public function printNotaDebCredAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $nota = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);

        $logo = __DIR__.'/../../../web/assets/images/logo_comprobante.png';
        $qr = __DIR__.'/../../../web/assets/imagesafip/qr.png';
        $logoafip = __DIR__.'/../../../web/assets/imagesafip/logoafip.png';

        $url =$this->getParameter('url_qr_afip');
        $cuit =$this->getParameter('cuit_afip');
        $ptovta =$this->getParameter('ptovta_ws_afip');

        $data = array(
                "ver" => 1,
                "fecha" => $nota->getFecha()->format('Y-m-d'),
                "cuit" => $cuit,
                "ptoVta" => $ptovta,
                "tipoCmp" => $nota->getNotaElectronica()->getCodigoComprobante(),
                "nroCmp" => $nota->getNotaElectronica()->getNroComprobante(),
                "importe" => round($nota->getMontoTotal(),2) ,
                "moneda" => $nota->getMoneda()->getCodigoAfip(),
                "ctz" => $nota->getCotizacion(),
                "tipoDocRec" => 0,
                "nroDocRec" => 0,
                "tipoCodAut" => "E",
                "codAut" => $nota->getNotaElectronica()->getCae() );
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
        $this->render('VentasBundle:NotaDebCred:comprobante.pdf.twig',
                      array( 'nota' => $nota, 'empresa'=>$empresa, 'logo' => $logo, 'qr' => $qr, 'logoafip'=> $logoafip ), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition'=>'filename='.$nota->getNotaElectronica()->getComprobanteTxt().'.pdf'));
    }



    /**
     * @Route("/printVentasListNotaDebCred.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ventas_list_notadebcred")
     * @Method("POST")
    */
    public function printVentasListNotaDebCredAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $clienteId = $request->get('clienteid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $cliente = $em->getRepository('VentasBundle:Cliente')->find($clienteId);
        $textoFiltro = array($cliente ? $cliente->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('VentasBundle:NotaDebCred:pdf-notasdebcred.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro,
                    'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_ventas_notasdebcred_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

}