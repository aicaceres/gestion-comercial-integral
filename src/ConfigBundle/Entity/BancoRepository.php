<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

/**
* BancoRepository
*/
class BancoRepository extends EntityRepository {

  public function findCuentasByBanco($id){
    $query = $this->_em->createQueryBuilder();
    $query->select('c')
            ->from('ConfigBundle\Entity\CuentaBancaria', 'c')
            ->innerJoin('c.banco', 'b')
            ->where('b.id='.$id)
            ->andWhere('c.activo=1')
            ->orderBy('c.nroCuenta');
    return $query->getQuery()->getArrayResult();
  }

    public function findByCriteria($id, $cta, $periodo, $conciliado) {
      $conciliado = $conciliado ? $conciliado : 0;
        // saldo anterior
        $query = $this->_em->createQueryBuilder();
        $query->select("SUM( CASE WHEN t.signo='-' THEN m.importe*-1 ELSE m.importe END) as saldo")
                ->from('ConfigBundle\Entity\BancoMovimiento', 'm')
                ->innerJoin('m.banco', 'b')
                ->innerJoin('m.cuenta', 'c')
                ->innerJoin('m.tipoMovimiento', 't')
                ->where('b.id='.$id)
                ->andWhere('c.id='.$cta);
        if ($periodo['ini']) {
            $cadena = " m.fechaCarga < '" . UtilsController::toAnsiDate($periodo['ini']) . "'";
            $query->andWhere($cadena);
        }
        $saldoInicial = $query->getQuery()->getSingleScalarResult();

        $cadenaIni = $periodo['ini'] ? " m.fechaCarga >= '" . UtilsController::toAnsiDate($periodo['ini']) . "'" : "";
        $cadenaFin = $periodo['fin'] ? " m.fechaCarga <= '" . UtilsController::toAnsiDate($periodo['fin']) . "'" : "";

        // movimientos
        $query = $this->_em->createQueryBuilder();
        $query->select('m,t')
                ->from('ConfigBundle\Entity\BancoMovimiento', 'm')
                ->innerJoin('m.banco', 'b')
                ->innerJoin('m.cuenta', 'c')
                ->innerJoin('m.tipoMovimiento', 't')
                ->where('b.id='.$id)
                ->andWhere('c.id='.$cta)
                ->andWhere($cadenaIni)
                ->andWhere($cadenaFin)
                ->orderBy('m.fechaCarga');
         if(!$conciliado){
          $query->andWhere('m.conciliado=0');
         }
        $movimientos = $query->getQuery()->getResult();

        // saldo conciliado
        $query = $this->_em->createQueryBuilder();
        $query->select("SUM( CASE WHEN t.signo='+' THEN m.importe ELSE 0 END) as debe,
                        SUM( CASE WHEN t.signo='-' THEN m.importe*-1 ELSE 0 END) as haber")
                ->from('ConfigBundle\Entity\BancoMovimiento', 'm')
                ->innerJoin('m.banco', 'b')
                ->innerJoin('m.cuenta', 'c')
                ->innerJoin('m.tipoMovimiento', 't')
                ->where('b.id='.$id)
                ->andWhere('c.id='.$cta)
                ->andWhere('m.conciliado = 1')
                ->andWhere($cadenaIni)
                ->andWhere($cadenaFin);
        $resultConciliados = $query->getQuery()->getOneOrNullResult();

        // saldo total
        $query = $this->_em->createQueryBuilder();
        $query->select("SUM( CASE WHEN t.signo='+' THEN m.importe ELSE 0 END) as debe,
                        SUM( CASE WHEN t.signo='-' THEN m.importe*-1 ELSE 0 END) as haber")
                ->from('ConfigBundle\Entity\BancoMovimiento', 'm')
                ->innerJoin('m.banco', 'b')
                ->innerJoin('m.cuenta', 'c')
                ->innerJoin('m.tipoMovimiento', 't')
                ->where('b.id='.$id)
                ->andWhere('c.id='.$cta)
                ->andWhere($cadenaIni)
                ->andWhere($cadenaFin);
        $resultTotal = $query->getQuery()->getOneOrNullResult();

        return array('movimientos' => $movimientos, 'saldoInicial' => $saldoInicial,
          'saldoConciliado'=> $resultConciliados, 'saldoTotal' => $resultTotal);
    }

  public function findSaldoAnterior($id, $cta, $periodo){

    $query = $this->_em->createQueryBuilder();
        $query->select("SUM( CASE WHEN t.signo='-' THEN m.importe*-1 ELSE m.importe END) as saldo")
                ->from('ConfigBundle\Entity\BancoMovimiento', 'm')
                ->innerJoin('m.banco', 'b')
                ->innerJoin('m.cuenta', 'c')
                ->innerJoin('m.tipoMovimiento', 't')
                ->where('b.id='.$id)
                ->andWhere('c.id='.$cta);
        if ($periodo['ini']) {
            $cadena = " m.fechaCarga < '" . UtilsController::toAnsiDate($periodo['ini']) . "'";
            $query->andWhere($cadena);
        }

        return $query->getQuery()->getSingleScalarResult();
  }
}
