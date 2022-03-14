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

    public function findByCriteria($unidneg, $provId=NULL,$estado=NULL, $desde=NULL, $hasta=NULL,$entregaDesde=NULL, $entregaHasta=NULL){
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
        if($entregaDesde){
          $cadena = " p.fechaEntrega >= '".UtilsController::toAnsiDate($entregaDesde)."'";
          $query->andWhere($cadena);
        }
        if($entregaHasta){
          $cadena = " p.fechaEntrega <= '". UtilsController::toAnsiDate($entregaHasta)."'";
          $query->andWhere($cadena);
        }
        return $query->getQuery()->getResult();

    }

    public function findPendientesEntregaByCriteria($unidneg, $provId, $prodId, $periodo) {
        $query = $this->_em->createQueryBuilder();
        $query->from('ComprasBundle\Entity\PedidoDetalle', 'pd')
                ->innerJoin('pd.pedido', 'p')
                ->innerJoin('pd.producto', 'pr')
                ->innerJoin('p.proveedor', 'pv')
                ->innerJoin('pr.unidadMedida', 'u')
                ->innerJoin('p.unidadNegocio', 'un')
                ->where('u.agrupador_id=27')
                ->andWhere('un.id=' . $unidneg)
                ->andWhere("p.fechaPedido>='" . UtilsController::toAnsiDate($periodo['desde']) . " 00:00'")
                ->andWhere("p.fechaPedido<='" . UtilsController::toAnsiDate($periodo['hasta']) . " 23:59'")
                ->andWhere("p.estado = 'PENDIENTE'");
        if ($prodId) {
            $query->andWhere('pr.id=' . $prodId);
        }

        if ($provId) {
            $query->andWhere('pv.id=' . $provId);
        }
        $query->select('pr.codigo,pr.nombre producto ,pv.nombre proveedor,p.fechaEntrega, u.nombre unidadMedida,'.
                '(CASE WHEN pd.bulto=1 THEN SUM( pd.cantidad * pd.cantidadxBulto ) ELSE SUM(pd.cantidad) END) cant')
              ->groupBy('p.id,pr.codigo')  ;
        /*if ($provId) {
            $query->select('pr.codigo, pr.nombre producto, u.nombre unidadMedida, '
                            . '(CASE WHEN pd.bulto=1 THEN SUM( pd.cantidad * pd.cantidadxBulto ) ELSE SUM(pd.cantidad) END) cant ')
                    ->andWhere('pv.id=' . $provId)
                    ->groupBy('p.id,pr.codigo');
        }
        else {
            $query->select('pv.nombre proveedor, pr.codigo, pr.nombre producto, u.nombre unidadMedida, '
                            . '(CASE WHEN pd.bulto=1 THEN SUM( pd.cantidad * pd.cantidadxBulto ) ELSE SUM(pd.cantidad) END) cant ')
                    ->groupBy('p.id,pr.codigo');
        }*/
        return $query->getQuery()->getResult();
    }


}