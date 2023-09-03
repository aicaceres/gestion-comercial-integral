<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class FacturaRepository extends EntityRepository {

    public function findSaldoComprobantes($ids) {
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

    /** AFIP VENTAS */
    public function findByFeventasPeriodoUnidadNegocio($desde, $hasta, $unidneg) {
        $query = $this->_em->createQueryBuilder('fe')
            ->select('fe')
            ->from('VentasBundle:FacturaElectronica', 'fe')
            ->innerJoin('fe.unidadNegocio', 'u')
            ->where('u.id=' . $unidneg)
            ->andWhere("fe.cae != ''")
            ->andWhere("fe.cbteFch >= '" . $desde . "'")
            ->andWhere("fe.cbteFch <= '" . $hasta . "'")
            ->orderBy('fe.cbteFch');
        return $query->getQuery()->getResult();
    }

    public function findComprobantesByPeriodoUnidadNegocio($desde, $hasta, $unidneg) {
        $cobros = $this->_em->createQueryBuilder('c')
            ->select('t.valor tipocomp,c.id,c.fechaCobro fecha, f.id fe ')
            ->from('VentasBundle\Entity\Cobro', 'c')
            ->innerJoin('c.facturaElectronica', 'f')
            ->innerJoin('f.tipoComprobante', 't')
            ->innerJoin('c.unidadNegocio', 'u')
            ->where('u.id=' . $unidneg)
            ->andWhere("c.estado!='ANULADO'")
            ->andWhere("c.fechaCobro>='" . $desde . " 00:00'")
            ->andWhere("c.fechaCobro<='" . $hasta . " 23:59'");
        $notas = $this->_em->createQueryBuilder('n')
            ->select('t.valor tipocomp,n.id,n.fecha, f.id fe ')
            ->from('VentasBundle\Entity\NotaDebCred', 'n')
            ->innerJoin('n.notaElectronica', 'f')
            ->innerJoin('f.tipoComprobante', 't')
            ->innerJoin('n.unidadNegocio', 'u')
            ->where('u.id=' . $unidneg)
            ->andWhere("n.estado!='ANULADO'")
            ->andWhere("n.fecha>='" . $desde . " 00:00'")
            ->andWhere("n.fecha<='" . $hasta . " 23:59'");

        $datos = array_merge($cobros->getQuery()->getArrayResult(), $notas->getQuery()->getArrayResult());
        $ord = usort($datos, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Y-m-d'));
            $value2 = strtotime($a2['fecha']->format('Y-m-d'));
            return $value1 - $value2;
        });
        return $datos;
    }

    public function getCantidadAlicuotas($tipo, $id) {
        $query = $this->_em->createQueryBuilder();
        $repo = ($tipo == 'FAC') ? 'VentasBundle:VentaDetalle' : 'VentasBundle:NotaDebCredDetalle';
        $cab = ($tipo == 'FAC') ? 'd.venta' : 'd.notaDebCred';
        $query->select('distinct d.alicuota')
            ->from($repo, 'd')
            ->innerJoin($cab, 'c')
            ->where('c.id=:arg')
            ->setParameter('arg', $id);
        return $query->getQuery()->getArrayResult();
    }

    /**
     * IMPRESORA FISCAL
     */
    public function findUltimotReporte($cajaId) {
        $query = $this->_em->createQueryBuilder();
        $query->select('if.fechaHasta')
            ->from('VentasBundle:ImpresoraFiscal', 'if')
            ->innerJoin('if.caja', 'c')
            ->where('c.id=' . $cajaId)
            ->andWhere("if.comando='cmObtenerPrimerBloqueReporteElectronico'")
            ->andWhere("if.fechaDesde IS NOT NULL")
            ->andWhere("if.fechaHasta IS NOT NULL")
            ->andWhere("if.error != 1")
            ->orderBy('if.fechaHasta', 'DESC')
            ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

}