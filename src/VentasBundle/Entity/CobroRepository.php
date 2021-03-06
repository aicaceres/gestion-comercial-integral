<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class CobroRepository extends EntityRepository {

    /**  Buscar ventas por criterio de filtro    */
    public function findByCriteria($unidneg, $desde = NULL, $hasta = NULL, $userId = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('VentasBundle\Entity\Cobro', 'c')
                ->innerJoin('c.unidadNegocio', 'un')
                ->where("un.id=" . $unidneg);
        if ($desde) {
            $cadena = " c.fechaCobro >= '" . UtilsController::toAnsiDate($desde) . "'";
            $query->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " c.fechaCobro <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $query->andWhere($cadena);
        }
        if($userId){
            $query->innerJoin( 'c.createdBy','u' );
            $cadena = " u.id = " . $userId;
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getResult();
    }

    /** Usuarios para filtro en listado de ventas */
    public function getUsers() {
        $query = $this->_em->createQueryBuilder();
        $query->select('u.id,u.nombre,u.username')
                ->from('VentasBundle\Entity\Cobro', 'c')
                ->innerJoin('c.createdBy', 'u')
                ->distinct();
        return $query->getQuery()->getArrayResult();
    }

    public function filterByCliente($cliente) {
        $query = $this->_em->createQueryBuilder();
            $query->select("f")
                    ->from('VentasBundle:FacturaElectronica', 'f')
                    ->innerJoin('f.cobro','c')
                    ->innerJoin('c.cliente','cl')
                    ->where('cl.id= :cli')
                    ->orderBy('c.fechaCobro')
                    ->setParameter('cli', $cliente );
        return $query->getQuery()->getResult();
    }

}