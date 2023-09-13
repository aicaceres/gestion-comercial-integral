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
            $cadena = " c.fechaCobro >= '" . UtilsController::toAnsiDate($desde) . " 00:00'";
            $query->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " c.fechaCobro <= '" . UtilsController::toAnsiDate($hasta) . " 23:59'";
            $query->andWhere($cadena);
        }
        if ($userId) {
            $query->innerJoin('c.createdBy', 'u');
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

    public function filterByCliente($cliente, $term) {
        $query = $this->_em->createQueryBuilder();
        $query->select("f")
            ->from('VentasBundle:FacturaElectronica', 'f')
            ->innerJoin('f.cliente', 'cl')
            ->innerJoin('f.tipoComprobante', 't')
            ->where('cl.id= :cli')
            ->andWhere("t.valor NOT LIKE 'CRE-%'")
            //->andWhere('f.saldo>0')
            ->orderBy('f.created', 'DESC')
            ->setParameter('cli', $cliente);
        if ($term) {
            $query->andWhere("f.nroComprobante LIKE '%" . $term . "%'");
        }
        return $query->getQuery()->getResult();
    }

}