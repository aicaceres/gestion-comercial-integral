<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

class EscalasRepository extends EntityRepository {

    /**
     * Escalas impositivas
     */
    public function filterEscalasByTipo($tipo='R'){
        if( $tipo == 'G' ){
            $consulta = "e.tipo='G' OR e.tipo='H'";
            $order = 'e.tipo,e.nombre,e.retencion';
        }else{
            $consulta = "e.tipo='".$tipo."'";
            $order = 'e.id';
        }
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('ConfigBundle\Entity\Escalas', 'e')
                ->where($consulta)
                ->orderBy($order);

        return $query->getQuery()->getResult();
    }

 /** Escala según tipo y valor hasta */
    public function getEscalaByHasta($tipo,$hasta){
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('ConfigBundle\Entity\Escalas', 'e')
                ->where("e.tipo='".$tipo."'")
                ->andWhere('e.retencion > '.$hasta)
                ->andWhere('e.adicional < '.$hasta);
        return $query->getQuery()->getOneOrNullResult();
    }
    
    public function getPercepcionByRetencion($porc){
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('ConfigBundle\Entity\Escalas', 'e')
                ->where("e.tipo='P'")
                ->andWhere('e.retencion = '.$porc);
        return $query->getQuery()->getOneOrNullResult();
    }

}