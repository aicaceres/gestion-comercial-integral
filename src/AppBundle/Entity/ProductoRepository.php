<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
* ProductoRepository
*/
class ProductoRepository extends EntityRepository {

    public function getProductosSinPrecio($lista){
        $qb=$this->_em->createQueryBuilder();
        $qb2=$this->_em->createQueryBuilder();

        $nots= $qb2->select('p2.id')
                ->from('AppBundle\Entity\Precio','pr')
                ->innerJoin('pr.producto', 'p2')->where('pr.precioLista='.$lista);
        $prod = $qb->select('p')
             ->from('AppBundle\Entity\Producto', 'p')
             ->where($qb2->expr()->notIn('p.id', $nots->getDQL()));
        return $prod;
    }
    
    public function getProductosFacturables(){
        $query = $this->_em->createQueryBuilder('p')
                ->select('p')
                ->from('AppBundle\Entity\Producto', 'p')
                ->where('p.facturable=1')
                ->andWhere('p.activo=1');
        return $query;
    }
    
    public function findBajoMinimo($id,$dep){
       $query = $this->_em->createQueryBuilder('p')
                ->select(" p.codigo,p.nombre,u.nombre unidadMedida,pr.nombre proveedor, r.nombre rubro, CASE WHEN (s.stockMinimo is not null) THEN s.stockMinimo ELSE p.stockMinimo END stockMinimo, s.cantidad stockActual  ")
                ->from('AppBundle\Entity\Producto', 'p')
                ->innerJoin('p.stock', 's')
                ->innerJoin('s.deposito','d')
                ->leftJoin('p.rubro', 'r')
                ->leftJoin('p.unidadMedida', 'u')
                ->leftJoin('p.proveedor', 'pr')
                ->where('d.id='.$dep)
               ->andWhere('CASE WHEN (s.stockMinimo is not null) THEN s.stockMinimo ELSE p.stockMinimo END >= s.cantidad');  
        
       /*$query = $this->_em->createQueryBuilder('s')
                ->select('s')
                ->from('AppBundle\Entity\Stock', 's')
                ->leftJoin('s.producto', 'p')
                ->innerJoin('s.deposito','d')
                ->andWhere('d.id='.$dep);  */ 
        if($id){
            $query->innerJoin('p.proveedor', 'pv')
                    ->andWhere('pv.id='.$id  );
        }

        return $query->getQuery()->getResult();                
    }

    
    public function findProductosPorDepositoyProveedor($unidneg,$prov,$dep){
        $query = $this->_em->createQueryBuilder('s')
                ->select('s')
                ->from('AppBundle\Entity\Stock', 's')
                ->innerJoin('s.producto', 'p')
                ->innerJoin('s.deposito', 'd')
                ->innerJoin('d.unidadNegocio', 'u')
                ->where('p.activo=1')
                ->andWhere('u.id='.$unidneg);
        if($prov){
            $query->innerJoin('p.proveedor', 'pr')
                  ->andWhere('pr.id='.$prov);
        }
        if($dep){
            $query->andWhere('d.id='.$dep);
        }
        return $query->getQuery()->getResult();
    }
    
    public function getProductosByTerm($term)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT p.id,p.nombre
            FROM AppBundle:Producto p
            WHERE p.nombre LIKE :term
            AND p.activo=1
        ")->setParameter('term', '%a'.$term.'%');

        return $query->getResult();
    }
    
    public function findDepositosSinstockPorProducto($id,$unidneg){
        $qb=$this->_em->createQueryBuilder();
        $qb2=$this->_em->createQueryBuilder();

        $nots= $qb2->select('d.id')
                ->from('AppBundle\Entity\Stock','s')
                ->innerJoin('s.deposito', 'd')   
                ->innerJoin('s.producto', 'p')   
                ->where('d.activo=1')
                ->andWhere( 'p.id='.$id );
        
        $stk = $qb->select('d2.id, upper(d2.nombre) nombre ')
             ->from('AppBundle\Entity\Deposito', 'd2')
             ->innerJoin('d2.unidadNegocio' , 'u')
             ->where('u.id='.$unidneg)   
             ->andWhere($qb2->expr()->notIn('d2.id', $nots->getDQL()))
             ->orderBy('d2.nombre')   ;
        return $stk->getQuery()->getArrayResult();        
    }
}