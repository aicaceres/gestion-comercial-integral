<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

/**
 * ClienteRepository
 */
class ClienteRepository extends EntityRepository {

    public function findAllSaldos() {
        $clientes = $this->_em->createQueryBuilder('c')
                ->select('c')
                ->from('VentasBundle\Entity\Cliente', 'c')
                ->innerJoin('c.vendedor', 'v')
                ->where('c.activo=1');
        return $clientes->getQuery()->getResult();
    }

    public function findAllOrderByLocalidad() {
        $clientes = $this->_em->createQueryBuilder('c')
                ->select('c')
                ->from('VentasBundle\Entity\Cliente', 'c')
                ->leftJoin('c.localidad', 'l')
                ->orderBy('l.name');
        return $clientes->getQuery()->getResult();
    }

    public function getDetalleCtaCte($id, $desde = null, $hasta = null) {
        $cli = $this->find($id);
        $facturas = $this->_em->createQueryBuilder('f')
                ->select('f.id,1 tipo,f.fechaFactura fecha, 0 comprobante,f.created fechaemi, 0 concepto , f.total importe')
                ->from('VentasBundle\Entity\Factura', 'f')
                ->innerJoin('f.cliente', 'p')
                ->where('p.id=:agr')
                ->andWhere("f.estado!='ANULADO'")
                ->setParameter('agr', $cli);

        $notadeb = $this->_em->createQueryBuilder('c')
                ->select('c.id,2 tipo,c.fecha, 0 comprobante, c.created fechaemi, 0 concepto , c.total importe')
                ->from('VentasBundle\Entity\NotaDebCred', 'c')
                ->innerJoin('c.cliente', 'p')
                ->where('p.id=:agr')
                ->andWhere("c.estado!='ANULADO'")
                ->setParameter('agr', $cli);

        $pagos = $this->_em->createQueryBuilder('f')
                ->select('g.id,3 tipo,g.fecha, 0 comprobante ,g.created fechaemi, 0 concepto, 0 importe')
                ->from('VentasBundle\Entity\PagoCliente', 'g')
                ->innerJoin('g.cliente', 'p')
                ->where('p.id=:agr')
                ->setParameter('agr', $cli);

        if ($desde) {
            $cadfactura = " f.fechaFactura >= '" . UtilsController::toAnsiDate($desde) . "'";
            $facturas->andWhere($cadfactura);
            $cadnotacred = " c.fecha >= '" . UtilsController::toAnsiDate($desde) . "'";
            $notadeb->andWhere($cadnotacred);
            $cadpagos = " g.fecha >= '" . UtilsController::toAnsiDate($desde) . "'";
            $pagos->andWhere($cadpagos);
        }
        if ($hasta) {
            $cadfactura = " f.fechaFactura <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $facturas->andWhere($cadfactura);
            $cadnotacred = " c.fecha <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $notadeb->andWhere($cadnotacred);
            $cadpagos = " g.fecha <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $pagos->andWhere($cadpagos);
        }
        $datos = array_merge($facturas->getQuery()->getArrayResult(), $pagos->getQuery()->getArrayResult());
        $datos = array_merge($datos, $notadeb->getQuery()->getArrayResult());

        /* if($desde){
          // calcular hasta fecha desde
          $inicial = new \DateTime($desde);
          // facturas
          $facturas = $this->_em->createQueryBuilder('f')
          ->select('SUM(f.total) importe')
          ->from('VentasBundle\Entity\Factura', 'f')
          ->innerJoin('f.cliente', 'p')
          ->where('p.id=:agr')
          ->andWhere(" f.fechaFactura <= '".UtilsController::toAnsiDate($desde)."'")
          ->setParameter('agr', $cli);
          //notas de Debito
          $notadeb = $this->_em->createQueryBuilder('c')
          ->select(' SUM(c.total) importe')
          ->from('VentasBundle\Entity\NotaDebCred', 'c')
          ->innerJoin('c.cliente', 'p')
          ->where('p.id=:agr')
          ->andWhere('c.estado=:agr2')
          ->andWhere("c.signo= '+'")
          ->andWhere(" c.fecha <= '". UtilsController::toAnsiDate($desde)."'")
          ->setParameter('agr', $cli)
          ->setParameter('agr2', 'ACREDITADO');
          //notas de Credito
          $notacred = $this->_em->createQueryBuilder('c')
          ->select(' SUM(c.total) importe')
          ->from('VentasBundle\Entity\NotaDebCred', 'c')
          ->innerJoin('c.cliente', 'p')
          ->where('p.id=:agr')
          ->andWhere('c.estado=:agr2')
          ->andWhere("c.signo= '-'")
          ->andWhere(" c.fecha <= '". UtilsController::toAnsiDate($desde)."'")
          ->setParameter('agr', $cli)
          ->setParameter('agr2', 'ACREDITADO');
          $pagos = $this->_em->createQueryBuilder('f')
          ->select('g')
          ->from('VentasBundle\Entity\PagoCliente', 'g')
          ->innerJoin('g.cliente', 'p')
          ->where('p.id=:agr')
          ->andWhere(" g.fecha <= '". UtilsController::toAnsiDate($desde)."'")
          ->setParameter('agr', $cli);
          $totpagos=0;
          if( $pagos ){
          foreach($pagos as $pago){
          $totpagos = $totpagos + $pago->getTotal();
          }
          }
          $saldoInicial = $cli->getSaldoInicial() + $facturas->getQuery()->getSingleScalarResult()
          + $notadeb->getQuery()->getSingleScalarResult() - $notacred->getQuery()->getSingleScalarResult() - $totpagos ;
          }else{
          // tomar valor del proveedor unicamente
          $saldoInicial = $cli->getSaldoInicial();
          $inicial = new \DateTime('2000-01-01');
          }
         */

        if (!$desde) {
            $saldoInicial = $cli->getSaldoInicial();
            $inicial = new \DateTime('2000-01-01');
            $saldoInicial = ( is_null($saldoInicial) ? 0 : $saldoInicial );
            array_push($datos, array('id' => 0, 'tipo' => 0, 'fecha' => $inicial, 'comprobante' => '0',
                'concepto' => 'Saldo Inicial', 'importe' => $saldoInicial));
        }
        return $datos;

        /* if($saldoInicial) array_push($datos,array('id'=>0,'tipo'=>0,'fecha'=>$inicial,'comprobante'=>0,
          'concepto'=>'Saldo Inicial','importe'=>$saldoInicial));

          $ord = usort($datos, function($a1, $a2) {
          $value1 = strtotime($a1['fecha']->format('Y-m-d H:i'));
          $value2 = strtotime($a2['fecha']->format('Y-m-d H:i'));
          return $value1 - $value2;
          });*

          return $datos;/
          /*
          $facturas = $this->_em->createQueryBuilder('f')
          ->select('f.id,1 tipo,f.fechaFactura fecha, f.nroFactura comprobante, 0 concepto , f.total importe')
          ->from('CM\VentasBundle\Entity\Factura', 'f')
          ->innerJoin('f.cliente', 'p')
          ->where('p.id=:agr')
          ->setParameter('agr', $cli);
          if( defined($desde) ){
          echo $desde;
          $facturas->andWhere('f.fechaFactura>=:desde')
          ->setParameter('desde', $fechaDesde);
          }
          if(defined($hasta)){
          echo $hasta;
          $facturas->andWhere('f.fechaFactura<=:hasta')
          ->setParameter('hasta', $fechaHasta);
          }

          $pagos = $this->_em->createQueryBuilder('f')
          ->select('g.id,2 tipo,g.fecha, g.nroComprobante comprobante , g.concepto, g.importe')
          ->from('CM\VentasBundle\Entity\PagoCliente', 'g')
          ->innerJoin('g.cliente', 'p')
          ->where('p.id=:agr')
          ->setParameter('agr', $cli);
          if(defined($desde)){
          $pagos->andWhere('g.fecha>=:desde')
          ->setParameter('desde', $fechaDesde);
          }
          if(defined($hasta)){
          $pagos->andWhere('g.fecha<=:hasta')
          ->setParameter('hasta', $fechaHasta);
          }
          $datos = array_merge($facturas->getQuery()->getArrayResult(), $pagos->getQuery()->getArrayResult());

          $ord = usort($datos, function($a1, $a2) {
          $value1 = strtotime($a1['fecha']->format('Y-m-d'));
          $value2 = strtotime($a2['fecha']->format('Y-m-d'));
          return $value1 - $value2;
          });

          return $datos; */
    }

