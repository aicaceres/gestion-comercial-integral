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
                ->andWhere('e.fechaHasta IS NULL')
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

    /**
     * Busca la escala vigente de la misma categoría (por id) para una fecha.
     */
    public function getEscalaVigenteByCategoriaAndDate($categoria, \DateTime $fecha){
        $catId = $categoria->getId();
        $today = new \DateTime();

        // Buscar escalas que sean la misma categoría (por id) o que coincidan en tipo/nombre/codigoAtp
        // Luego filtrar por el rango fechaDesde - fechaHasta considerando fechaHasta NULL como fecha de hoy
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')
           ->from('ConfigBundle\\Entity\\Escalas', 'e')
           ->where(
               $qb->expr()->orX(
                   'e.id = :catId',
                   $qb->expr()->andX('e.tipo = :tipo', 'e.nombre = :nombre', 'e.codigoAtp = :codigoAtp')
               )
           )
           ->andWhere('e.fechaDesde <= :f')
           ->andWhere(
               $qb->expr()->orX(
                   $qb->expr()->andX('e.fechaHasta IS NULL', $qb->expr()->lte(':f', ':today')),
                   $qb->expr()->gte('e.fechaHasta', ':f')
               )
           )
           ->setParameter('catId', $catId)
           ->setParameter('tipo', $categoria->getTipo())
           ->setParameter('nombre', $categoria->getNombre())
           ->setParameter('codigoAtp', $categoria->getCodigoAtp())
           ->setParameter('f', $fecha->format('Y-m-d'))
           ->setParameter('today', $today->format('Y-m-d'))
           ->orderBy('e.fechaDesde', 'DESC')
           ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Busca la percepción tipo P por retención vigente para una fecha
     * Devuelve la escala más reciente cuyo rango contiene la fecha (fecha_hasta NULL significa vigente)
     */
    public function getPercepcionByRetencionAndDate($porc, \DateTime $fecha){
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')
           ->from('ConfigBundle\Entity\Escalas', 'e')
           ->where("e.tipo='P'")
           ->andWhere('e.retencion = :porc')
           ->setParameter('porc', $porc)
           ->andWhere('e.fechaDesde <= :f')
           ->setParameter('f', $fecha->format('Y-m-d'))
           ->andWhere(
               $qb->expr()->orX('e.fechaHasta IS NULL', 'e.fechaHasta >= :f')
           )
           ->orderBy('e.fechaDesde', 'DESC')
           ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

}