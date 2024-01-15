<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
* ChequeRepository
*/
class ChequeRepository extends EntityRepository {

    public function getCheque($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('ConfigBundle\Entity\Cheque', 'c')
                ->where('c.id='.$id);
        return $query->getQuery()->getArrayResult();
    }

    public function getChequesParaPago(){
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('ConfigBundle\Entity\Cheque', 'c')
                ->innerJoin('c.banco', 'b')
                ->where('c.usado=0')
                ->andWhere("b.nombre NOT LIKE '%RETENCION%' ")
                ->andWhere('c.devuelto=0');
        return $query->getQuery()->getResult();
    }

    public function findNoRetenciones($tipo = ''){
      $query = $this->_em->createQueryBuilder();
      $query->select('c')
              ->from('ConfigBundle\Entity\Cheque', 'c')
              ->innerJoin('c.banco', 'b')
              ->where("b.nombre NOT LIKE '%RETENCION%' ")
              ->orderBy('c.fecha','DESC');
      if($tipo){
        $query->andWhere('c.tipo = :tipo')
              ->setParameter('tipo', $tipo);
      }
      return $query->getQuery()->getResult();
    }
}