    public function getFacturasImpagas($id) {
        $cli = $this->find($id);
        $facturas = $this->_em->createQueryBuilder('f')
                ->select("f.id, 'FAC' tipo, f.fechaFactura fecha, f.total, f.saldo, f.nroFactura nroComprobante")
                ->from('VentasBundle\Entity\Factura', 'f')
                ->innerJoin('f.cliente', 'p')
                ->where('f.saldo>0')
                ->andWhere('p.id=:agr')
                ->setParameter('agr', $cli)
                ->orderBy('f.fechaFactura');
        $notacred = $this->_em->createQueryBuilder('c')
                ->select("c.id,'DEB' tipo,c.fecha, c.total, c.saldo, c.nroNotaDebCred nroComprobante")
                ->from('VentasBundle\Entity\NotaDebCred', 'c')
                ->innerJoin('c.cliente', 'p')
                ->where('p.id=:agr')
                ->andWhere('c.saldo>0')
                ->andWhere("c.signo='+'")
                ->setParameter('agr', $cli)
                ->orderBy('c.fecha');
        $datos = array_merge($facturas->getQuery()->getArrayResult(), $notacred->getQuery()->getArrayResult());
        $ord = usort($datos, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Y-m-d'));
            $value2 = strtotime($a2['fecha']->format('Y-m-d'));
            return $value1 - $value2;
        });

