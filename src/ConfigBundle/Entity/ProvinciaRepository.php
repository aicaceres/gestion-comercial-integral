<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
* ProvinciaRepository
*/
class ProvinciaRepository extends EntityRepository {

    public function findByPaisId($pais_id) {
        $query = $this->getEntityManager()->createQuery("
        SELECT provincia
        FROM ConfigBundle:Provincia provincia
        LEFT JOIN provincia.pais pais
        WHERE pais.id = :pais_id
        ")->setParameter('pais_id', $pais_id);

        return $query->getArrayResult();
    }
    
}
