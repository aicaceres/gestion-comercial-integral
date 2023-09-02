<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
 * PrecioActualizacionRepository
 */
class PrecioActualizacionRepository extends EntityRepository {
    /* public function findOrderDesc(){
      $query = $this->_em->createQueryBuilder();
      $query->select('a')
      ->from('CM\AdminBundle\Entity\PrecioActualizacion', 'a')
      ->orderBy('a.id','ASC')  ;
      return $query->getQuery()->getResult();
      } */

    public function getDatosActPrecio($tipo) {
        if ($tipo == 'R') {
            $qb = $this->_em->createQueryBuilder('p')
                ->select('p')
                ->from('ConfigBundle\Entity\Parametro', 'p')
                ->innerJoin('p.agrupador', 'r')
                ->where('r.padre_id=:agr')
                ->orderBy('p.nombre')
                ->setParameter('agr', 9);
        }
        else {
            $qb = $this->_em->createQueryBuilder('p')
                ->select('p')
                ->from('ComprasBundle\Entity\Proveedor', 'p')
                ->where('p.activo=1')
                ->orderBy('p.nombre');
        }
        return $qb->getQuery()->getResult();
    }

    public function setPreciosActualizados($entity, $valores, $user) {
        if ($entity->getTipoActualizacion() == 'M') {
            $calculo1 = 'pr.precio + ' . $entity->getValor();
        }
        else {
            $calculo1 = 'ROUND((pr.precio * (100 + ' . $entity->getValor() . ' ) / 100),2)';
        }
        $qb = $this->_em->createQueryBuilder('pr')
            ->update('AppBundle\Entity\Precio', 'pr')
            ->set('pr.precio', $calculo1)
            ->set('pr.updated', 'CURRENT_TIMESTAMP()')
            ->set('pr.updatedBy', $user->getId())
            ->where('pr.precioLista=' . $entity->getPrecioLista()->getId());
        if ($entity->getCriteria() <> 'T') {
            $qb2 = $this->_em->createQueryBuilder();
            $qb2->select('p2.id')
                ->from('AppBundle\Entity\Producto', 'p2');
            //->where('pr2.precioLista='.$entity->getPrecioLista()->getId())  ;
            if ($entity->getCriteria() == 'P') {
                $qb2->andWhere('p2.proveedor in (' . $valores . ')');
            }
            else {
                $qb2->andWhere('p2.rubro in (' . $valores . ')');
            }
            $ins = $qb2->getDQL();
            $qb->andWhere($qb2->expr()->In('pr.producto', $ins));
        }
        return $qb->getQuery()->execute();
    }

}