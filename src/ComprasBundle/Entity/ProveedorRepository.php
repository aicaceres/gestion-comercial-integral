<?php

namespace ComprasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

/**
 * ProveedorRepository
 */
class ProveedorRepository extends EntityRepository {

    public function getDetalleCtaCte($id, $desde = NULL, $hasta = NULL) {
        $prov = $this->find($id);
        $facturas = $this->_em->createQueryBuilder('f')
                ->select('f.id,1 tipo,f.fechaFactura fecha, 0 comprobante, 0 concepto , f.total importe')
                ->from('ComprasBundle\Entity\Factura', 'f')
                ->innerJoin('f.proveedor', 'p')
                ->where('p.id=:agr')
                ->andWhere("f.estado!='CANCELADO'")
                ->setParameter('agr', $prov);

        $notacred = $this->_em->createQueryBuilder('c')
                ->select('c.id,2 tipo,c.fecha, 0 comprobante, 0 concepto , c.total importe')
                ->from('ComprasBundle\Entity\NotaDebCred', 'c')
                ->innerJoin('c.proveedor', 'p')
                ->where('p.id=:agr')
                //->andWhere('c.estado=:agr2')
                ->setParameter('agr', $prov);
        //->setParameter('agr2', 'ACREDITADO')

        $pagos = $this->_em->createQueryBuilder('f')
                ->select('g.id,3 tipo,g.fecha, 0 comprobante , 0 concepto, 0 importe')
                ->from('ComprasBundle\Entity\PagoProveedor', 'g')
                ->innerJoin('g.proveedor', 'p')
                ->where('p.id=:agr')
                ->setParameter('agr', $prov);
        if ($desde) {
            $cadfactura = " f.fechaFactura >= '" . UtilsController::toAnsiDate($desde) . "'";
            $facturas->andWhere($cadfactura);
            $cadnotacred = " c.fecha >= '" . UtilsController::toAnsiDate($desde) . "'";
            $notacred->andWhere($cadnotacred);
            $cadpagos = " g.fecha >= '" . UtilsController::toAnsiDate($desde) . "'";
            $pagos->andWhere($cadpagos);
        }
        if ($hasta) {
            $cadfactura = " f.fechaFactura <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $facturas->andWhere($cadfactura);
            $cadnotacred = " c.fecha <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $notacred->andWhere($cadnotacred);
            $cadpagos = " g.fecha <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $pagos->andWhere($cadpagos);
        }

        $datos = array_merge($facturas->getQuery()->getArrayResult(), $pagos->getQuery()->getArrayResult());
        $datos = array_merge($datos, $notacred->getQuery()->getArrayResult());

        // calcular saldo Inicial
        /* if($desde){
          // calcular hasta fecha desde
          $inicial = new \DateTime($desde);
          // facturas
          $facturas = $this->_em->createQueryBuilder('f')
          ->select('SUM(f.total) importe')
          ->from('ComprasBundle\Entity\Factura', 'f')
          ->innerJoin('f.proveedor', 'p')
          ->where('p.id=:agr')
          ->andWhere(" f.fechaFactura <= '".UtilsController::toAnsiDate($desde)."'")
          ->setParameter('agr', $prov);
          //notas de Debito
          $notadeb = $this->_em->createQueryBuilder('c')
          ->select(' SUM(c.total) importe')
          ->from('ComprasBundle\Entity\NotaDebCred', 'c')
          ->innerJoin('c.proveedor', 'p')
          ->where('p.id=:agr')
          ->andWhere('c.estado=:agr2')
          ->andWhere("c.signo= '+'")
          ->andWhere(" c.fecha <= '". UtilsController::toAnsiDate($desde)."'")
          ->setParameter('agr', $prov)
          ->setParameter('agr2', 'ACREDITADO');
          //notas de Credito
          $notacred = $this->_em->createQueryBuilder('c')
          ->select(' SUM(c.total) importe')
          ->from('ComprasBundle\Entity\NotaDebCred', 'c')
          ->innerJoin('c.proveedor', 'p')
          ->where('p.id=:agr')
          ->andWhere('c.estado=:agr2')
          ->andWhere("c.signo= '-'")
          ->andWhere(" c.fecha <= '". UtilsController::toAnsiDate($desde)."'")
          ->setParameter('agr', $prov)
          ->setParameter('agr2', 'ACREDITADO');
          $pagos = $this->_em->createQueryBuilder('f')
          ->select('g')
          ->from('ComprasBundle\Entity\PagoProveedor', 'g')
          ->innerJoin('g.proveedor', 'p')
          ->where('p.id=:agr')
          ->andWhere(" g.fecha <= '". UtilsController::toAnsiDate($desde)."'")
          ->setParameter('agr', $prov);
          $totpagos=0;
          if( $pagos ){
          foreach($pagos as $pago){
          $totpagos = $totpagos + $pago->getTotal();
          }
          }
          $saldoInicial = $prov->getSaldoInicial() + $facturas->getQuery()->getSingleScalarResult()
          + $notadeb->getQuery()->getSingleScalarResult() - $notacred->getQuery()->getSingleScalarResult() - $totpagos ;
          }else{
          // tomar valor del proveedor unicamente
          $saldoInicial = $prov->getSaldoInicial();
          $inicial = new \DateTime('2000-01-01');
          } */
        if (!$desde) {
            $saldoInicial = $prov->getSaldoInicial();
            $inicial = new \DateTime('2000-01-01');


            $saldoInicial = ( is_null($saldoInicial) ? 0 : $saldoInicial );
            array_push($datos, array('id' => 0, 'tipo' => 0, 'fecha' => $inicial, 'comprobante' => '0',
                'concepto' => 'Saldo Inicial', 'importe' => $saldoInicial));
        }
        return $datos;
    }

