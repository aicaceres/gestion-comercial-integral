<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

/**
 * CajaAperturaRepository
 */
class CajaAperturaRepository extends EntityRepository {

    /**  Buscar aperturas de caja por criterio de filtro    */
    public function findByCriteria($unidneg, $desde = NULL, $hasta = NULL, $cajaId = NULL) {
        $query = $this->_em->createQueryBuilder();
        $query->select('a')
            ->from('VentasBundle\Entity\CajaApertura', 'a')
            ->innerJoin('a.caja', 'c')
            ->innerJoin('c.unidadNegocio', 'un')
            ->where("un.id=" . $unidneg);
        if ($desde) {
            $cadena = " a.fechaApertura >= '" . UtilsController::toAnsiDate($desde) . " 00:00'";
            $query->andWhere($cadena);
        }
        if ($hasta) {
            $cadena = " a.fechaApertura <= '" . UtilsController::toAnsiDate($hasta) . " 23:59'";
            $query->andWhere($cadena);
        }
        if ($cajaId) {
            $cadena = " c.id = " . $cajaId;
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getResult();
    }

    public function findAperturaSinCerrar($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('a')
            ->from('VentasBundle\Entity\CajaApertura', 'a')
            ->innerJoin('a.caja', 'c')
            ->where("c.id=" . $id)
            ->andWhere('a.fechaCierre is null');
        return $query->getQuery()->getOneOrNullResult();
    }

    public function getMovimientosById($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('cd')
            ->from('VentasBundle\Entity\CobroDetalle', 'cd')
            ->innerJoin('cd.cajaApertura', 'a')
            ->where("a.id=" . $id)
            ->orderBy("cd.pagoProveedor,cd.pagoCliente,cd.cobro,cd.notaDebCred");

        return $query->getQuery()->getResult();
    }

}