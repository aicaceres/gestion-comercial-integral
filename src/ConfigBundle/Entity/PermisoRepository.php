<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
* PermisoRepository
*/
class PermisoRepository extends EntityRepository {

    public function getPadres() {
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('ConfigBundle\Entity\Permiso', 'p')  
              ->where("p.padre IS NULL")
              ->orderBy('p.orden','DESC')  ;       
        return $query->getQuery()->getResult();  
    }
        
}
