<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class DespachoRepository extends EntityRepository
{
    
    public function getDespachosByCriteria($depId,$periodo){
        $query = $this->_em->createQueryBuilder();
        $query->select('d')
              ->from('AppBundle\Entity\Despacho', 'd')
              ->innerJoin('d.depositoOrigen', 'do')
              ->andWhere('do.id='.$depId )  
                ->andWhere("d.fechaDespacho>='".UtilsController::toAnsiDate($periodo['desde'])." 00:00'")
              ->andWhere("d.fechaDespacho<='".UtilsController::toAnsiDate($periodo['hasta'])." 23:59'");  
        return $query->getQuery()->getResult();
    }
    
    public function getRecepcionesByCriteria($depId,$periodo){
        $query = $this->_em->createQueryBuilder();
        $query->select('d')
              ->from('AppBundle\Entity\Despacho', 'd')
              ->innerJoin('d.depositoDestino', 'dd')
              ->andWhere('dd.id='.$depId )  
                ->andWhere("d.fechaDespacho>='".UtilsController::toAnsiDate($periodo['desde'])." 00:00'")
              ->andWhere("d.fechaDespacho<='".UtilsController::toAnsiDate($periodo['hasta'])." 23:59'");  
        return $query->getQuery()->getResult();
    }
    
    public function getLastNroDespacho($prefijo){
        $query = $this->_em->createQueryBuilder();
        $query->select('MAX(e.despachoNro)')
              ->from('AppBundle\Entity\Despacho', 'e')
              ->where('e.prefijoNro=:arg')
              ->setParameter('arg',$prefijo)  ;  
        return $query->getQuery()->getSingleScalarResult();
    }
    
    public function getResumenDespacho($id){
        $query = $this->_em->createQueryBuilder();
        $query->select('p.codigo, p.nombre producto, SUM(d.cantidad) cantidad')
              ->from('AppBundle\Entity\Despacho', 'e')
                ->innerJoin('e.detalles', 'd')
                ->innerJoin('d.producto', 'p')
                ->where('e.id=:arg')
                ->setParameter('arg',$id) 
                ->groupBy('p.codigo, p.nombre');
        return $query->getQuery()->getResult();
    }
    public function getPedidosDespacho($id){
        $query = $this->_em->createQueryBuilder();
        $query->select('distinct p.id')
              ->from('AppBundle\Entity\Despacho', 'e')
              ->innerJoin('e.detalles', 'd')
              ->innerJoin('d.pedidoDetalle', 'pd')
              ->innerJoin('pd.pedido', 'p')
              ->where('e.id=:arg')
              ->setParameter('arg',$id) ;
        return $query->getQuery()->getResult();
    }
    
    
    public function findDespachadoByCriteria($depId,$periodo){
        $query = $this->_em->createQueryBuilder();
        $query->select('pr.codigo, pr.nombre producto, u.nombre unidadMedida, SUM(dd.entregado) entregado, 0 precultcompra ')
              ->from('AppBundle\Entity\DespachoDetalle', 'dd')
              ->innerJoin('dd.despacho', 'd')
              ->innerJoin('d.depositoDestino', 'do')
              ->innerJoin('dd.producto','pr')
              ->innerJoin('pr.unidadMedida','u')  
              ->where('do.id='.$depId )  
              ->andWhere('u.agrupador_id=27' )  
              ->andWhere("d.estado='ENTREGADO'")  
              ->andWhere("d.fechaDespacho>='".UtilsController::toAnsiDate($periodo['desde'])." 00:00'")
              ->andWhere("d.fechaDespacho<='".UtilsController::toAnsiDate($periodo['hasta'])." 23:59'")
              ->groupBy('pr.id')  ;  
        return $query->getQuery()->getResult();
    }     
}