<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class PresupuestoRepository extends EntityRepository {


    public function findByCriteria($unidneg, $cliId = NULL, $desde = NULL, $hasta = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
                ->from('VentasBundle\Entity\Presupuesto', 'p')
                ->innerJoin('p.unidadNegocio', 'u')
                ->where("u.id=" . $unidneg);
        if ($cliId) {
            $query->innerJoin('p.cliente', 'pr')
                    ->andWhere('pr.id=' . $cliId);
        }
        if ($desde) {
            $cadena = " p.fechaPresupuesto >= '" . UtilsController::toAnsiDate($desde) . " 00:00'";
            $query->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " p.fechaPresupuesto <= '" . UtilsController::toAnsiDate($hasta) . " 23:59'";
            $query->andWhere($cadena);
        }
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



    public function findComprobantesByPeriodoUnidadNegocio($desde, $hasta, $unidneg) {
        $facturas = $this->_em->createQueryBuilder('f')
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
                ->andWhere("n.fecha<='" . $hasta . " 23:59'");

        $datos = array_merge($facturas->getQuery()->getArrayResult(), $notacred->getQuery()->getArrayResult());
        $ord = usort($datos, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Y-m-d'));
            $value2 = strtotime($a2['fecha']->format('Y-m-d'));
            return $value1 - $value2;
        });
        return $datos;
    }

    public function getCantidadAlicuotas($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('distinct a.id')
                ->from('VentasBundle\Entity\FacturaDetalle', 'd')
                ->innerJoin('d.afipAlicuota', 'a')
                ->innerJoin('d.factura', 'f')
                ->where('f.id=:arg')
                ->setParameter('arg', $id);
        return $query->getQuery()->getResult();
    }

}