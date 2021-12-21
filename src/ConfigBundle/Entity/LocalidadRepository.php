<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Entity\Pais;

/**
 * LocalidadRepository
 */
class LocalidadRepository extends EntityRepository
{
    public function findByTerm($term)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT localidad.id as id, localidad.name as label
            FROM ConfigBundle:Localidad localidad
            WHERE localidad.name LIKE :term
        ")->setParameter('term', '%' . $term . '%');

        return $query->getArrayResult();
    }

    public function findByProvinciaId($provincia_id)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT localidad
            FROM ConfigBundle:Localidad localidad
            LEFT JOIN localidad.provincia provincia
            WHERE provincia.id = :provincia_id
        ")->setParameter('provincia_id', $provincia_id);

        return $query->getArrayResult();
    }

    public function findRandomLocalidadesByPais(Pais $pais, $limit = null)
    {
        $queryLocalidadIds = $this->getEntityManager()->createQuery("
            SELECT localidad.id
            FROM ConfigBundle:Localidad localidad
            LEFT JOIN localidad.provincia provincia
            LEFT JOIN provincia.pais pais
            WHERE pais.id = :pais
        ")->setParameter('pais', $pais->getId());

        $getId = function ($value) { return $value['id'];};
        $localidadIds = array_map($getId, $queryLocalidadIds->getArrayResult());

        if (0 === count($localidadIds)) {
            return array();
        }

        shuffle($localidadIds);

        if (null !== $limit && count($localidadIds) >= $limit) {
            $localidadIds = array_slice($localidadIds, 0, $limit);
        }

        $queryLocalidades = $this->getEntityManager()->createQuery("
            SELECT localidad
            FROM ConfigBundle:Localidad localidad
            WHERE localidad.id IN (:localidadIds)
        ")->setParameter('localidadIds', $localidadIds);

        return $queryLocalidades->getResult();
    }
}