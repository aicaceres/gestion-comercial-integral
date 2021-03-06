<?php

namespace ConfigBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UsuarioRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsuarioRepository extends EntityRepository
{
    public function getDepositosByUnidadNegocio(){
      $agrupador = $this->findOneByNombre('rubro');
      $qb = $this->_em->createQueryBuilder('p')
              ->select('p')
              ->from('ConfigBundle\Entity\Parametro','p')
              ->innerJoin('p.agrupador', 'r')
              ->where('r.padre_id=:agr')
              ->setParameter('agr', $agrupador);
      return $qb;
  }
    
}
