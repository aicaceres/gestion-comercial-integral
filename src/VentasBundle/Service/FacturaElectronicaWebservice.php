<?php

namespace VentasBundle\Service;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\FacturaElectronica;
use VentasBundle\Afip\src\Afip;
use Endroid\QrCode\QrCode;

class FacturaElectronicaWebservice {
    private $em;
    private $session;
    private $ptovtaWsFactura;
    private $ptovtaIfuTicket;
    private $iibbPercent;
    private $cuitAfip;
    private $afipOptionsProd;
    private $afipOptionsTest;
    private $webserviceError = array(
        10013 => "Para comprobantes clase A y M el tipo de documento debe ser CUIT",
        10015 => "Para facturas B de este monto, debe ingresar tipo y nro de documento del cliente.",
        10016 => "Se está intentando autorizar un comprobante con fecha anterior al último informado.",
        10208 => 'La fecha hasta del periodo asociado tiene que ser anterior o igual a la Fecha de Emision del comprobante.',
        10024 => "Si ImpTrib es mayor a 0 el objeto Tributos y Tributo son obligatorios.",
        10040 => "Tipo de comprobante asociado inválido. Debe ser de tipo B.",
        10047 => "El campo ImpIVA (Importe de IVA) para comprobantes tipo C debe ser igual a cero (0)",
        10048 => "El campo  'Importe Total' ImpTotal, debe ser igual  a la  suma de ImpNeto + ImpTrib. Donde ImpNeto es igual al Sub Total",
        10071 => "Para comprobantes tipo C el objeto IVA no debe informarse.",
    );

    public function __construct(EntityManager $em, Session $session, $iibbPercent, $cuitAfip) {
        $this->em = $em;
        $this->session = $session;
        $caja = $em->getRepository('ConfigBundle:Caja')->find($session->get('caja')['id']);
        $this->ptovtaWsFactura = $caja->getPtoVtaWs();
        $this->ptovtaIfuTicket = $caja->getPtoVtaIfu();
        $this->iibbPercent = $iibbPercent;
        $this->cuitAfip = $cuitAfip;
        $this->afipOptionsProd = array('CUIT' => $cuitAfip, 'production' => true, 'cert' => 'cert.crt', 'key' => 'decr.key');
        $this->afipOptionsTest = array('CUIT' => '27208373124', 'production' => false, 'cert' => 'cert', 'key' => 'key');
    }

    public function procesarComprobante($id, $entity, $modo, $nroTicket = null) {
        $response['res'] = 'ERROR';
        $response['msg'] = '';
        $em = $this->em;
        try {
            $em->getConnection()->beginTransaction();
            $comprobante = $em->getRepository('VentasBundle:' . $entity)->find($id);
            $tipo = $entity === 'Cobro' ? 'FAC' : 'NDC';
            // preparar los datos de FE
            $dataFe = $this->setDataFacturaElectronica($tipo, $comprobante, $modo);
            if ($modo == 'WS') {
                // enviar WS
                $result = $this->enviarWs($dataFe);
                // al recibir respuesta actualizar datos de cae
                $dataFe['cae'] = $result['cae'];
                $dataFe['caeVto'] = $result['caeVto'];
                $dataFe['nroComprobante'] = $result['nroComprobante'];
            }
            elseif ($modo == 'TF') {
                $dataFe['nroComprobante'] = $nroTicket;
            }

            // guardar FE
            $this->guardarFacturaElectronica($dataFe);

            //* Marcar como finalizado
            if ($tipo == 'FAC') {
                $comprobante->setEstado('FINALIZADO');
                $comprobante->getVenta()->setEstado('FACTURADO');
            }
            else {
                $comprobante->setEstado('ACREDITADO');
            }
            $em->persist($comprobante);
            $em->flush();

            $em->getConnection()->commit();
            $response['res'] = 'OK';
            $response['msg'] = 'Comprobante emitido correctamente!';
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $msg = array_key_exists($ex->getCode(), $this->webserviceError) ?
                $this->webserviceError[$ex->getCode()] :
                $ex->getMessage();

            $response['res'] = 'ERROR';
            $response['msg'] = $ex->getMessage();
        }

        return $response;
    }

