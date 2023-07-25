<?php

namespace VentasBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\FacturaElectronica;
use VentasBundle\Afip\src\Afip;
use Endroid\QrCode\QrCode;

class FacturaElectronicaWebservice
{

    private $em;
    private $session;
    private $ptovtaWsFactura;
    private $iibbPercent;
    private $cuitAfip;

    private $webserviceError = array(
        10013 => "Para comprobantes clase A y M el tipo de documento debe ser CUIT",
        10015 => "Para facturas B de este monto, debe ingresar tipo y nro de documento del cliente.",
        10016 => "Se está intentando autorizar un comprobante con fecha anterior al último informado.",
        10208 => 'La fecha hasta del periodo asociado tiene que ser anterior o igual a la Fecha de Emision del comprobante.',
        10024 => "Si ImpTrib es mayor a 0 el objeto Tributos y Tributo son obligatorios.",
        10047 => "El campo ImpIVA (Importe de IVA) para comprobantes tipo C debe ser igual a cero (0)",
        10048 => "El campo  'Importe Total' ImpTotal, debe ser igual  a la  suma de ImpNeto + ImpTrib. Donde ImpNeto es igual al Sub Total",
        10071 => "Para comprobantes tipo C el objeto IVA no debe informarse.",
      );

    public function __construct( EntityManager $em, Session $session, $ptovtaWsFactura, $iibbPercent, $cuitAfip )
    {
      $this->em = $em;
      $this->session = $session;
      $this->ptovtaWsFactura = $ptovtaWsFactura;
      $this->iibbPercent = $iibbPercent;
      $this->cuitAfip = $cuitAfip;
    }

    public function emitir($id,$entity)
    {
      $em = $this->em;
      $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->session->get('unidneg_id'));
      $comprobante = $em->getRepository('VentasBundle:'.$entity)->find($id);
      $cliente = $comprobante->getCliente();
      // Cobro o NotaDebCred
      $esCobro = $entity === 'Cobro';
      $detalles = $esCobro ? $comprobante->getVenta()->getDetalles() : $comprobante->getDetalles() ;
      $response['res'] = 'ERROR';
      if( $esCobro ){
        if( $comprobante->getFacturaElectronica() ){
          $response['res'] = 'OK';
          $response['msg'] = 'Este comprobante ya ha sido facturado!!';
          return $response;
        }
      }

      $response['msg'] = 'Ha ocurrido un error al procesar el comprobante en Afip!';