    public function getFacturasImpagas($id) {
        $prov = $this->find($id);
        $facturas = $this->_em->createQueryBuilder('f')
                ->select("f.id, 'FAC' tipo, f.fechaFactura fecha, f.total, f.saldo, f.nroComprobante ")
                ->from('ComprasBundle\Entity\Factura', 'f')
                ->innerJoin('f.proveedor', 'p')
                ->where('f.saldo>0')
                ->andWhere('p.id=:agr')
                ->andWhere("f.estado!='CANCELADO'")
                ->setParameter('agr', $prov)
                ->orderBy('f.fechaFactura');
        $notacred = $this->_em->createQueryBuilder('c')
                ->select("c.id,'DEB' tipo,c.fecha, c.total, c.saldo, c.nroComprobante")
                ->from('ComprasBundle\Entity\NotaDebCred', 'c')
                ->innerJoin('c.proveedor', 'p')
                ->where('p.id=:agr')
                ->andWhere('c.saldo>0')
                ->andWhere("c.signo='+'")
                ->andWhere("c.estado!='CANCELADO'")
                ->setParameter('agr', $prov)
                ->orderBy('c.fecha');
        $datos = array_merge($facturas->getQuery()->getArrayResult(), $notacred->getQuery()->getArrayResult());
        /* $ord = usort($datos, function($a1, $a2) {
          $value1 = strtotime($a1['fecha']->format('Y-m-d'));
          $value2 = strtotime($a2['fecha']->format('Y-m-d'));
          return $value1 - $value2;
          }); */
        $ord = usort($datos, function($a1, $a2) {
            $value1 = $a1['nroComprobante'];
            $value2 = $a2['nroComprobante'];
            return $value1 > $value2;
        });
        return $datos;
    }

