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
   * @Route("/prueba", name="prueba_tickeadora")
   * @Method("GET")
   * @Template()
   */
  public function pruebaAction(Request $request)
  {
    return $this->render('VentasBundle:Cobro:prueba.html.twig');
  }

  /**
   * @Route("/", name="ventas_cobro")
   * @Method("GET")
   * @Template()
   */
  public function indexAction(Request $request)
  {
    $unidneg = $this->get('session')->get('unidneg_id');
    $user = $this->getUser();
    UtilsController::haveAccess($user, $unidneg, 'ventas_factura');
    $em = $this->getDoctrine()->getManager();
    $desde = $request->get('desde');
    $hasta = $request->get('hasta');

    if ($user->getAccess($unidneg, 'ventas_factura_own') && !$user->isAdmin($unidneg)) {
      $id = $user->getId();
      $owns = true;
    } else {
      $id = $request->get('userId');
      $owns = false;
    }
    $entities = $em->getRepository('VentasBundle:Cobro')->findByCriteria($unidneg, $desde, $hasta, $id);
    $users = $em->getRepository('VentasBundle:Cobro')->getUsers();

    $ventas = $em->getRepository('VentasBundle:Venta')->findPorCobrarByCriteria($unidneg, $desde, $hasta, $id);

    return $this->render('VentasBundle:Cobro:index.html.twig', array(
      'entities' => $entities,
      'ventas' => $ventas,
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

    $referer = $request->headers->get('referer');
    if( $this->checkCajaAbierta($em)){
      return $this->redirect($referer);
    }

    $entity = new Cobro();

    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
    if ($param) {
      // ultimo nro de operacion de cobro
      $entity->setNroOperacion($param->getUltimoNroOperacionCobro() + 1);
      $cantidadItemsParaFactura = $param->getCantidadItemsParaFactura();
    }else{
      $this->addFlash('error','No se ha podido acceder a la parametrización. Intente nuevamente o contacte a servicio técnico.');
      return $this->redirect($referer);
    }

    $venta = $em->getRepository('VentasBundle:Venta')->find($id);
    if (!$venta) {
      $this->addFlash('error','No se encuentra este registro de venta.');
      return $this->redirect($referer);
    }

    $entity->setVenta($venta);
    $entity->setCliente($venta->getCliente());
    $entity->setNombreCliente( $venta->getNombreCliente() );
    $entity->setMoneda($venta->getMoneda());
    $entity->setFormaPago($venta->getFormaPago());

    $form = $this->createCreateForm($entity);
    return $this->render('VentasBundle:CobroVenta:form-facturar-venta.html.twig', array(
      'entity' => $entity,
      'form' => $form->createView(),
      'cantidadItemsParaFactura' => $cantidadItemsParaFactura,
    ));

  }

  /**
   * Creates a form to create a Venta entity.
   * @param Cobro $entity The entity
   * @return \Symfony\Component\Form\Form The form
   */
  private function createCreateForm(Cobro $entity)
  {
    $form = $this->createForm(new CobroType(), $entity, array(
      'action' => $this->generateUrl('ventas_cobro_create'),
      'method' => 'POST',
    ));
    return $form;
  }

/**+++++++++++++++++++++++++++++++++++++++++++++++++++++++++  */
  /**
   * @Route("/xfacturarVenta/{id}", name="xventas_cobro_facturar")
   * @Method("GET")
   * @Template()
   */
  public function xfacturarVentaAction(Request $request, $id)
  {
    $session = $this->get('session');
    $unidneg_id = $session->get('unidneg_id');
    UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');
    $em = $this->getDoctrine()->getManager();
    // Verificar si la caja está abierta CAJA=1
    $caja = $em->getRepository('ConfigBundle:Caja')->find(1);
    if (!$caja->getAbierta()) {
      $this->addFlash('error', 'La caja está cerrada. Debe realizar la apertura para iniciar cobros');
      return $this->redirect($request->headers->get('referer'));
    }
    $cantidadItemsParaFactura = 0;
    $entity = new Cobro();
    $entity->setFechaCobro(new \DateTime());
    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
    if ($param) {
      // ultimo nro de operacion de cobro
      $entity->setNroOperacion($param->getUltimoNroOperacionCobro() + 1);
      $cantidadItemsParaFactura = $param->getCantidadItemsParaFactura();
    }
    $venta = $em->getRepository('VentasBundle:Venta')->find($id);
    if (!$venta) {
      throw $this->createNotFoundException('No se encuentra la venta.');
    }
    $entity->setVenta($venta);
    $entity->setCliente($venta->getCliente());
    if( $venta->getNombreCliente() ) $entity->setNombreCliente($venta->getNombreCliente() );
    $entity->setMoneda($venta->getMoneda());
    $entity->setFormaPago($venta->getFormaPago());
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
      'cantidadItemsParaFactura' => $cantidadItemsParaFactura,
    ));
  }


  /**
   * @Route("/", name="ventas_cobro_create")
   * @Method("POST")
   * @Template("VentasBundle:Cobro:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $datos = $request->get('ventasbundle_cobro');
    $ventaId = $request->get('ventasbundle_cobro_venta');
    $cobroId = $request->get('ventasbundle_cobro_id');
    $session = $this->get('session');
    $unidneg_id = $session->get('unidneg_id');
    UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');
    $em = $this->getDoctrine()->getManager();
    $response = array('res' => 'OK' ,'msg' => '', 'id'=> null);

    // Verificar si la caja está abierta CAJA=1
    $apertura = $em->getRepository('VentasBundle:CajaApertura')->findOneBy(array('caja' => 1, 'fechaCierre' => null));
    if (!$apertura) {
      $response['res'] = 'ERROR';
      $response['msg'] = 'La caja está cerrada. Debe realizar la apertura para iniciar cobros';
      return new JsonResponse($response);
    }

    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));

    if($cobroId){
      $entity = $em->getRepository('VentasBundle:Cobro')->find($cobroId);
    }else{
      $entity = new Cobro();
      $entity->setFechaCobro(new \DateTime());
    }

    $form = $this->createCreateForm($entity);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em->getConnection()->beginTransaction();
      try {
        $venta = $em->getRepository('VentasBundle:Venta')->find($ventaId);
        $entity->setVenta($venta);
        $cliente = $entity->getVenta()->getCliente();
        $entity->setCliente($cliente);
        $entity->setMoneda($entity->getVenta()->getMoneda());
        $entity->setCotizacion($entity->getVenta()->getCotizacion());
        $formapago = $em->getRepository('ConfigBundle:FormaPago')->find($request->get('select_formapago'));
        if( $entity->getVenta()->getFormaPago() !== $formapago ){
          $venta->setFormaPago( $formapago );
          $venta->setDescuentoRecargo( $formapago->getPorcentajeRecargo() );
          $em->persist($venta);
        }
        $entity->setFormaPago($formapago);

        if($cliente->getConsumidorFinal()){
          $entity->setNombreCliente( $request->get('ventasbundle_nombreCliente') );
          $tipoDoc = $em->getRepository('ConfigBundle:Parametro')->find($request->get('ventasbundle_tipoDocumentoCliente'));
          $entity->setTipoDocumentoCliente($tipoDoc);
          $entity->setNroDocumentoCliente($request->get('ventasbundle_nroDocumentoCliente'));
        }

        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $entity->setUnidadNegocio($unidneg);

        if ($param && !$entity->getNroOperacion()) {
          // cargar datos parametrizados por defecto
          $entity->setNroOperacion($param->getUltimoNroOperacionCobro() + 1);
          $param->setUltimoNroOperacionCobro($entity->getNroOperacion());
          $em->persist($param);
        }

        // completar datos de detalles
        // $saldo = 0;
        $efectivo = true;
        if (count($entity->getDetalles()) == 0) {
          if ($formapago->getTipoPago() === 'CTACTE') {
            // insertar un detalle para ctacte
            $detalle = new CobroDetalle();
            $detalle->setTipoPago('CTACTE');
            $detalle->setMoneda($entity->getMoneda());
            $detalle->setImporte($venta->getMontoTotal());
            $detalle->setCajaApertura($apertura);
            $entity->addDetalle($detalle);
            // $saldo = round($impTotal, 2);
            $efectivo = false;
          }
        } else {
          foreach ($entity->getDetalles() as $detalle) {
            $detalle->setCajaApertura($apertura);
            if (!$detalle->getMoneda()) {
              $detalle->setMoneda($entity->getMoneda());
            }
            $tipoPago = $detalle->getTipoPago();
            if ($tipoPago !== 'CHEQUE') {
              $detalle->setChequeRecibido(null);
            }
            if ($tipoPago !== 'TARJETA') {
              $detalle->setDatosTarjeta(null);
            }
            if ($tipoPago !== 'EFECTIVO') {
              $efectivo = false;
            }
          }
        }

        $entity->getVenta()->setEstado('FACTURADO');
        $entity->setEstado('CREADO');
        $em->persist($entity);
         $em->flush();
        $em->getConnection()->commit();

        $response['res'] = 'OK';
        $response['msg'] = 'El registro del cobro se han guardado correctamente!';
        $response['id'] = $entity->getId();
        return new JsonResponse($response);

      } catch (\Exception $ex) {
        $em->getConnection()->rollback();
        $response['res'] = 'ERROR';
        $response['msg'] = $ex->getMessage();
        return new JsonResponse($response);
      }

    }


die;


///////

    // $nroTicketB = $datos['nroTicket'];

    $session = $this->get('session');
    $unidneg_id = $session->get('unidneg_id');
    UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');
    $em = $this->getDoctrine()->getManager();
    // Verificar si la caja está abierta CAJA=1
    //$caja = $em->getRepository('ConfigBundle:Caja')->find(1);
    $apertura = $em->getRepository('VentasBundle:CajaApertura')->findOneBy(array('caja' => 1, 'fechaCierre' => null));
    if (!$apertura) {
      $this->addFlash('error', 'La caja está cerrada. Debe realizar la apertura para iniciar cobros');
      return $this->redirect($request->headers->get('referer'));
    }
    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));

    $entity = new Cobro();
    $form = $this->createCreateForm($entity, 'create');
    $form->handleRequest($request);

    // SACAR SI DESPUES SE HABILITA MODIFICAR CLIENTE, FORMA DE PAGO Y MONEDA
    $entity->setCliente($entity->getVenta()->getCliente());
    //$entity->setFormaPago($entity->getVenta()->getFormaPago());
    $entity->setMoneda($entity->getVenta()->getMoneda());

    ////////////////////////////////

    if ($form->isValid()) {
      $em->getConnection()->beginTransaction();
      try {
        $facturaElectronica = new FacturaElectronica();
        // REVISAR COMO MODIFICAR PARA OPTIMIZAR CUANDO SE IMPRIMIÓ TICKET.

        // armar datos para webservice
        $docTipo = 99;
        $docNro = 0;
        if ($entity->getCliente()->getCuit()) {
          $docTipo = 80;
          $docNro = trim($entity->getCliente()->getCuit());
        } elseif ($entity->getTipoDocumentoCliente()) {
          $docTipo = $entity->getTipoDocumentoCliente()->getCodigo();
          $docNro = $entity->getNroDocumentoCliente();
        }

        $catIva = ($entity->getCliente()->getCategoriaIva()) ? $entity->getCliente()->getCategoriaIva()->getNombre() : 'C';
        //$entity->setDescuentoRecargo($entity->getFormaPago()->getPorcentajeRecargo()) ;
        $iva = $tributos = array();
        if ($entity->getVenta()->getDetalles()) {
          $impTotal = $impNeto = $impIVA = $impTrib = $impDtoRec = 0;
          foreach ($entity->getVenta()->getDetalles() as $item) {
            $alicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->findOneBy(array('valor' => $item->getProducto()->getIva()));
            $codigo = intval($alicuota->getCodigo());
            $dtoRec = $item->getTotalDtoRecItem() /  $entity->getVenta()->getCotizacion();
            $baseImp = $item->getBaseImponibleItem() + $dtoRec;
            $importe = $item->getTotalIvaItem() /  $entity->getVenta()->getCotizacion();
            $key = array_search($codigo, array_column($iva, 'Id'));
            // IVA
            /*  array(
                            'Id' 		=> 5, // Id del tipo de IVA (ver tipos disponibles)
                            'BaseImp' 	=> 100, // Base imponible
                            'Importe' 	=> 21 // Importe
                        )*/
            if ($importe > 0) {
              if ($key === false) {
                $iva[] = array(
                  'Id' => $codigo,
                  'BaseImp' => round($baseImp, 2),
                  'Importe' => round($importe, 2)
                );
              } else {
                $iva[$key] = array(
                  'Id' => $codigo,
                  'BaseImp' => round($iva[$key]['BaseImp'] + $baseImp, 2),
                  'Importe' => round($iva[$key]['Importe'] + $importe, 2)
                );
              }
              // TOTALES
              $impDtoRec += $dtoRec;
              $impNeto += $baseImp;
              $impIVA += $importe;
              $impTotal += ($baseImp + $importe);
              //$item->setDescuento($dtoRec);
            }
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
          if ($catIva == 'I') {
            $neto = round($impNeto, 2);
            $iibb = round(($neto * $this->getParameter('iibb_percent')/100 ), 2);
            $impTrib = $iibb;
            $tributos = array(
              'Id' => 7,
              'BaseImp' => $neto,
              'Alic' => $this->getParameter('iibb_percent'),
              'Importe' => $iibb
            );
          }
          $impTotal += $impTrib;
        }

        // completar datos de detalles
        $saldo = 0;
        $efectivo = true;
        if (count($entity->getDetalles()) == 0) {
          if ($entity->getFormaPago()->getTipoPago() == 'CTACTE') {
            // insertar un detalle para ctacte
            $detalle = new CobroDetalle();
            $detalle->setTipoPago('CTACTE');
            $detalle->setMoneda($entity->getMoneda());
            $detalle->setImporte($impTotal);
            $detalle->setCajaApertura($apertura);
            $entity->addDetalle($detalle);
            $saldo = round($impTotal, 2);
            $efectivo = false;
          }
        } else {
          foreach ($entity->getDetalles() as $detalle) {
            $detalle->setCajaApertura($apertura);
            if (!$detalle->getMoneda()) {
              $detalle->setMoneda($entity->getMoneda());
            }
            $tipoPago = $detalle->getTipoPago();
            if ($tipoPago != 'CHEQUE') {
              $detalle->setChequeRecibido(null);
            }
            if ($tipoPago != 'TARJETA') {
              $detalle->setDatosTarjeta(null);
            }
            if ($tipoPago != 'EFECTIVO') {
              $efectivo = false;
            }
          }
        }
        // definir tipo de factura segun cliente

        $tipoComprobante = 'FAC-B';
        $ptovta = $this->getParameter('ptovta_ws_factura');
        if ($catIva == 'I' || $catIva == 'M') {
          $tipoComprobante = 'FAC-A';
        }
        $esCategoriaC = $catIva == 'C';
        $esMonedaPesos = $entity->getMoneda()->getCodigoAfip() == 'PES';
        $cantidadItemsTicket =  count($entity->getVenta()->getDetalles()) <= $param->getCantidadItemsParaFactura() ;

        if ( $esCategoriaC && $esMonedaPesos && $cantidadItemsTicket) {
          $ptovta = $this->getParameter('ptovta_ifu_ticket');
          $tipoComprobante = 'TICK-B';
        }

        $tipoFactura = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByValor($tipoComprobante);
        $facturaElectronica->setTipoComprobante($tipoFactura);
        $facturaElectronica->setPuntoVenta($ptovta);
        $entity->setFacturaElectronica($facturaElectronica);

        // emitir comprobante electronico si no es tique
        if ($tipoComprobante != 'TICK-B') {
          /**  INICIO EMISION FACTURA WEBSERVICE */
          $afip = new Afip(array('CUIT' => $this->getParameter('cuit_afip')));

          $data = array(
            'CantReg'   => 1,  // Cantidad de comprobantes a registrar
            'PtoVta'   => $facturaElectronica->getPuntoVenta(),  // Punto de venta
            'CbteTipo'   => $facturaElectronica->getCodigoComprobante(),  // Tipo de comprobante (ver tipos disponibles)
            'Concepto'   => 1,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
            'DocTipo'   => $docTipo, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
            'DocNro'   => $docNro,  // Número de documento del comprador (0 consumidor final)
            'CbteFch'   => intval($entity->getFechaCobro()->format('Ymd')), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
            'ImpTotal'   => round($impTotal, 2), // Importe total del comprobante
            'ImpTotConc'   => 0,   // Importe neto no gravado
            'ImpNeto'   => round($impNeto, 2), // Importe neto gravado
            'ImpOpEx'   => 0,   // Importe exento de IVA
            'ImpIVA'   => round($impIVA, 2),  //Importe total de IVA
            'ImpTrib'   => round($impTrib, 2),   //Importe total de tributos
            'MonId'   => $entity->getMoneda()->getCodigoAfip(), //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos)
            'MonCotiz'   => $entity->getMoneda()->getCotizacion(),     // Cotización de la moneda usada (1 para pesos argentinos)
            'Tributos' => $tributos,
            'Iva'       => $iva,
          );
          // si no hay tributos
          if (empty($tributos)) {
            unset($data['Tributos']);
          }
          $wsResult = $afip->ElectronicBilling->CreateNextVoucher($data);
          $facturaElectronica->setCae($wsResult['CAE']);
          $facturaElectronica->setCaeVto($wsResult['CAEFchVto']);
          $facturaElectronica->setNroComprobante($wsResult['voucher_number']);
          /**  FIN EMISION FACTURA WEBSERVICE */
          $comprobante = $entity->getFacturaElectronica()->getComprobanteTxt();
        } else {
          /** EMISIÓN A TIQUEADORA */
          $hoy = new \DateTime();
          $facturaElectronica->setCae('');
          $facturaElectronica->setCaeVto($hoy->format('Y-m-d'));
          $facturaElectronica->setNroComprobante($nroTicketB);
          $comprobante = 'TICK-B ';
        }
        //$entity->setIva($impIVA);
        //$entity->setPercIibb($impTrib);
        //$entity->setTotal($impTotal);
        //$entity->setSaldo($impTotal);
        // $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        // $entity->setUnidadNegocio($unidneg);

        // Guardar datos en factura electronica
        $facturaElectronica->setCobro($entity);
        $facturaElectronica->setTotal($impTotal);
        $facturaElectronica->setSaldo(round($saldo, 2));
        $em->persist($facturaElectronica);

        // set numeracion
        // if ($param) {
        //   // cargar datos parametrizados por defecto
        //   $entity->setNroOperacion($param->getUltimoNroOperacionCobro() + 1);
        //   $param->setUltimoNroOperacionCobro($entity->getNroOperacion());
        //   $em->persist($param);
        // }

        // $entity->getVenta()->setEstado('FACTURADO');
        // $entity->setEstado('FINALIZADO');
        $em->persist($entity);
        $em->flush();
        $em->getConnection()->commit();

        $this->addFlash('success', 'Emitido el comprobante ' . $comprobante);
        if ($tipoComprobante == 'TICK-B') {
          // EMITIDO TICKET NO FACTURA
          return $this->redirect($this->generateUrl('ventas_cobro'));
        }
        return $this->redirect($this->generateUrl('ventas_cobro', array('printpdf' => $entity->getId())));
      } catch (\Exception $ex) {
        $this->addFlash('error', $ex->getMessage());
        $em->getConnection()->rollback();
      }
    }
    //$this->addFlash('error', 'invalid');
    return $this->render('VentasBundle:Cobro:facturar-venta.html.twig', array(
      'entity' => $entity,
      'form' => $form->createView(),
      'cantidadItemsParaFactura' => $param->getCantidadItemsParaFactura(),
    ));
  }


  /**
   * @Route("/{id}/edit", name="ventas_cobro_edit")
   * @Method("GET")
   * @Template()
   */
  public function editAction($id)
  {
    $session = $this->get('session');
    $unidneg_id = $session->get('unidneg_id');
    UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_factura_new');

    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('VentasBundle:Cobro')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('No se encuentra el Cobro.');
    }
    $editForm = $this->createEditForm($entity);

    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));

    return $this->render('VentasBundle:CobroVenta:form-facturar-venta.html.twig', array(
      'entity' => $entity,
      'form' => $editForm->createView(),
      'cantidadItemsParaFactura' => $param->getCantidadItemsParaFactura(),
    ));
  }


  private function createEditForm(Cobro $entity)
  {
    $form = $this->createForm(new CobroType(), $entity, array(
      'action' => $this->generateUrl('ventas_cobro_create', array('id' => $entity->getVenta()->getId())),
      'method' => 'POST',
      'attr' => array('type' => ''),
    ));
    return $form;
  }



  /**
   * @Route("/{id}/showVenta", name="ventas_cobro_showventa")
   * @Method("GET")
   * @Template()
   */
  public function showVentaAction($id)
  {
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
  public function showAction($id)
  {
    UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura');
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('VentasBundle:Cobro')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Cobro entity.');
    }
    return $this->render('VentasBundle:Cobro:show.html.twig', array(
      'entity' => $entity
    ));
  }

  /**
   * @Route("/ventasPorCobrar", name="ventas_por_cobrar")
   */
  public function ventasPorCobrarAction()
  {
    $em = $this->getDoctrine()->getManager();
    $ventas = $em->getRepository('VentasBundle:Venta')->findBy(array('estado' => 'PENDIENTE'), array('nroOperacion' => 'ASC'));

    return $this->render('VentasBundle:Cobro:_partial-ventas-por-cobrar.html.twig', array(
      'ventas' => $ventas
    ));
  }

  /**
   * @Route("/ventasViewDetail", name="ventas_view_detail")
   */
  public function ventasViewDetailAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $venta = $em->getRepository('VentasBundle:Venta')->find( $request->get('id'));

    return $this->render('VentasBundle:Cobro:_partial-view-detalle.html.twig', array(
      'venta' => $venta
    ));
  }

  /**
   * @Route("/{id}/printCobroVentas.{_format}",
   * defaults = { "_format" = "pdf" },
   * name="xventas_cobro_print")
   * @Method("GET")
   */
  public function printCobroVentasAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $cobro = $em->getRepository('VentasBundle:Cobro')->find($id);
    $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);

    $logo = __DIR__ . '/../../../web/assets/images/logo_comprobante.png';
    $qr = __DIR__ . '/../../../web/assets/imagesafip/qr.png';
    $logoafip = __DIR__ . '/../../../web/assets/imagesafip/logoafip.png';

    $url = $this->getParameter('url_qr_afip');
    $cuit = $this->getParameter('cuit_afip');
    $ptovta = $this->getParameter('ptovta_ws_factura');

    $data = array(
      "ver" => 1,
      "fecha" => $cobro->getFechaCobro()->format('Y-m-d'),
      "cuit" => $cuit,
      "ptoVta" => $ptovta,
      "tipoCmp" => $cobro->getFacturaElectronica()->getCodigoComprobante(),
      "nroCmp" => $cobro->getFacturaElectronica()->getNroComprobante(),
      "importe" => round($cobro->getVenta()->getMontoTotal(), 2),
      "moneda" => $cobro->getMoneda()->getCodigoAfip(),
      "ctz" => $cobro->getCotizacion(),
      "tipoDocRec" => 0,
      "nroDocRec" => 0,
      "tipoCodAut" => "E",
      "codAut" => $cobro->getFacturaElectronica()->getCae()
    );
    $base64 = base64_encode(json_encode($data));

    $qrCode = new QrCode();
    $qrCode
      ->setText($url . $base64)
      ->setSize(120)
      ->setPadding(5)
      ->setErrorCorrection('low')
      ->setImageType(QrCode::IMAGE_TYPE_PNG);
    $qrCode->render($qr);

    $facade = $this->get('ps_pdf.facade');
    $response = new Response();
    $this->render(
      'VentasBundle:Cobro:comprobante.pdf.twig',
      array('cobro' => $cobro, 'venta' => $cobro->getVenta(), 'empresa' => $empresa, 'logo' => $logo, 'qr' => $qr, 'logoafip' => $logoafip),
      $response
    );

    $xml = $response->getContent();
    $content = $facade->render($xml);
    $hoy = new \DateTime();
    $filename = $cobro->getFacturaElectronica()->getComprobanteTxt() . '.pdf';
    if ($this->getParameter('billing_folder')) {
      $file = $this->getParameter('billing_folder') . $filename;
      if (!file_exists($file)) {
        file_put_contents($file, $content, FILE_APPEND);
      }
    }
    return new Response($content, 200, array(
      'content-type' => 'application/pdf',
      'Content-Disposition' => 'filename=' . $filename
    ));
  }

  /**
   * @Route("/getAutocompleteFacturas", name="get_autocomplete_facturas")
   * @Method("GET")
   */
  public function getAutocompleteFacturasAction(Request $request)
  {
    $cliente = $request->get('id');
    $em = $this->getDoctrine()->getManager();
    $results = $em->getRepository('VentasBundle:Cobro')->filterByCliente($cliente);
    $facturas = array();
    if ($results) {
      foreach ($results as $row) {
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
  public function getItemsComprobanteAction(Request $request)
  {
    $id = $request->get('id');
    $em = $this->getDoctrine()->getManager();
    $comprobante = $em->getRepository('VentasBundle:FacturaElectronica')->find($id);
    $items = array();
    if ($comprobante) {
      $detalle = ($comprobante->getCobro())
        ? $comprobante->getCobro()->getVenta()->getDetalles()
        : $comprobante->getNotaDebCred()->getDetalles();

      foreach ($detalle as $row) {
        $items[] = array(
          'id' => $row->getProducto()->getId(),
          'text' => $row->getProducto()->getNombre(),
          'cant' => $row->getCantidad(),
          'comodin' => $row->getTextoComodin(),
          'precio' => $row->getPrecio(),
          'alicuota' => $row->getAlicuota()
        );
      }
    }
    return new JsonResponse($items);
  }

  /**
   * @Route("/getTiposComprobanteValido", name="get_tipos_comprobante_valido")
   * @Method("GET")
   *
   */
  public function getTiposComprobanteValido(Request $request)
  {
    $id = $request->get('id');
    $em = $this->getDoctrine()->getManager();
    $comprobante = $em->getRepository('VentasBundle:FacturaElectronica')->find($id);
    $items = array();
    if ($comprobante) {
      $valor = explode('-', $comprobante->getTipoComprobante()->getValor());
      $tipos = $em->getRepository('ConfigBundle:AfipComprobante')->findByValor( array( 'CRE-'.$valor[1] , 'DEB-'.$valor[1] ) );
      foreach ($tipos as $tipo) {
        $items[] = $tipo->getId();
      }
    }
    return new JsonResponse($items);
  }

  private function checkCajaAbierta($em)
  {
    $apertura = $em->getRepository('VentasBundle:CajaApertura')->findOneBy(array('caja' => 1, 'fechaCierre' => null));
    if (!$apertura) {
      $this->addFlash('error', 'La caja está cerrada. Debe realizar la apertura para iniciar cobros');
      return true;
    }
    return false;
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