      try {
        $em->getConnection()->beginTransaction();
        // armar datos para webservice
        $fe = new FacturaElectronica();
        $fe->setUnidadNegocio($unidneg);

        $fe->setPuntoVenta($this->ptovtaWsFactura);
        $fe->setConcepto(1); // Productos
        $cbteFch = $esCobro ? $comprobante->getFechaCobro() : $comprobante->getFecha();
        $fe->setCbteFch( intval( $cbteFch->format('Ymd')) );
        $docTipo = 99;
        $docNro = 0;
        $fe->setNombreCliente($comprobante->getNombreClienteTxt());
        if ($cliente->getCuit()) {
          $docTipo = 80;
          $docNro = trim($cliente->getCuit());
        } elseif ($comprobante->getTipoDocumentoCliente()) {
          $docTipo = $comprobante->getTipoDocumentoCliente()->getCodigo();
          $docNro = $comprobante->getNroDocumentoCliente();
        }
        $fe->setDocTipo($docTipo);
        $fe->setDocNro($docNro);

        $catIva = ($cliente->getCategoriaIva()) ? $cliente->getCategoriaIva()->getNombre() : 'C';

        $cbtesAsoc = $periodoAsoc = array();
        if($esCobro){
          //* COBRO
          $valor = ($catIva == 'I' || $catIva == 'M') ? 'FAC-A' : 'FAC-B';
          $tipoComprobante = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByValor($valor);
        }else{
          //* NOTA DEBITO CREDITO
          if ($comprobante->getComprobanteAsociado()) {
            /* array(
                'Tipo' 		=> 6, // Tipo de comprobante (ver tipos disponibles)
                'PtoVta' 	=> 1, // Punto de venta
                'Nro' 		=> 1 // Numero de comprobante
                )
            )*/
            $facturaAsoc = $comprobante->getComprobanteAsociado();
            $cbtesAsoc[] = array(
              'Tipo'   =>  $facturaAsoc->getCodigoComprobante(),
              'PtoVta' =>  $facturaAsoc->getPuntoVenta(),
              'Nro'    =>  $facturaAsoc->getNroComprobante()
            );
          } else {
            /* array(
                'FchDesde' => Ymd
                'FchHasta'  => Ymd
                )
            */
            $periodoAsoc = array('FchDesde' => intval($comprobante->getPeriodoAsocDesde()->format('Ymd')), 'FchHasta' => intval($comprobante->getPeriodoAsocHasta()->format('Ymd')));
          }
          $tipoComprobante = $comprobante->getTipoComprobante();
        }

        $fe->setTipoComprobante($tipoComprobante);

        $iva = $tributos = array();
        if($detalles){
          $impTotal = $impNeto = $impIVA = $impTrib = $impDtoRec = 0;
          foreach($detalles as $item){
            $alicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->findOneBy(array('valor' => $item->getProducto()->getIva()));
            $codigo = intval($alicuota->getCodigo());
            $dtoRec = $item->getTotalDtoRecItem() /  $comprobante->getCotizacion();
            $baseImp = $item->getBaseImponibleItem() + $dtoRec;
            $importe = $item->getTotalIvaItem() /  $comprobante->getCotizacion();
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
            }
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
          $iibb = round(($neto * $this->iibbPercent/100 ), 2);
          $impTrib = $iibb;
          $tributos = array(
            'Id' => 7,
            'BaseImp' => $neto,
            'Alic' => $this->iibbPercent,
            'Importe' => $iibb
          );
        }
        $impTotal += $impTrib;
        $fe->setTotal( round($impTotal, 2) );
        $fe->setImpTotConc(0);
        $fe->setImpNeto(round($impNeto, 2));
        $fe->setImpOpEx(0);
        $fe->setImpIva(round($impIVA, 2));
        $fe->setImpTrib(round($impTrib, 2));
        $fe->setMonId($comprobante->getMoneda()->getCodigoAfip());
        $fe->setMonCotiz($comprobante->getMoneda()->getCotizacion());
        // guardar json
        $fe->setTributos( json_encode($tributos));
        $fe->setCbtesAsoc(json_encode($cbtesAsoc));
        $fe->setPeriodoAsoc(json_encode($periodoAsoc));
        $fe->setIva(json_encode($iva));

        $afip = new Afip(array('CUIT' => $this->cuitAfip));

        $data = array(
          'CantReg'   => 1,  // Cantidad de comprobantes a registrar
          'PtoVta'   => $fe->getPuntoVenta(),  // Punto de venta
          'CbteTipo'   => $fe->getCodigoComprobante(),  // Tipo de comprobante (ver tipos disponibles)
          'Concepto'   => $fe->getConcepto(),  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
          'DocTipo'   => $fe->getDocTipo(), // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
          'DocNro'   => $fe->getDocNro(),  // Número de documento del comprador (0 consumidor final)
          'CbteFch'   => $fe->getCbteFch(), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
          'ImpTotal'   => $fe->getTotal(), // Importe total del comprobante
          'ImpTotConc'   => $fe->getImpTotConc(),   // Importe neto no gravado
          'ImpNeto'   => $fe->getImpNeto(), // Importe neto gravado
          'ImpOpEx'   => $fe->getImpOpEx(),   // Importe exento de IVA
          'ImpIVA'   => $fe->getImpIva(),  //Importe total de IVA
          'ImpTrib'   => $fe->getImpTrib(),   //Importe total de tributos
          'MonId'   => $fe->getMonId(), //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos)
          'MonCotiz'   => $fe->getMonCotiz(),     // Cotización de la moneda usada (1 para pesos argentinos)
          'Tributos' => $tributos,
          'CbtesAsoc'   => $cbtesAsoc,
          'PeriodoAsoc' => $periodoAsoc,
          'Iva'       => $iva,
        );

        // si no hay combrobante asociado
        if (empty($cbtesAsoc)) {
          unset($data['CbtesAsoc']);
        }
        if (empty($periodoAsoc)) {
          unset($data['PeriodoAsoc']);
        }
        // si no hay tributos
        if (empty($tributos)) {
          unset($data['Tributos']);
        }

        // create voucher
        $wsResult = $afip->ElectronicBilling->CreateNextVoucher($data);

        //* completar datos del registro factura electronica
        $fe->setCae($wsResult['CAE']);
        $fe->setCaeVto($wsResult['CAEFchVto']);
        $fe->setNroComprobante($wsResult['voucher_number']);

        $esCobro ? $fe->setCobro($comprobante) : $fe->setNotaDebCred($comprobante);
        $saldofe = $esCobro ? $impTotal : ($fe->getTipoComprobante()->getClase() === 'CRE') ? 0 : $impTotal;
        $fe->setSaldo(round($saldofe, 2));
        $em->persist($fe);

        //* Marcar como finalizado
        if($esCobro){
          $comprobante->setEstado('FINALIZADO');
          $comprobante->getVenta()->setEstado('FACTURADO');
          $em->persist($comprobante);
        }
        // else{
        //   $comprobante->setEstado('ACREDITADO');
        // }


        $em->flush();

        $em->getConnection()->commit();
        $response['res'] = 'OK';
        $response['msg'] = 'Factura emitida correctamente!';

      } catch (\Exception $ex) {
        $em->getConnection()->rollback();

        $msg =  array_key_exists($ex->getCode(), $this->webserviceError) ?
                  $this->webserviceError[$ex->getCode()] :
                  $ex->getMessage() ;

        $response['res'] = 'ERROR';
        $response['msg'] = $msg;
      }

      return $response;

    }
}