    public function findPagosByCriteria($provId = NULL, $desde = NULL, $hasta = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
                ->from('ComprasBundle\Entity\PagoProveedor', 'p')
                ->where("1=1");
        if ($provId) {
            $query->innerJoin('p.proveedor', 'pr')
                    ->andWhere('pr.id=' . $provId);
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
                ->from('ComprasBundle\Entity\Proveedor', 'p')
                ->where(" p.cuit = '" . $txt . "'")
                ->andWhere("p.id != " . $id);

        return $query->getQuery()->getScalarResult();
    }

    public function getPagosByFactura($provId, $factura) {
        $query = $this->_em->createQueryBuilder();
        $fact = "'%FAC-" . $factura . "%'";
        $query->select('p')
                ->from('ComprasBundle\Entity\PagoProveedor', 'p')
                ->innerJoin('p.proveedor', 'pr')
                ->where('pr.id=' . $provId)
                ->andWhere('p.concepto like ' . $fact);

        return $query->getQuery()->getArrayResult();
    }

    public function getFacturasCompraxFecha($id, $desde, $hasta, $rubro) {
        $facturas = $this->_em->createQueryBuilder();
        $facturas->select("sum(f.total) total")
                ->from('ComprasBundle\Entity\Factura', 'f')
                ->innerJoin('f.proveedor', 'p')
                ->where('p.id=:agr')
                ->andWhere("f.estado!='CANCELADO'")
                ->andWhere("f.estado!='ANULADO'")
                ->setParameter('agr', $id);
        if ($desde) {
            $cadena = " f.fechaFactura >= '" . UtilsController::toAnsiDate($desde) . "'";
            $facturas->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " f.fechaFactura <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $facturas->andWhere($cadena);
        }
        if($rubro){
            $facturas->innerJoin('p.rubroCompras','r')
                     ->andWhere('r.id = '.$rubro);
        }
        return $facturas->getQuery()->getSingleScalarResult();
    }

    public function getNotasCompraxFecha($id, $desde, $hasta, $signo = '-', $rubro) {
        $facturas = $this->_em->createQueryBuilder();
        $facturas->select("sum(f.total) total")
                ->from('ComprasBundle\Entity\NotaDebCred', 'f')
                ->innerJoin('f.proveedor', 'p')
                ->where('p.id=:agr')
                ->andWhere('f.signo=' . "'" . $signo . "'")
                //->andWhere("f.estado!='ACREDITADO'")
                ->setParameter('agr', $id);
        if ($desde) {
            $cadena = " f.fecha >= '" . UtilsController::toAnsiDate($desde) . "'";
            $facturas->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " f.fecha <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $facturas->andWhere($cadena);
        }
        if($rubro){
            $facturas->innerJoin('p.rubroCompras','r')
                     ->andWhere('r.id = '.$rubro);
        }
        return $facturas->getQuery()->getSingleScalarResult();
    }

    public function getPagosxFecha($id, $desde, $hasta, $rubro) {
        $facturas = $this->_em->createQueryBuilder();
        $facturas->select("f")
                ->from('ComprasBundle\Entity\PagoProveedor', 'f')
                ->innerJoin('f.proveedor', 'p')
                ->where('p.id=:agr')
                ->setParameter('agr', $id);
        if ($desde) {
            $cadena = " f.fecha >= '" . UtilsController::toAnsiDate($desde) . "'";
            $facturas->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " f.fecha <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $facturas->andWhere($cadena);
        }
        if($rubro){
            $facturas->innerJoin('p.rubroCompras','r')
                     ->andWhere('r.id = '.$rubro);
        }
        return $facturas->getQuery()->getResult();
    }

    public function filterByTerm($key) {
        $query = $this->_em->createQueryBuilder();
            $query->select("c.id,c.nombre text")
                    ->from('ComprasBundle:Proveedor', 'c')
                    ->where('c.nombre LIKE :key')
                    ->orderBy('c.nombre')
                    ->setParameter('key', '%' . $key . '%')
                    ->setMaxResults(5);
        return $query->getQuery()->getArrayResult();
    }
}