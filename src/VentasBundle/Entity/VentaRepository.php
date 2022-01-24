<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class VentaRepository extends EntityRepository {

    public function findByCriteria($unidneg, $ptoId = NULL, $desde = NULL, $hasta = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('v')
                ->from('VentasBundle\Entity\Venta', 'v')
                ->innerJoin('v.puntoVenta', 'p')              
                ->innerJoin('p.unidadNegocio', 'u')                
                ->where("u.id=" . $unidneg);
        if ($ptoId) {
            $query->andWhere('p.id=' . $ptoId);
        }        
        if ($desde) {
            $cadena = " v.fechaVenta >= '" . UtilsController::toAnsiDate($desde) . "'";
            $query->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " v.fechaVenta <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getResult();
    }

}