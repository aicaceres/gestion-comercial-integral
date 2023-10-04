<?php

namespace ComprasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class NotaDebCredRepository extends EntityRepository {

    public function findByCriteria($unidneg, $provId = NULL, $desde = NULL, $hasta = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
            ->from('ComprasBundle\Entity\NotaDebCred', 'p')
            ->innerJoin('p.unidadNegocio', 'u')
            ->where("u.id=" . $unidneg);
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

    public function isDuplicado($entity) {
        $query = $this->_em->createQueryBuilder();
        $query->select('f')
            ->from('ComprasBundle\Entity\NotaDebCred', 'f')
            ->innerJoin('f.unidadNegocio', 'u')
            ->innerJoin('f.proveedor', 'p')
            ->where("f.estado !='CANCELADO' ")
            ->andWhere("u.id=" . $entity->getUnidadNegocio()->getId())
            ->andWhere("f.nroComprobante='" . $entity->getNroComprobante() . "'")
            ->andWhere('p.id=' . $entity->getProveedor()->getId())
            ->andWhere("f.signo='" . $entity->getSigno() . "'");
        return $query->getQuery()->getResult();
    }

    public function findNotasByPeriodoUnidadNegocio($desde, $hasta, $unidneg) {
        $query = $this->_em->createQueryBuilder();
        $query->select('n')
            ->from('ComprasBundle\Entity\NotaDebCred', 'n')
            ->innerJoin('n.unidadNegocio', 'u')
            ->where("n.estado!='ANULADO'")
            ->andWhere('u.id=' . $unidneg)
            ->andWhere("n.fecha>='" . $desde . " 00:00'")
            ->andWhere("n.fecha<='" . $hasta . " 23:59'");
        return $query->getQuery()->getResult();
    }

    public function getCantidadAlicuotas($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('distinct a.id')
            ->from('ComprasBundle\Entity\NotaDebCredDetalle', 'd')
            ->innerJoin('d.afipAlicuota', 'a')
            ->innerJoin('d.notaDebCred', 'n')
            ->innerJoin('n.afipComprobante', 'ac')
            ->where('n.id=:arg')
            ->andWhere("ac.valor NOT LIKE '%-C'")
            ->andWhere("ac.valor NOT LIKE '%-B'")
            ->setParameter('arg', $id);
        return $query->getQuery()->getResult();
    }

}