    public function procesarComprobanteMipymes($id, $entity, $rechazado = 'NO') {
        $response['res'] = 'ERROR';
        $em = $this->em;
        $opcionales = [];
        try {
            $em->getConnection()->beginTransaction();
            $comprobante = $em->getRepository('VentasBundle:' . $entity)->find($id);
            $tipo = $entity === 'Cobro' ? 'FAC' : 'NDC';

            // preparar los datos de FE
            $dataFe = $this->setDataFacturaElectronica($tipo, $comprobante, 'WS');

            // cambiar tipo de comprobante a MiPymes
            $cbteTipo = $em->getRepository('ConfigBundle:AfipComprobante')->find($dataFe['afipComprobante']);
            $split = split('-', $cbteTipo->getValor());

            $cbte = $split[0] == 'FAC' ? 'FCE' : $split[0];
            $cbteTipoNuevo = $em->getRepository('ConfigBundle:AfipComprobante')->findOneBy(array('valor' => $cbte . '-' . $split[1]));
            $dataFe['afipComprobante'] = $cbteTipoNuevo->getId();
            $hoy = new \DateTime();

            if ($tipo === 'NDC') {
              $dataFe['periodoAsoc'] = [];
                $opcionales[] = array(
                    'Id' => 22,
                    'Valor' => $rechazado
                );
                if($cbte === 'NCE' || $cbte === 'NDE') {
                  $dataFe['cbtesAsoc'][0]['Cuit'] = $this->cuitAfip == '27208373124' ? $this->afipOptionsTest['CUIT'] : $this->afipOptionsProd['CUIT'];
                  $dataFe['cbtesAsoc'][0]['CbteFch'] = $comprobante->getComprobanteAsociado()->getCbteFch();
                }
            }
            else {
               $dataFe['fchVtoPago'] = intval($comprobante->getFechaVtoPago()->format('Ymd'));
                $parametrizacion = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $this->session->get('unidneg_id')));
                if ($parametrizacion->getCbuEmisor()) {
                    $opcionales[] = array(
                        'Id' => 2101,
                        'Valor' => $parametrizacion->getCbuEmisor()
                    );
                }
                if ($parametrizacion->getAliasEmisor()) {
                    $opcionales[] = array(
                        'Id' => 2102,
                        'Valor' => $parametrizacion->getAliasEmisor()
                    );
                }
                if ($parametrizacion->getReferenciaComercial()) {
                    $opcionales[] = array(
                        'Id' => 23,
                        'Valor' => $parametrizacion->getReferenciaComercial()
                    );
                }
                if ($parametrizacion->getformPagoFE()) {
                    $opcionales[] = array(
                        'Id' => 27,
                        'Valor' => $parametrizacion->getformPagoFE()
                    );
                }
            }
            $dataFe['opcionales'] = $opcionales;

            $result = $this->enviarWs($dataFe);
            // al recibir respuesta actualizar datos de cae
            $dataFe['cae'] = $result['cae'];
            $dataFe['caeVto'] = $result['caeVto'];
            $dataFe['nroComprobante'] = $result['nroComprobante'];

            // guardar FE
            $this->guardarFacturaElectronica($dataFe);

            //* Marcar como finalizado
            if ($tipo == 'FAC') {
                $comprobante->setEstado('FINALIZADO');
                $comprobante->getVenta()->setEstado('FACTURADO');
            }
            else {
                $comprobante->setEstado('ACREDITADO');
            }
            $em->persist($comprobante);
            $em->flush();

            $em->getConnection()->commit();
            $response['res'] = 'OK';
            $response['msg'] = 'Comprobante emitido correctamente!';
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $msg = array_key_exists($ex->getCode(), $this->webserviceError) ?
                $this->webserviceError[$ex->getCode()] :
                $ex->getMessage();

