<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class FacturaRepository extends EntityRepository {

    // public function findSaldoComprobantes($ids) {
    //     $query = $this->_em->createQueryBuilder();
    //     $query->select('f')
    //         ->from('VentasBundle\Entity\FacturaElectronica', 'f')
    //         ->andWhere('f.id IN (:ids)')
    //         ->setParameter('ids', $ids);
    //     $result = $query->getQuery()->getArrayResult();
    //     foreach ($result as $value) {
    //       var_dump($value);
    //     }
    //     die;
    //     return $saldo;
    // }

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
//            ->andWhere("fe.cae != ''")
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

    public function findByPeriodoUnidadNegocio($desde, $hasta, $unidneg) {
        $fechaDesde = str_replace('-', '', $desde);
        $fechaHasta = str_replace('-', '', $hasta);
        $query = $this->_em->createQueryBuilder('fe')
            ->select('fe')
            ->from('VentasBundle:FacturaElectronica', 'fe')
            ->innerJoin('fe.unidadNegocio', 'u')
            ->where('u.id=' . $unidneg)
            ->andWhere("fe.cbteFch >='" . $fechaDesde . "'")
            ->andWhere("fe.cbteFch <='" . $fechaHasta . "'")
            ->orderBy('fe.cbteFch', 'ASC');
        return $query->getQuery()->getResult();
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

    /**
     * Encontrar los pagos con retencion de rentas dentro del periodo indicado
     */
    public function findPercepcionesRentas($desde, $hasta) {
        $query = $this->_em->createQueryBuilder();
        $query->select("p")
            ->from('VentasBundle:FacturaElectronica', 'p')
            ->andWhere(" p.cbteFch >= '" . $desde . "'")
            ->andWhere(" p.cbteFch <= '" . $hasta . "'")
            ->andWhere("p.impTrib > 0")
            ->orderBy('p.cbteFch');

        return $query->getQuery()->getResult();
    }

    /**
     * Pagos de cliente con retenciones de rentas
     */
    public function findRetencionesRentas($desde, $hasta) {
        $query = $this->_em->createQueryBuilder();
        $query->select("p.prefijoNro, p.pagoNro,d.importe,c.nombre nombreCliente ")
            ->from('VentasBundle:PagoCliente', 'p')
            ->innerJoin('p.cliente', 'c')
            ->innerJoin('p.cobroDetalles', 'd')
            ->innerJoin('d.chequeRecibido', 'ch')
            ->innerJoin('ch.banco', 'b')
            ->where("d.tipoPago = 'CHEQUE' ")
            ->andWhere("b.nombre = 'RETENCION DGR'")
            ->andWhere(" p.fecha >= '" . $desde . "'")
            ->andWhere(" p.fecha <= '" . $hasta . "'")
            ->orderBy('p.fecha');
        return $query->getQuery()->getResult();
    }

    public function findEstadisticaVentasByUnidadNegocio($unidneg, $desde = null, $hasta = null, $prodId = null, $order='importe'){
        $fechaDesde = $desde ? str_replace('-', '', $desde) : null;
        $fechaHasta = $hasta ? str_replace('-', '', $hasta) : null;
        $prodId = $prodId ?: null;

        $qb = $this->_em->createQueryBuilder();
        $qb->select('DISTINCT fe, c, v, vd, p')
            ->from('VentasBundle:FacturaElectronica', 'fe')
            ->innerJoin('fe.unidadNegocio', 'u')
            ->innerJoin('fe.cobro', 'c')
            ->innerJoin('c.venta', 'v')
            ->innerJoin('v.detalles','vd')
            ->leftJoin('vd.producto', 'p')
            ->where('u.id = :unidneg')
            ->orderBy('fe.cbteFch', 'ASC')
            ->setParameter('unidneg', $unidneg);

        if ($fechaDesde) {
            $qb->andWhere('fe.cbteFch >= :fechaDesde')
               ->setParameter('fechaDesde', $fechaDesde);
        }

        if ($fechaHasta) {
            $qb->andWhere('fe.cbteFch <= :fechaHasta')
               ->setParameter('fechaHasta', $fechaHasta);
        }

        if ($prodId) {
            $qb->andWhere('p.id = :prodId')
               ->setParameter('prodId', $prodId);
        }

        $facturas = $qb->getQuery()->getResult();

        $ranking = [];
        foreach ($facturas as $factura) {
            $cobro = $factura->getCobro();
            if (!$cobro) {
                continue;
            }
            $venta = $cobro->getVenta();
            if (!$venta) {
                continue;
            }
            foreach ($venta->getDetalles() as $detalle) {
                $producto = $detalle->getProducto();
                if (
                    ($prodId && (!$producto || (int) $producto->getId() !== (int) $prodId)) ||
                    ($producto && $producto->getComodin())
                ) {
                    continue;
                }

                $key = $producto ? 'prod_' . $producto->getId() : 'detalle_' . $detalle->getId();
                if (!isset($ranking[$key])) {
                    $ranking[$key] = [
                        'producto' => $producto ? $producto->getCodigoNombre() : $detalle->getNombreProducto(),
                        'cantidad' => 0,
                        'importe' => 0,
                    ];
                }

                $ranking[$key]['cantidad'] += (float) $detalle->getCantidad();
                $ranking[$key]['importe'] += (float) $detalle->getTotalItem();
            }
        }

        usort($ranking, function ($a, $b) use($order){
            if ($a[$order] === $b[$order]) {
                return 0;
            }
            return ($a[$order] < $b[$order]) ? 1 : -1;
        });

        return array_values($ranking);
    }

}