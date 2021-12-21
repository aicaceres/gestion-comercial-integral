<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class PedidoRepository extends EntityRepository
{
    public function getPedidosByCriteria($tipo,$depId,$periodo){
        if($tipo=='O'){
            $dep = 'do.id';
        }else{
            $dep = 'dd.id';
        }
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('AppBundle\Entity\Pedido', 'p')
              ->innerJoin('p.depositoDestino', 'dd')
              ->innerJoin('p.depositoOrigen', 'do')
              ->where($dep.'='.$depId )  
              ->andWhere("p.fechaPedido>='".UtilsController::toAnsiDate($periodo['desde'])." 00:00'")
              ->andWhere("p.fechaPedido<='".UtilsController::toAnsiDate($periodo['hasta'])." 23:59'");  
        return $query->getQuery()->getResult();
    }
    
    
    public function getPedidosPendientesByDeposito($origen,$destino){
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('AppBundle\Entity\Pedido', 'p')
              ->innerJoin('p.depositoDestino', 'dd')
              ->innerJoin('p.depositoOrigen', 'do')
              ->innerJoin('p.detalles', 'd')  
              ->where("p.estado='PENDIENTE'")
              ->andWhere('dd.id='.$destino )  
              ->andWhere('do.id='.$origen )   
              ->orderBy('p.fechaPedido')  ;  
        return $query->getQuery()->getResult();
    }
    public function getProductosPendientesByDeposito($origen,$destino){
        $query = $this->_em->createQueryBuilder();
        $query->select('d')
              ->from('AppBundle\Entity\PedidoDetalle', 'd')
              ->innerJoin('d.pedido', 'p') 
              ->innerJoin('p.depositoDestino', 'dd')
              ->innerJoin('p.depositoOrigen', 'do')
              ->innerJoin('d.producto','pr')  
              ->where("p.estado='PENDIENTE'")
              ->andWhere('dd.id='.$destino )  
              ->andWhere('do.id='.$origen )   
              ->orderBy('pr.id')  ;  
        return $query->getQuery()->getResult();
    }
    
    public function getPendientes(){
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('AppBundle\Entity\Pedido', 'p')
              ->where("p.estado='PENDIENTE'")
              ->orderBy('p.fechaPedido')  ;  
        return $query->getQuery()->getResult();
    }
    
    public function findProdPedPendiente($prod,$dep){
/*        $qb2=$this->_em->createQueryBuilder();
          $qb2->select('p2.id')
                ->from('AppBundle\Entity\DespachoDetalle','d2')
                 ->innerJoin('d2.pedidoDetalle','p2') ;
          $ins = $qb2->getDQL();
          */
        $query = $this->_em->createQueryBuilder();
        $query->select(' SUM( CASE WHEN d.bulto=0 then d.cantidad ELSE d.cantidad*d.cantidadxBulto END ) cantidad')
              ->from('AppBundle\Entity\Pedido', 'p')
              ->innerJoin('p.depositoOrigen', 'do')  
              ->innerJoin('p.detalles','d') 
              ->innerJoin('d.producto','pr')              
              ->where("p.estado='PENDIENTE'")
              ->andWhere('do.id='.$dep )    
              ->andWhere('pr.id='.$prod);
             // ->andWhere($qb2->expr()->notIn('d.id', $ins));         
        return $query->getQuery()->getSingleScalarResult();
    }
    
    public function findByUnidadNegocioId($unidneg_id)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT deposito
            FROM AppBundle:Deposito deposito
            LEFT JOIN deposito.unidadNegocio unidadNegocio
            WHERE unidadNegocio.id = :unidneg_id
            AND deposito.activo=1
        ")->setParameter('unidneg_id', $unidneg_id);

        return $query->getArrayResult();
    }
   
}