<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

class EscalasRepository extends EntityRepository {

    /**
     * Escalas impositivas
     */        
    public function filterEscalasByTipo($tipo='R'){
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