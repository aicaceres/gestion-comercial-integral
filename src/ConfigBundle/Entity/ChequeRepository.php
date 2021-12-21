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
                ->where('c.usado=0')
                ->andWhere('c.devuelto=0');
        return $query->getQuery()->getResult();        
    }
}
