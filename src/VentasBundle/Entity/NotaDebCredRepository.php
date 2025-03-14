<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class NotaDebCredRepository extends EntityRepository {

    public function findByCriteria($unidneg, $cliId = NULL, $desde = NULL, $hasta = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
                ->from('VentasBundle\Entity\NotaDebCred', 'p')
                ->innerJoin('p.unidadNegocio', 'u')
                ->where("u.id=" . $unidneg);
        if ($cliId) {
            $query->innerJoin('p.cliente', 'pr')
                    ->andWhere('pr.id=' . $cliId);
        }
        if ($desde) {
            $cadena = " p.fecha >= '" . UtilsController::toAnsiDate($desde) . " 00:00'";
            $query->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " p.fecha <= '" . UtilsController::toAnsiDate($hasta) . " 23:59'";
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getResult();
    }

    public function getCantidadAlicuotas($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('distinct a.id')
                ->from('VentasBundle\Entity\NotaDebCredDetalle', 'd')
                ->innerJoin('d.afipAlicuota', 'a')
                ->innerJoin('d.notaDebCred', 'n')
                ->where('n.id=:arg')
                ->setParameter('arg', $id);
        return $query->getQuery()->getResult();
    }

}