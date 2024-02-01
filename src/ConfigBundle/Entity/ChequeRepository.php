<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
* ChequeRepository
*/
class ChequeRepository extends EntityRepository {

    public function getCheque($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('ConfigBundle\Entity\Cheque', 'c')
                ->where('c.id='.$id);
        return $query->getQuery()->getArrayResult();
    }

    public function getChequesParaPago(){
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('ConfigBundle\Entity\Cheque', 'c')
                ->innerJoin('c.banco', 'b')
                ->where('c.usado=0')
                ->andWhere("b.nombre NOT LIKE '%RETENCION%' ")
                ->andWhere('c.devuelto=0');
        return $query->getQuery()->getResult();
    }

    public function findNoRetenciones($tipo = '', $tipoCheque='', $estado = ''){
      $query = $this->_em->createQueryBuilder();
      $query->select('c')
              ->from('ConfigBundle\Entity\Cheque', 'c')
              ->innerJoin('c.banco', 'b')
              ->where("b.nombre NOT LIKE '%RETENCION%' ")
              ->orderBy('c.fecha','DESC');
      if($tipo){
        $query->andWhere('c.tipo = :tipo')
              ->setParameter('tipo', $tipo);
      }
      if($tipoCheque){
        $query->andWhere('c.tipoCheque = :tipocheque')
              ->setParameter('tipocheque', $tipoCheque);
      }
      if($estado){
        $hoy = new \DateTime();
        switch ($estado) {
          case 'RECHAZADO':
            $query->andWhere('c.devuelto = 1');
            break;
          case 'USADO':
            $query->andWhere('c.usado = 1');
            break;
          case 'FUTURO':
            $query->andWhere("c.fecha > '" . $hoy->format('Y-m-d') ."'")
              ->andWhere("c.devuelto = 0 and c.usado=0");
            break;
          case 'ENFECHA':
            $query->andWhere("c.fecha <= '" . $hoy->format('Y-m-d') ."'")
              ->andWhere("c.devuelto = 0 and c.usado=0");
            break;
        }
      }
      return $query->getQuery()->getResult();
    }
}