            $response['res'] = 'ERROR';
            $response['msg'] = $ex->getMessage();
        }

        return $response;
    }

    public function enviarWs($fe) {
        $em = $this->em;
        $cbteTipo = $em->getRepository('ConfigBundle:AfipComprobante')->find($fe['afipComprobante']);
        $config = $this->cuitAfip == '27208373124' ? $this->afipOptionsTest : $this->afipOptionsProd;
        $afip = new Afip($config);
        $data = array(
            'CantReg' => 1, // Cantidad de comprobantes a registrar
            'PtoVta' => $fe['puntoVta'], // Punto de venta
            'CbteTipo' => $cbteTipo->getCodigo(), // Tipo de comprobante (ver tipos disponibles)
            'Concepto' => $fe['concepto'], // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
            'DocTipo' => $fe['docTipo'], // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
            'DocNro' => $fe['docNro'], // Número de documento del comprador (0 consumidor final)
            'CbteFch' => $fe['cbteFch'], // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
            'ImpTotal' => $fe['total'], // Importe total del comprobante
            'ImpTotConc' => $fe['impTotC'], // Importe neto no gravado
            'ImpNeto' => $fe['impNeto'], // Importe neto gravado
            'ImpOpEx' => $fe['impOpEx'], // Importe exento de IVA
            'ImpIVA' => $fe['impIVA'], //Importe total de IVA
            'ImpTrib' => $fe['impTrib'], //Importe total de tributos
            'MonId' => $fe['monId'], //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos)
            'MonCotiz' => $fe['monCotiz'], // Cotización de la moneda usada (1 para pesos argentinos)
            'Tributos' => $fe['tributos'],
            'CbtesAsoc' => $fe['cbtesAsoc'],
            'PeriodoAsoc' => $fe['periodoAsoc'],
            'Iva' => $fe['iva'],
            'Opcionales' => $fe['opcionales'],
            'FchVtoPago' => $fe['fchVtoPago']
        );

        // si no hay tributos
        if (empty($fe['tributos'])) {
            unset($data['Tributos']);
        }

        if (empty($fe['cbtesAsoc'])) {
            unset($data['CbtesAsoc']);
        }
        else {
            unset($data['PeriodoAsoc']);
        }
        if (empty($fe['periodoAsoc'])) {
            unset($data['PeriodoAsoc']);
        }
        // MiPymes
        if (empty($fe['opcionales'])) {
            unset($data['Opcionales']);
        }
        if (empty($fe['fchVtoPago'])) {
            unset($data['FchVtoPago']);
        }
        if ($fe['impIVA'] == 0){
             unset($data['Iva']);
        }
        // create voucher
        $wsResult = $afip->ElectronicBilling->CreateNextVoucher($data);

        // devolver datos para completar fe
        return array(
            'cae' => $wsResult['CAE'],
            'caeVto' => $wsResult['CAEFchVto'],
            'nroComprobante' => $wsResult['voucher_number']
        );
    }

    // tipo: FAC | NDC - modo: WS | TF
    public function setDataFacturaElectronica($tipo, $comprobante, $modo) {
        $em = $this->em;
        $cliente = $comprobante->getCliente();
        $catIva = ($cliente->getCategoriaIva()) ? $cliente->getCategoriaIva()->getNombre() : 'C';
        $percRentas = $cliente->getPercepcionRentas();
        $cobroId = $notaId = null;
        $cbtesAsoc = $periodoAsoc = $tributos = $iva = [];

        $hoy = new \Datetime();
        if ($tipo == 'FAC') {
            $cobroId = $comprobante->getId();
            $detalles = $comprobante->getVenta()->getDetalles();
            $letra = ($catIva == 'I' || $catIva == 'M') ? 'A' : 'B';
            $codigo = ($modo == 'WS') ? 'FAC' : 'TICK';
            $valor = $codigo . '-' . $letra;
            $afipComprobante = $em->getRepository('ConfigBundle:AfipComprobante')->findOneByValor($valor);
            $cbteFch = $comprobante->getFechaCobro() ? $comprobante->getFechaCobro() : $hoy;
        }
        elseif ($tipo == 'NDC') {
            $notaId = $comprobante->getId();
            $detalles = $comprobante->getDetalles();
            $afipComprobante = $comprobante->getTipoComprobante();
            $cbteFch = $comprobante->getFecha() ? $comprobante->getFecha() : $hoy;

            $asoc = $comprobante->getComprobanteAsociado();
            if ($asoc) {
                $cbtesAsoc[] = array(
                    'Tipo' => $asoc->getCodigoComprobante(),
                    'PtoVta' => $asoc->getPuntoVenta(),
                    'Nro' => $asoc->getNroComprobante()
                );
            }

            $periodoAsoc = array(
                'FchDesde' => intval($comprobante->getFecha()->format('Ymd')),
                'FchHasta' => intval($comprobante->getFecha()->format('Ymd')));
        }
        // punto de venta
        $ptoVta = $modo == 'WS' ? $this->ptovtaWsFactura : $this->ptovtaIfuTicket;
        // datos del cliente
        $nombreCliente = $comprobante->getNombreClienteTxt() ? $comprobante->getNombreClienteTxt() : 'CONSUMIDOR FINAL';
        $docTipo = 99;
        $docNro = 0;
        if ($comprobante->getTipoDocumentoCliente()) {
            $docTipo = $comprobante->getTipoDocumentoCliente()->getCodigo();
            $docNro = $comprobante->getNroDocumentoCliente();
        }
        elseif ($cliente->getCuit()) {
            $docTipo = 80;
            $docNro = trim($cliente->getCuit());
        }
        // IMPORTES Y CONCEPTOS DEL DETALLE
        $impTotC = $impOpEx = 0; // importe no gravado // Importe exento de IVA
        if ($detalles) {
            $impTotal = $impNeto = $impIVA = $impTrib = $impDtoRec = 0;
            foreach ($detalles as $item) {
                $alicuota = $em->getRepository('ConfigBundle:AfipAlicuota')->findOneBy(array('valor' => number_format($item->getAlicuota(), 2, '.', '')));

                $codigo = intval($alicuota->getCodigo());
                $dtoRec = $item->getTotalDtoRecItem() / $comprobante->getCotizacion();
                $baseImp = $item->getBaseImponibleItem() + $dtoRec;
                $importe = $item->getTotalIvaItem() / $comprobante->getCotizacion();
                $key = array_search($codigo, array_column($iva, 'Id'));
                // IVA
                /*  array(
                  'Id' 		=> 5, // Id del tipo de IVA (ver tipos disponibles)
                  'BaseImp' 	=> 100, // Base imponible
                  'Importe' 	=> 21 // Importe
                  ) */
                if ($importe > 0) {
                    if ($key === false) {
                        $iva[] = array(
                            'Id' => $codigo,
                            'BaseImp' => round($baseImp, 2),
                            'Importe' => round($importe, 2)
                        );
                    }
                    else {
                        $iva[$key] = array(
                            'Id' => $codigo,
                            'BaseImp' => round($iva[$key]['BaseImp'] + $baseImp, 2),
                            'Importe' => round($iva[$key]['Importe'] + $importe, 2)
                        );
                    }                  
                    $impNeto += $baseImp;
                }else{
                    $impOpEx += $baseImp;
                }
                // TOTALES
                $impDtoRec += $dtoRec;
                
                $impIVA += $importe;
                $impTotal += ($baseImp + $importe);
            }
        }

        // TRIBUTOS
        /* array(
          'Id' 		=>  99, // Id del tipo de tributo (ver tipos disponibles)
          'Desc' 		=> 'Ingresos Brutos', // (Opcional) Descripcion
          'BaseImp' 	=> 150, // Base imponible para el tributo
          'Alic' 		=> 5.2, // Alícuota
          'Importe' 	=> 7.8 // Importe del tributo
          ) */
        $impTrib = 0;
        if ($percRentas > 0) {
            $neto = round($impNeto, 2);
            $iibb = round(($neto * $percRentas / 100), 2);
            $impTrib = $iibb;
            $tributos = array(
                'Id' => 7,
                'Desc' => 'Ingresos Brutos',
                'BaseImp' => $neto,
                'Alic' => $percRentas,
                'Importe' => $iibb
            );
        }
        $impTotal += $impTrib;
        $saldofe = 0;
        if ($tipo == 'FAC') {
            $saldofe = $comprobante->getFormaPago()->getCuentaCorriente() ? $impTotal : 0;
        }
        else {
            $saldofe = ($comprobante->getTipoComprobante()->getClase() === 'CRE') ? 0 : $impTotal;
        }
        $dataFe = array(
            'unidadNegocio' => $this->session->get('unidneg_id'),
            'afipComprobante' => $afipComprobante->getId(),
            'cobroId' => $cobroId,
            'notaId' => $notaId,
            'puntoVta' => $ptoVta,
            'nroComprobante' => '',
            'cae' => '',
            'caeVto' => '',
            'total' => round($impTotal, 2),
            'saldo' => round($saldofe, 2),
            'concepto' => 1, //Concepto del Comprobante: (1)Productos
            'docTipo' => $docTipo,
            'docNro' => $docNro,
            'nombreCliente' => $nombreCliente,
            'cbteFch' => intval($cbteFch->format('Ymd')),
            'impTotC' => $impTotC,
            'impNeto' => round($impNeto, 2),
            'impOpEx' => $impOpEx,
            'impIVA' => round($impIVA, 2),
            'impTrib' => round($impTrib, 2),
            'monId' => $comprobante->getMoneda()->getCodigoAfip(),
            'monCotiz' => $comprobante->getMoneda()->getCotizacion(),
            'cbtesAsoc' => $cbtesAsoc,
            'periodoAsoc' => $periodoAsoc,
            'tributos' => $tributos,
            'iva' => $iva,
            'opcionales' => [],
            'fchVtoPago' => ''
        );
        return $dataFe;
    }

    public function guardarFacturaElectronica($dataFe) {
        $em = $this->em;
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($dataFe['unidadNegocio']);
        $tipoComprobante = $em->getRepository('ConfigBundle:AfipComprobante')->find($dataFe['afipComprobante']);
        $cobro = $nota = $cliente = null;
        if ($dataFe['cobroId']) {
            $cobro = $em->getRepository('VentasBundle:Cobro')->find($dataFe['cobroId']);
            $cliente = $cobro->getCliente();
        }
        elseif ($dataFe['notaId']) {
            $nota = $em->getRepository('VentasBundle:NotaDebCred')->find($dataFe['notaId']);
            $cliente = $nota->getCliente();
        }
        $fe = new FacturaElectronica();
        $fe->setUnidadNegocio($unidneg);
        $fe->setTipoComprobante($tipoComprobante);
        $fe->setCliente($cliente);
        $fe->setCobro($cobro);
        $fe->setNotaDebCred($nota);
        $fe->setPuntoVenta($dataFe['puntoVta']);
        $fe->setNroComprobante($dataFe['nroComprobante']);
        $fe->setCae($dataFe['cae']);
        $fe->setCaeVto($dataFe['caeVto']);
        $fe->setTotal($dataFe['total']);
        $fe->setSaldo($dataFe['saldo']);
        $fe->setConcepto($dataFe['concepto']);
        $fe->setDocTipo($dataFe['docTipo']);
        $fe->setDocNro($dataFe['docNro']);
        $fe->setNombreCliente($dataFe['nombreCliente']);
        $fe->setCbteFch($dataFe['cbteFch']);
        $fe->setImpTotConc($dataFe['impTotC']);
        $fe->setImpNeto($dataFe['impNeto']);
        $fe->setImpOpEx($dataFe['impOpEx']);
        $fe->setImpIva($dataFe['impIVA']);
        $fe->setImpTrib($dataFe['impTrib']);
        $fe->setMonId($dataFe['monId']);
        $fe->setMonCotiz($dataFe['monCotiz']);
        $fe->setTributos(json_encode($dataFe['tributos']));
        $fe->setCbtesAsoc(json_encode($dataFe['cbtesAsoc']));
        $fe->setPeriodoAsoc(json_encode($dataFe['periodoAsoc']));
        $fe->setIva(json_encode($dataFe['iva']));
        $fe->setOpcionales(json_encode($dataFe['opcionales']));
        $fe->setFchVtoPago($dataFe['fchVtoPago']);

        $em->persist($fe);
        $em->flush();
    }

    /// TICKET FISCAL
    public function setDatosClienteTicket($comprobante) {
        // Tipos de documento
        // tdCUIT = 0;
        // tdDNI = 1;
        // tdPasaporte = 2;
        // tdCedula = 3;
        // otro = 9
        // Responsabilidad ante IVA
        // riResponsableInscripto = 0;
        // riMonotributo = 1;
        // riExento = 3;
        // riConsumidorFinal = 4;

        $nombre = $comprobante->getNombreClienteTxt();
        $docTipo = $docNro = '1';
        $direccion = 'MOSTRADOR';
        $cliente = $comprobante->getCliente();
        if ($cliente->getCuit()) {
            $docTipo = 0;
            $docNro = trim($cliente->getCuit());
            $direccion = $cliente->getDomicilioCompleto();
        }
        elseif ($comprobante->getTipoDocumentoCliente()) {
            $docTipo = $comprobante->getTipoDocumentoCliente()->getNumerico() ? intVal($comprobante->getTipoDocumentoCliente()->getNumerico()) : 9;
            $docNro = $docTipo ? $comprobante->getNroDocumentoCliente() : '';
        }
        $catIva = $cliente->getCategoriaIva()->getNumerico2() ? intVal($cliente->getCategoriaIva()->getNumerico2()) : '';
        $dataCliente = array($nombre, $docTipo, $docNro, $catIva, $direccion);
        return $dataCliente;
    }

    public function getTiposComprobantesValidos($modo) {
//      30525882086  2147483647
        $afipOptionsProd = array('CUIT' => '30525882086', 'production' => true, 'cert' => 'cert.crt', 'key' => 'decr.key');
        $afipOptionsTest = array('CUIT' => '27208373124', 'production' => false, 'cert' => 'cert', 'key' => 'key');
        $config = $modo == 'PRODUCCION' ? $afipOptionsProd : $afipOptionsTest;
        try {
            $afip = new Afip($config);
            $wsResult = $afip->ElectronicBilling->GetOptionsTypes();

            return array('MODO' => $modo, 'STATUS' => $wsResult);
        }
        catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

}