<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class FacturaRepository extends EntityRepository {

    public function findSaldoComprobantes($ids){
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(f.saldo)')
                ->from('VentasBundle\Entity\FacturaElectronica', 'f')
                ->andWhere('f.id IN (:ids)')
                ->setParameter('ids', $ids);
        return $query->getQuery()->getSingleScalarResult();
    }



/**   -----  */

    public function findFacturasByPeriodo($desde, $hasta) {
        $query = $this->_em->createQueryBuilder();
        $query->select('f')
                ->from('VentasBundle\Entity\Factura', 'f')
                ->where("f.estado!='ANULADO'")
                ->andWhere("f.fechaFactura>='" . UtilsController::toAnsiDate($desde) . " 00:00'")
                ->andWhere("f.fechaFactura<='" . UtilsController::toAnsiDate($hasta) . " 23:59'");
        return $query->getQuery()->getResult();
    }

    public function findNotaDCByPeriodo($desde, $hasta) {
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('VentasBundle\Entity\NotaDebCred', 'c')
                ->where("c.estado!='ANULADO'")
                ->andWhere("c.fecha>='" . UtilsController::toAnsiDate($desde) . " 00:00'")
                ->andWhere("c.fecha<='" . UtilsController::toAnsiDate($hasta) . " 23:59'");
        return $query->getQuery()->getResult();
    }

    public function findByClienteId($id) {
        $query = $this->getEntityManager()->createQuery("
        SELECT f.id,f.tipoFactura, f.nroFactura, f.total
        FROM VentasBundle:Factura f
        LEFT JOIN f.cliente p
        WHERE p.id = :id
        ORDER BY f.fechaFactura DESC
        ")->setParameter('id', $id);

        return $query->getArrayResult();
    }

    public function findByCriteria($unidneg, $cliId = NULL, $desde = NULL, $hasta = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
                ->from('VentasBundle\Entity\Factura', 'p')
                ->innerJoin('p.unidadNegocio', 'u')
                ->where("u.id=" . $unidneg);
        if ($cliId) {
            $query->innerJoin('p.cliente', 'pr')
                    ->andWhere('pr.id=' . $cliId);
        }
        if ($desde) {
            $cadena = " p.fechaFactura >= '" . UtilsController::toAnsiDate($desde) . "'";
            $query->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " p.fechaFactura <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getResult();
    }

    public function findComprobantesByPeriodoUnidadNegocio($desde, $hasta, $unidneg) {
        $cobros = $this->_em->createQueryBuilder('c')
                ->select('t.valor tipocomp,c.id,c.fechaCobro fecha ')
                ->from('VentasBundle\Entity\Cobro', 'c')
                ->innerJoin('c.facturaElectronica','f')
                ->innerJoin('f.tipoComprobante','t')
                ->innerJoin('c.unidadNegocio','u')
                ->where('u.id=' . $unidneg)
                ->andWhere("c.estado!='ANULADO'")
                ->andWhere("c.fechaCobro>='" . $desde . " 00:00'")
                ->andWhere("c.fechaCobro<='" . $hasta . " 23:59'");
        $notas = $this->_em->createQueryBuilder('n')
                ->select('t.valor tipocomp,n.id,n.fecha ')
                ->from('VentasBundle\Entity\NotaDebCred', 'n')
                ->innerJoin('n.notaElectronica','f')
                ->innerJoin('f.tipoComprobante','t')
                ->innerJoin('n.unidadNegocio','u')
                ->where('u.id=' . $unidneg)
                ->andWhere("n.estado!='ANULADO'")
                ->andWhere("n.fecha>='" . $desde . " 00:00'")
                ->andWhere("n.fecha<='" . $hasta . " 23:59'");

        /*$facturas = $this->_em->createQueryBuilder('f')
                ->select(" 'FAC-' tipocomp, f.id, f.fechaFactura fecha, f.tipoFactura tipo  ")
                ->from("VentasBundle\Entity\Factura", 'f')
                ->innerJoin('f.unidadNegocio', 'u')
                ->where("f.estado!='ANULADO'")
                ->andWhere('u.id=' . $unidneg)
                ->andWhere("f.fechaFactura>='" . $desde . " 00:00'")
                ->andWhere("f.fechaFactura<='" . $hasta . " 23:59'");
        $notacred = $this->_em->createQueryBuilder('c')
                ->select(" CASE WHEN n.signo='+' THEN 'DEB-' ELSE 'CRE-' END tipocomp,n.id, n.fecha, n.tipoNota tipo")
                ->from('VentasBundle\Entity\NotaDebCred', 'n')
                ->innerJoin('n.unidadNegocio', 'u')
                ->where("n.estado!='ANULADO'")
                ->andWhere('u.id=' . $unidneg)
                ->andWhere("n.fecha>='" . $desde . " 00:00'")
                ->andWhere("n.fecha<='" . $hasta . " 23:59'");*/

        $datos = array_merge($cobros->getQuery()->getArrayResult(), $notas->getQuery()->getArrayResult());
        $ord = usort($datos, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Y-m-d'));
            $value2 = strtotime($a2['fecha']->format('Y-m-d'));
            return $value1 - $value2;
        });
        return $datos;
    }

    public function getCantidadAlicuotas($tipo,$id) {
        $query = $this->_em->createQueryBuilder();
        $repo = ($tipo=='FAC') ? 'VentasBundle:VentaDetalle' : 'VentasBundle:NotaDebCredDetalle';
        $cab = ($tipo=='FAC') ? 'd.venta' : 'd.notaDebCred';
        $query->select('distinct d.alicuota')
                ->from($repo, 'd')
                ->innerJoin($cab, 'c')
                ->where('c.id=:arg')
                ->setParameter('arg', $id);
        return $query->getQuery()->getArrayResult();


    }

}