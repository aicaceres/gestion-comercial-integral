<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
* PrecioListaRepository
*/
class PrecioListaRepository extends EntityRepository {

    public function setPrincipalOff() {
        $query = $this->_em->createQuery('Update AppBundle\Entity\PrecioLista p
        set p.principal=0');
        return $query->execute();
    }
    public function findListadoPrincipal(){
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('AppBundle\Entity\Precio', 'p')
              ->innerJoin('p.precioLista', 'l')
              ->where('l.principal=1')
              ->orderBy('p.producto')  ;  
        return $query->getQuery()->getResult();
    }
    
    public function findByProductoyLista($prod,$lista){
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('AppBundle\Entity\Precio', 'p')
              ->innerJoin('p.precioLista', 'l')
              ->innerJoin('p.producto', 'd')
              ->where('l.id='.$lista)
              ->andWhere('d.id='.$prod);
        return $query->getQuery()->getOneOrNullResult();
    }
    
    public function findByRubroProductoyLista($rubroid=NULL,$provid=NULL,$lista){
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('AppBundle\Entity\Precio', 'p')
              ->innerJoin('p.precioLista', 'l')   
                ->innerJoin('p.producto', 'd')
              ->andWhere('l.id='.$lista);
        if($provid){
          $query->leftJoin('d.proveedor', 'v')
              ->where('v.id='.$provid);  
        }
        if($rubroid){
          $query
              ->leftJoin('d.rubro', 'r')
              ->where('r.id='.$rubroid);  
        }
        
        return $query->getQuery()->getResult();
    }
    
    public function listOrderByRubro($id){
        $query = $this->_em->createQueryBuilder();
        $query->select('p')
              ->from('AppBundle\Entity\Precio', 'p')
              ->innerJoin('p.precioLista', 'l')
              ->innerJoin('p.producto', 'd')
              ->where('l.id='.$id)
              ->orderBy('d.rubro');
        return $query->getQuery()->getResult();
    }
}