        return $datos;
    }

    public function getFacturasImpagasxx($id) {
        $cli = $this->find($id);
        $facturas = $this->_em->createQueryBuilder('f')
                ->select('f.id,f.tipoFactura, f.nroFactura, f.total, f.saldo ')
                ->from('VentasBundle\Entity\Factura', 'f')
                ->innerJoin('f.cliente', 'p')
                ->where('f.saldo>0')
                ->andWhere("f.estado!='CANCELADO'")
                ->andWhere("f.estado!='ANULADO'")
                ->andWhere('p.id=:agr')
                ->setParameter('agr', $cli)
                ->orderBy('f.fechaFactura');
        return $facturas->getQuery()->getResult();
    }

    public function findPagosByCriteria($cliId = NULL, $desde = NULL, $hasta = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
                ->from('VentasBundle\Entity\PagoCliente', 'p')
                ->where("1=1");
        if ($cliId) {
            $query->innerJoin('p.cliente', 'pr')
                    ->andWhere('pr.id=' . $cliId);
        }
        if ($desde) {
            $cadena = " p.fecha >= '" . UtilsController::toAnsiDate($desde) . "'";
            $query->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " p.fecha <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getResult();
    }

    public function checkExiste($txt, $id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('1')
                ->from('VentasBundle\Entity\Cliente', 'p')
                ->where(" p.cuit = '" . $txt . "'")
                ->andWhere("p.id != " . $id);

        return $query->getQuery()->getScalarResult();
    }

// PARA LISTA DE CLIENTES POPUP
    public function listcount()
    {
        $query = $this->_em->createQueryBuilder();
        $query->select("count(e.id)")
                ->from('VentasBundle\Entity\Cliente', 'e')
                ->where('e.activo=1');
        return $query->getQuery()->getSingleScalarResult();
    }
    public function getListDTData($start, $length, $orders, $search, $columns, $otherConditions)
    {
        // Create Main Query
        $query = $this->_em->createQueryBuilder();
        $query->select("e")
                ->from('VentasBundle\Entity\Cliente', 'e');
         
        // Create Count Query
        $countQuery = $this->_em->createQueryBuilder();
        $countQuery->select("count(e.id)")
                ->from('VentasBundle\Entity\Cliente', 'e');          
        
        // Other conditions than the ones sent by the Ajax call ?        
        if ($otherConditions === null)
        {
            // No
            // However, add a "always true" condition to keep an uniform treatment in all cases
            $query->where("e.activo=1");
            $countQuery->where("e.activo=1");
        }
        else
        {
            // Add condition
            $query->where($otherConditions);
            $countQuery->where($otherConditions);
        }
 
        if( $search['value'] ){
            $searchItem = trim($search['value']);
            $searchQuery =  ' e.nombre LIKE \'%'.$searchItem.'%\' OR e.cuit LIKE \'%'.$searchItem.'%\' '; 
            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }
                        
        // Limit
        $query->setFirstResult($start)->setMaxResults($length);
        
        // Order
        foreach ($orders as $key => $order)
        {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '')
            {
                $orderColumn = null;
            
                switch($order['name'])
                {
                    case 'nombre':
                    {
                        $orderColumn = 'e.nombre';
                        break;
                    }
                    case 'cuit':
                    {
                        $orderColumn = 'e.cuit';
                        break;
                    }                    
                }
        
                if ($orderColumn !== null)
                {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }
        
        // Execute       
        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();
        
        return array(
            "results" 		=> $results,
            "countResult"	=> $countResult
        );
    }   


/**
* para administracion de productos
 */
    public function indexCount($provId = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select("count(c.id)")
                ->from('VentasBundle\Entity\Cliente', 'c');        
        return $query->getQuery()->getSingleScalarResult();
    }

    public function getIndexDTData($start, $length, $orders, $search, $columns, $otherConditions) {
        // Create Main Query
        $query = $this->_em->createQueryBuilder();
        $query->select("c")
                ->from('VentasBundle\Entity\Cliente', 'c');

        // Create Count Query
        $countQuery = $this->_em->createQueryBuilder();
        $countQuery->select("count(c.id)")
                ->from('VentasBundle\Entity\Cliente', 'c');

        // Create inner joins
        $query->innerJoin('c.localidad', 'l');

        $countQuery->innerJoin('c.localidad', 'l');

        // Other conditions than the ones sent by the Ajax call ?
/*        if ($otherConditions === null) {
            // No
            // However, add a "always true" condition to keep an uniform treatment in all cases
            $query->where("1=1");
            $countQuery->where("1=1");
        }
        else {
            // Add condition
            $query->where($otherConditions);
            $countQuery->where($otherConditions);
        }*/        

        if ($search['value']) {
            $searchItem = trim($search['value']);
            $searchQuery = ' c.nombre LIKE \'%' . $searchItem . '%\' OR c.cuit LIKE \'%' . $searchItem . '%\' ' .
                    ' OR c.direccion LIKE \'%' . $searchItem . '%\'  OR  c.telefono LIKE \'%' . $searchItem . '%\' '. ' OR l.name LIKE \'%' . $searchItem . '%\'' ;
            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'nombre': {
                            $orderColumn = 'c.nombre';
                            break;
                        }
                    case 'cuit': {
                            $orderColumn = 'c.cuit';
                            break;
                        }
                    case 'direccion': {
                            $orderColumn = 'c.direccion';
                            break;
                        }
                    case 'localidad': {
                            $orderColumn = 'l.name';
                            break;
                        }                                     
                    case 'telefono': {
                            $orderColumn = 'c.telefono';
                            break;
                        }                                     
                    case 'activo': {
                            $orderColumn = 'c.activo';
                            break;
                        }                                     
                }

                if ($orderColumn !== null) {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }

        // Execute
        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return array(
            "results" => $results,
            "countResult" => $countResult
        );
    }

    public function getClientesForExportXls($search=null){
        $query = $this->_em->createQueryBuilder();
        $query->select("c")
                ->from('VentasBundle\Entity\Cliente', 'c')
                ->leftJoin('c.localidad', 'l')                
                ->orderBy('c.nombre')    ;        
        if ($search) {
            $searchItem = trim($search);
            $searchQuery = ' c.nombre LIKE \'%' . $searchItem . '%\' OR c.cuit LIKE \'%' . $searchItem . '%\' ' .
                    ' OR c.direccion LIKE \'%' . $searchItem . '%\'  OR  c.telefono LIKE \'%' . $searchItem . '%\' '. ' OR l.name LIKE \'%' . $searchItem . '%\'' ;            
            $query->andWhere($searchQuery);
        }
        return $query->getQuery()->getResult();
    }

}