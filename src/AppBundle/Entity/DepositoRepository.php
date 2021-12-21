<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;

class DepositoRepository extends EntityRepository
{
    public function setPorDefectoFalse($unidneg){
        $qb = $this->_em->createQueryBuilder('d')
              ->update('AppBundle\Entity\Deposito','d')
              ->set('d.pordefecto', 0)
              ->where('d.unidadNegocio='.$unidneg);      
        return $qb->getQuery()->execute();
    }
 
    
}