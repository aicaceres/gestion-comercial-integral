<?php
namespace ComprasBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class PedidoRepository extends EntityRepository
{
    public function getPendientes(){
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('ComprasBundle\Entity\Pedido', 'p')
              ->where("p.estado='PENDIENTE'")
              ->orderBy('p.fechaPedido')  ;
        return $query->getQuery()->getResult();
    }
    public function getCountPendientes(){
        $query = $this->_em->createQueryBuilder();
        $query->select('COUNT(p.id)')
              ->from('ComprasBundle\Entity\Pedido', 'p')
              ->where("p.estado='PENDIENTE'");
        return $query->getQuery()->getSingleScalarResult();
    }

    public function findByCriteria($unidneg, $provId=NULL,$estado=NULL, $desde=NULL, $hasta=NULL){
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('ComprasBundle\Entity\Pedido', 'p')
              ->innerJoin('p.unidadNegocio', 'u')
              ->where("u.id=".$unidneg) ;
        if($provId){
            $query->innerJoin('p.proveedor', 'pr')
                    ->andWhere('pr.id='.$provId);
        }
        if($estado){
            $query->andWhere("p.estado='".$estado."'");
        }
        if($desde){
          $cadena = " p.fechaPedido >= '".UtilsController::toAnsiDate($desde)."'";
          $query->andWhere($cadena);
      }
      if($hasta){
          $cadena = " p.fechaPedido <= '". UtilsController::toAnsiDate($hasta)."'";
          $query->andWhere($cadena);
      }
        return $query->getQuery()->getResult();

    }

}