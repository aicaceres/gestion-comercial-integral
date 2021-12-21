<?php

namespace ComprasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class FacturaRepository extends EntityRepository {

    public function findFacturasByPeriodo($desde, $hasta) {
        $query = $this->_em->createQueryBuilder();
        $query->select('f')
                ->from('ComprasBundle\Entity\Factura', 'f')
                ->where("f.estado!='ANULADO'")
                ->andWhere("f.fechaFactura>='" . UtilsController::toAnsiDate($desde) . " 00:00'")
                ->andWhere("f.fechaFactura<='" . UtilsController::toAnsiDate($hasta) . " 23:59'");
        return $query->getQuery()->getResult();
    }

    public function findNotaDCByPeriodo($desde, $hasta) {
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('ComprasBundle\Entity\NotaDebCred', 'c')
                ->where("c.estado!='ANULADO'")
                ->andWhere("c.fecha>='" . UtilsController::toAnsiDate($desde) . " 00:00'")
                ->andWhere("c.fecha<='" . UtilsController::toAnsiDate($hasta) . " 23:59'");
        return $query->getQuery()->getResult();
    }

    public function findByProveedorId($id) {
        $query = $this->getEntityManager()->createQuery("
        SELECT f.id,f.tipoFactura, f.prefijoNro, f.facturaNro, f.total,f.saldo, f.nroComprobante
        FROM ComprasBundle:Factura f
        LEFT JOIN f.proveedor p
        WHERE p.id = :id
        AND f.saldo>0
        AND f.estado != 'CANCELADO'
        ORDER BY f.fechaFactura DESC
        ")->setParameter('id', $id);

        return $query->getArrayResult();
    }

    public function findByCriteria($unidneg, $provId = NULL, $desde = NULL, $hasta = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
                ->from('ComprasBundle\Entity\Factura', 'p')
                ->innerJoin('p.unidadNegocio', 'u')
                ->where("u.id=" . $unidneg);
        if ($provId) {
            $query->innerJoin('p.proveedor', 'pr')
                    ->andWhere('pr.id=' . $provId);
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

    public function findCompradoByCriteria($unidneg, $provId, $prodId, $periodo) {
        $query = $this->_em->createQueryBuilder();
        $query->from('ComprasBundle\Entity\FacturaDetalle', 'fd')
                ->innerJoin('fd.factura', 'f')
                ->innerJoin('fd.producto', 'pr')
                ->innerJoin('f.proveedor', 'p')
                ->innerJoin('pr.unidadMedida', 'u')
                ->innerJoin('f.unidadNegocio', 'un')
                ->where('u.agrupador_id=27')
                ->andWhere('un.id=' . $unidneg)
                ->andWhere("f.fechaFactura>='" . UtilsController::toAnsiDate($periodo['desde']) . " 00:00'")
                ->andWhere("f.fechaFactura<='" . UtilsController::toAnsiDate($periodo['hasta']) . " 23:59'")
                ->andWhere("f.estado <> 'CANCELADO'");
        if ($prodId) {
            $query->andWhere('pr.id=' . $prodId);
        }
        if ($provId) {
            $query->select('pr.codigo, pr.nombre producto, u.nombre unidadMedida, '
                            . 'AVG(CASE WHEN fd.bulto=1 THEN ( fd.precio / fd.cantidadxBulto ) ELSE fd.precio END)  preciounit, '
                            . '(CASE WHEN fd.bulto=1 THEN SUM( fd.cantidad * fd.cantidadxBulto ) ELSE SUM(fd.cantidad) END) cant, '
                            . ' SUM(fd.cantidad * fd.precio) total ')
                    ->andWhere('p.id=' . $provId)
                    ->groupBy('pr.codigo');
        }
        else {
            $query->select('p.nombre proveedor, pr.codigo, pr.nombre producto, u.nombre unidadMedida, '
                            . 'AVG(CASE WHEN fd.bulto=1 THEN ( fd.precio / fd.cantidadxBulto ) ELSE fd.precio END)  preciounit, '
                            . '(CASE WHEN fd.bulto=1 THEN SUM( fd.cantidad * fd.cantidadxBulto ) ELSE SUM(fd.cantidad) END) cant, '
                            . ' SUM(fd.cantidad * fd.precio) total ')
                    ->groupBy('p.nombre, pr.codigo');
        }
        return $query->getQuery()->getResult();
    }

    public function getPrecioUltimaCompra($cod) {
        $qb = $this->_em->createQueryBuilder();
        $qb2 = $this->_em->createQueryBuilder();

        $qb2->select('MAX(f.fechaFactura) ultcompra')
                ->from('ComprasBundle\Entity\Factura', 'f')
                ->innerJoin('f.detalles', 'd')
                ->innerJoin('d.producto', 'p')
                ->where('p.id=' . $cod);

        $ultfecha = $qb2->getQuery()->getOneOrNullResult();

        if ($ultfecha['ultcompra']) {
            $qb->select(' (CASE WHEN fd.bulto=1 THEN MAX( fd.precio/fd.cantidadxBulto ) ELSE MAX( fd.precio ) END) precio')
                    ->from('ComprasBundle\Entity\FacturaDetalle', 'fd')
                    ->innerJoin('fd.factura', 'f')
                    ->innerJoin('fd.producto', 'p')
                    ->where('p.id=' . $cod)
                    ->andWhere($qb->expr()->eq('f.fechaFactura', "'" . $ultfecha['ultcompra'] . "'"));
            return $qb->getQuery()->getOneOrNullResult();
        }
        else {
            return 0;
        }
    }

    public function isDuplicado($unidneg, $factura) {
        $query = $this->_em->createQueryBuilder();
        $query->select('f')
                ->from('ComprasBundle\Entity\Factura', 'f')
                ->innerJoin('f.unidadNegocio', 'u')
                ->innerJoin('f.proveedor', 'p')
                ->where("f.estado !='CANCELADO' ")
                ->andWhere("u.id=" . $unidneg)
                ->andWhere("f.tipoFactura='" . $factura->getTipoFactura() . "'")
                ->andWhere("f.nroComprobante='" . $factura->getNroComprobante() . "'")
                ->andWhere('p.id=' . $factura->getProveedor()->getId());
        return $query->getQuery()->getResult();
    }

    public function getFacturaDuplicada(array $criteria) {
        $query = $this->_em->createQueryBuilder();
        $query->select('f')
                ->from('ComprasBundle\Entity\Factura', 'f')
                ->innerJoin('f.unidadNegocio', 'u')
                ->innerJoin('f.proveedor', 'p')
                ->where("f.estado !='CANCELADO' ")
                ->andWhere("u.id=" . $criteria['unidadNegocio'])
                ->andWhere("f.nroComprobante='" . $criteria['nroComprobante'] . "'")
                ->andWhere('p.id=' . $criteria['proveedor']);
        return ( count($query->getQuery()->getResult()) > 0 ) ? false : true;
    }

    public function getCantidadAlicuotas($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('distinct a.id')
                ->from('ComprasBundle\Entity\FacturaDetalle', 'd')
                ->innerJoin('d.afipAlicuota', 'a')
                ->innerJoin('d.factura', 'f')
                ->where('f.id=:arg')
                ->setParameter('arg', $id);
        return $query->getQuery()->getResult();
    }

    public function findFacturasByPeriodoUnidadNegocio($desde, $hasta, $unidneg) {
        $query = $this->_em->createQueryBuilder();
        $query->select('f')
                ->from('ComprasBundle\Entity\Factura', 'f')
                ->innerJoin('f.unidadNegocio', 'u')
                ->where("f.estado!='ANULADO'")
                ->andWhere('u.id=' . $unidneg)
                ->andWhere("f.fechaFactura>='" . $desde . " 00:00'")
                ->andWhere("f.fechaFactura<='" . $hasta . " 23:59'");
        return $query->getQuery()->getResult();
    }

    public function findComprobantesByPeriodoUnidadNegocio($desde, $hasta, $unidneg) {
        $facturas = $this->_em->createQueryBuilder('f')
                ->select(" 'FAC-' tipocomp, f.id, f.fechaFactura fecha, f.tipoFactura tipo  ")
                ->from("ComprasBundle\Entity\Factura", 'f')
                ->innerJoin('f.unidadNegocio', 'u')
                ->where("f.estado!='ANULADO'")
                ->andWhere('u.id=' . $unidneg)
                ->andWhere("f.fechaFactura>='" . $desde . " 00:00'")
                ->andWhere("f.fechaFactura<='" . $hasta . " 23:59'");
        $notacred = $this->_em->createQueryBuilder('c')
                ->select(" CASE WHEN n.signo='+' THEN 'DEB-' ELSE 'CRE-' END tipocomp,n.id, n.fecha, n.tipoNota tipo")
                ->from('ComprasBundle\Entity\NotaDebCred', 'n')
                ->innerJoin('n.unidadNegocio', 'u')
                ->where("n.estado!='ANULADO'")
                ->andWhere('u.id=' . $unidneg)
                ->andWhere("n.fecha>='" . $desde . " 00:00'")
                ->andWhere("n.fecha<='" . $hasta . " 23:59'");

        $datos = array_merge($facturas->getQuery()->getArrayResult(), $notacred->getQuery()->getArrayResult());
        $ord = usort($datos, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Y-m-d'));
            $value2 = strtotime($a2['fecha']->format('Y-m-d'));
            return $value1 - $value2;
        });
        return $datos;
    }

    public function detalleCentroCostoByCriteria($ccId, $periodo) {
        $query = $this->_em->createQueryBuilder();
        $desde = UtilsController::toAnsiDate($periodo['desde']) . " 00:00'";
        $hasta = UtilsController::toAnsiDate($periodo['hasta']) . " 23:59'";

        $query->select('fd.id, f.id facturaid, f.tipoFactura, f.nroComprobante, f.fechaFactura, fd.precio*fd.cantidad subtotal,'
                        . 'p.codigo codigoproducto, p.nombre nombreproducto,cc.nombre centrocosto, ccd.costo')
                ->from('ComprasBundle\Entity\FacturaDetalle', 'fd')
                ->innerJoin('fd.factura', 'f')
                ->innerJoin('fd.producto', 'p')
                ->leftJoin('fd.centroCostoDetalle', 'ccd')
                ->leftJoin('ccd.centroCosto', 'cc')
                ->where("f.estado <> 'CANCELADO'")
                ->andWhere("f.fechaFactura>='" . UtilsController::toAnsiDate($periodo['desde']) . " 00:00'")
                ->andWhere("f.fechaFactura<='" . UtilsController::toAnsiDate($periodo['hasta']) . " 23:59'");
        if ($ccId) {
            if ($ccId == 'S') {
                $query->andWhere('ccd IS NULL');
            }
            else {
                $query->andWhere('cc.id=' . $ccId);
            }
        }

        return $query->getQuery()->getResult();

        $query->select('ccd')
                ->from('ComprasBundle\Entity\CentroCostoDetalle', 'ccd')
                ->innerJoin('ccd.facturaDetalle', 'fd')
                ->innerJoin('fd.factura', 'f')
                ->innerJoin('fd.producto', 'pr')
                ->innerJoin('ccd.centroCosto', 'cc')
                ->where("f.estado <> 'CANCELADO'")
                ->andWhere("f.fechaFactura>='" . UtilsController::toAnsiDate($periodo['desde']) . " 00:00'")
                ->andWhere("f.fechaFactura<='" . UtilsController::toAnsiDate($periodo['hasta']) . " 23:59'");
        if ($ccId) {
            $query->andWhere('cc.id=' . $ccId);
        }

        return $query->getQuery()->getResult();
    }

    public function reporteEconomico($tipo, $periodo) {
        $query = $this->_em->createQueryBuilder();
        if ($tipo == 'PROV') {
            $select = "trim(p.nombre) proveedor,trim(cc.nombre) centroCosto, DATE_FORMAT(f.fechaFactura, '%m-%Y') mesanio,sum(ccd.costo) subtotal ";
            $group = 'proveedor,centroCosto,mesanio';
            $order = 'proveedor,centroCosto,mesanio';
        }
        else {
            $select = "trim(cc.nombre) centroCosto,trim(p.nombre) proveedor, DATE_FORMAT(f.fechaFactura, '%m-%Y') mesanio,sum(ccd.costo) subtotal ";
            $group = 'centroCosto,proveedor,mesanio';
            $order = 'centroCosto,proveedor,mesanio';
        }
        $query->select($select)
                ->from('ComprasBundle\Entity\CentroCostoDetalle', 'ccd')
                ->innerJoin('ccd.facturaDetalle', 'fd')
                ->innerJoin('fd.factura', 'f')
                ->leftJoin('f.proveedor', 'p')
                ->leftJoin('ccd.centroCosto', 'cc')
                ->where("f.estado <> 'CANCELADO'")
                ->andWhere("f.fechaFactura>='" . UtilsController::toAnsiDate($periodo['desde']) . " 00:00'")
                ->andWhere("f.fechaFactura<='" . UtilsController::toAnsiDate($periodo['hasta']) . " 23:59'")
                ->groupBy($group)
                ->orderBy($order)
        ;

        return $query->getQuery()->getResult();
    }

}