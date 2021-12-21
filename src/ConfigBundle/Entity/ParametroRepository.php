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
                ->setParameter('agr', $agrupador);
        return $qb;
    }

    /*  public function findUltNroFactura($tipo){
      $agrupador = $this->findOneByNombre('numeracion');
      $query = $this->_em->createQuery("Select r.numerico prefijo, r.numerico2 numero from CM\AdminBundle\Entity\Parametro r
      where r.nombre='FACTURA' and r.descripcion='".$tipo."' and r.agrupador_id=".$agrupador->getId());
      return $query->getOneOrNullResult();
      }
      public function setUltNroFactura($tipo,$nro){
      $agrupador = $this->findOneByNombre('numeracion');
      $query = $this->_em->createQuery("Update CM\AdminBundle\Entity\Parametro r
      set r.numerico2=".$nro." where r.nombre='FACTURA' and r.descripcion='".$tipo."' and r.agrupador_id=".$agrupador->getId());
      return $query->execute();
      } */

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

    public function repetido($ptovta, $nro) {
        $query = $this->_em->createQueryBuilder();
        $query->select('b')
                ->from('ConfigBundle\Entity\AfipImportacionBuffets', 'b')
                ->where('b.puntoVenta = ' . "'" . $ptovta . "'")
                ->andWhere('b.numeroComprobante=' . "'" . $nro . "'");
        return $query->getQuery()->getOneOrNullResult();
    }

}