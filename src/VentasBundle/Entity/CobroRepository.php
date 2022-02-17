<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class CobroRepository extends EntityRepository {

    /**  Buscar ventas por criterio de filtro    */
    public function findByCriteria($unidneg, $desde = NULL, $hasta = NULL, $userId = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('v')
                ->from('VentasBundle\Entity\Venta', 'v')                          
                ->innerJoin('v.unidadNegocio', 'un')                
                ->where("un.id=" . $unidneg);              
        if ($desde) {
            $cadena = " v.fechaVenta >= '" . UtilsController::toAnsiDate($desde) . "'";
            $query->andWhere($cadena);
        }   
        if ($hasta) {
            $cadena = " v.fechaVenta <= '" . UtilsController::toAnsiDate($hasta) . "'";
            $query->andWhere($cadena);
        }
        if($userId){
            $query->innerJoin( 'v.createdBy','u' );
            $cadena = " u.id = " . $userId;
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getResult();
    }

    /** Usuarios para filtro en listado de ventas */
    public function getUsers() {
        $query = $this->_em->createQueryBuilder();
        $query->select('u.id,u.nombre,u.username')
                ->from('VentasBundle\Entity\Venta', 'v')                          
                ->innerJoin('v.createdBy', 'u')
                ->distinct();        
        return $query->getQuery()->getArrayResult();
    }



}