<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

class ParametroRepository extends EntityRepository {

    public function findByMyCriteria() {
        return $this->findByMyCriteriaDQL()->getResult();
    }

    public function findByAgrupadorDQL($agrupador) {
        $query = $this->_em->createQuery('Select p from ConfigBundle\Entity\Parametro p
        where p.agrupador_id=' . $agrupador->getId());
        return $query;
    }

    public function findByAgrupador($agrupador) {
        $query = $this->_em->createQuery('Select p from ConfigBundle\Entity\Parametro p
        where p.boleano=0 and p.activo=1 and p.agrupador_id=' . $agrupador);
        return $query;
    }

    public function findOrdenCalificacionProveedor() {
        $agrupador = $this->findOneByNombre('calificacion-proveedor');
        $query = $this->_em->createQuery("Select MAX(p.numerico) from ConfigBundle\Entity\Parametro p
        where p.agrupador_id=" . $agrupador->getId());
        return $query->getSingleScalarResult();
    }

    public function findRubros() {
        $agrupador = $this->findOneByNombre('rubro');
        $query = $this->_em->createQuery("Select r.id ,r.nombre, r.descripcion,r.activo from ConfigBundle\Entity\Parametro r
          where r.boleano=0 and r.padre_id=" . $agrupador->getId());
        return $query->getResult();
    }

    public function findSubRubros() {
        $agrupador = $this->findOneByNombre('rubro');
        $query = $this->_em->createQuery("Select r.id as padre,s.id ,r.nombre as rubro, s.nombre as codigo,
          s.descripcion as subrubro, s.numerico, s.numerico2, s.activo from ConfigBundle\Entity\Parametro r
          INNER JOIN ConfigBundle\Entity\Parametro s with r.id=s.agrupador_id
          where r.padre_id=" . $agrupador->getId());

        return $query->getResult();
    }

    public function getSubRubros() {
        $agrupador = $this->findOneByNombre('rubro');
        $qb = $this->_em->createQueryBuilder('p')
                ->select('p')
                ->from('ConfigBundle\Entity\Parametro', 'p')
                ->innerJoin('p.agrupador', 'r')
                ->where('r.padre_id=:agr')
                ->setParameter('agr', $agrupador)
                ->orderBy('p.nombre');
        return $qb;
    }

    public function findUltNro($tipo, $letra) {
        $agrupador = $this->findOneByNombre('numeracion');
        $query = $this->_em->createQuery("Select r.numerico prefijo, r.numerico2 nro from ConfigBundle\Entity\Parametro r
          where r.nombre='" . $tipo . "' and r.descripcion='" . $letra . "' and r.agrupador_id=" . $agrupador->getId());
        return $query->getOneOrNullResult();
    }

    public function setUltNro($tipo, $letra, $nro) {
        $agrupador = $this->findOneByNombre('numeracion');
        $query = $this->_em->createQuery("Update ConfigBundle\Entity\Parametro r
          set r.numerico2=" . $nro . " where r.nombre='" . $tipo . "'
              and r.descripcion='" . $letra . "' and r.agrupador_id=" . $agrupador->getId());
        return $query->execute();
    }

    /*
     * Parametros Afip
     */

    public function getIdByTipo($valor) {
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('ConfigBundle\Entity\AfipComprobante', 'c')
                ->where('c.valor LIKE :term')
                ->andWhere('c.activo=1');
        $query->setParameter('term', '%' . $valor . '%');
        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Escalas impositivas
     */        
    public function getByTipoEscala($tipo='R'){
        $consulta = ($tipo=='O') ? "e.tipo='G' OR e.tipo='H'" : "e.tipo='".$tipo."'";
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('ConfigBundle\Entity\Escalas', 'e')
                ->where('1=1')
                ->orderBy('e.id');
        $query->andWhere($consulta);
        return $query->getQuery()->getResult();
    }
}