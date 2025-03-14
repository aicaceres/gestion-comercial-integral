<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
 * ProductoRepository
 */
class ProductoRepository extends EntityRepository {

    public function getProductosSinPrecio($lista) {
        $qb = $this->_em->createQueryBuilder();
        $qb2 = $this->_em->createQueryBuilder();

        $nots = $qb2->select('p2.id')
                ->from('AppBundle\Entity\Precio', 'pr')
                ->innerJoin('pr.producto', 'p2')->where('pr.precioLista=' . $lista);
        $prod = $qb->select('p')
            ->from('AppBundle\Entity\Producto', 'p')
            ->where($qb2->expr()->notIn('p.id', $nots->getDQL()));
        return $prod;
    }

    public function getProductosFacturables() {
        $query = $this->_em->createQueryBuilder('p')
            ->select('p')
            ->from('AppBundle\Entity\Producto', 'p')
            ->where('p.facturable=1')
            ->andWhere('p.activo=1');
        return $query;
    }

    public function findBajoMinimo($id, $dep) {
        $query = $this->_em->createQueryBuilder('p')
            ->select(" p.codigo,p.nombre,u.nombre unidadMedida,pr.nombre proveedor, r.nombre rubro, CASE WHEN (s.stockMinimo is not null) THEN s.stockMinimo ELSE p.stockMinimo END stockMinimo, s.cantidad stockActual, p.costo  ")
            ->from('AppBundle\Entity\Producto', 'p')
            ->innerJoin('p.stock', 's')
            ->innerJoin('s.deposito', 'd')
            ->leftJoin('p.rubro', 'r')
            ->leftJoin('p.unidadMedida', 'u')
            ->leftJoin('p.proveedor', 'pr')
            ->where('d.id=' . $dep)
            ->andWhere('p.comodin=0')
            ->andWhere('CASE WHEN (s.stockMinimo is not null) THEN s.stockMinimo ELSE p.stockMinimo END >= s.cantidad');
        if ($id) {
            $query->innerJoin('p.proveedor', 'pv')
                ->andWhere('pv.id=' . $id);
        }

        return $query->getQuery()->getResult();
    }

    public function findProductosPorDepositoyProveedor($unidneg, $prov, $dep, $conStock=false, $soloActivos=true) {
        $query = $this->_em->createQueryBuilder('s')
            ->select('s')
            ->from('AppBundle\Entity\Stock', 's')
            ->innerJoin('s.producto', 'p')
            ->innerJoin('s.deposito', 'd')
            ->innerJoin('d.unidadNegocio', 'u')            
            ->where('u.id=' . $unidneg);
        if ($prov) {
            $query->innerJoin('p.proveedor', 'pr')
                ->andWhere('pr.id=' . $prov);
        }
        if ($dep) {
            $query->andWhere('d.id=' . $dep);
        }
        if($conStock){
          $query->andWhere('s.cantidad>0');
        }
        if($soloActivos){
          $query->andWhere('p.activo=1');
        }
        return $query->getQuery()->getResult();
    }

    public function getProductosByTerm($term) {
        $query = $this->getEntityManager()->createQuery("
            SELECT p.id,p.nombre
            FROM AppBundle:Producto p
            WHERE p.nombre LIKE :term
            AND p.activo=1
        ")->setParameter('term', '%a' . $term . '%');

        return $query->getResult();
    }

    public function findDepositosSinstockPorProducto($id, $unidneg) {
        $qb = $this->_em->createQueryBuilder();
        $qb2 = $this->_em->createQueryBuilder();

        $nots = $qb2->select('d.id')
            ->from('AppBundle\Entity\Stock', 's')
            ->innerJoin('s.deposito', 'd')
            ->innerJoin('s.producto', 'p')
            ->where('d.activo=1')
            ->andWhere('p.id=' . $id);

        $stk = $qb->select('d2.id, upper(d2.nombre) nombre ')
            ->from('AppBundle\Entity\Deposito', 'd2')
            ->innerJoin('d2.unidadNegocio', 'u')
            ->where('u.id=' . $unidneg)
            ->andWhere($qb2->expr()->notIn('d2.id', $nots->getDQL()))
            ->orderBy('d2.nombre');
        return $stk->getQuery()->getArrayResult();
    }

    public function findListasSinPrecioPorProducto($id) {
        $qb = $this->_em->createQueryBuilder();
        $qb2 = $this->_em->createQueryBuilder();

        $nots = $qb2->select('l.id')
            ->from('AppBundle\Entity\Precio', 'pr')
            ->innerJoin('pr.precioLista', 'l')
            ->innerJoin('pr.producto', 'p')
            ->where('p.id=' . $id);

        $lista = $qb->select('l2.id, upper(l2.nombre) nombre ')
            ->from('AppBundle\Entity\PrecioLista', 'l2')
            ->where($qb2->expr()->notIn('l2.id', $nots->getDQL()))
            ->orderBy('l2.nombre');
        return $lista->getQuery()->getArrayResult();
    }

    public function getProductosForExportXls($provId = null, $search = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select("p")
            ->from('AppBundle\Entity\Producto', 'p')
            ->leftJoin('p.proveedor', 'pr')
            ->leftJoin('p.rubro', 'r')
            ->orderBy('p.nombre');
        if ($provId) {
            $query->andWhere('pr.id=' . $provId);
        }
        if ($search) {
            $searchItem = trim($search);
            $searchQuery = ' p.nombre LIKE \'%' . $searchItem . '%\' OR p.codigo LIKE \'%' . $searchItem . '%\' ' .
                ' OR pr.nombre LIKE \'%' . $searchItem . '%\'  OR  r.nombre LIKE \'%' . $searchItem . '%\' ' . ' OR p.costo LIKE \'%' . $searchItem . '%\' ';
            $query->andWhere($searchQuery);
        }
        return $query->getQuery()->getResult();
    }

// PARA LISTA DE PRODUCTOS POPUP
    public function listcount() {
        $query = $this->_em->createQueryBuilder();
        $query->select("count(e.id)")
            ->from('AppBundle\Entity\Producto', 'e')
            ->where('e.activo=1');
        return $query->getQuery()->getSingleScalarResult();
    }

    public function getListDTData($start, $length, $orders, $search, $columns, $otherConditions,
        $listaprecio) {
        // Create Main Query
        $query = $this->_em->createQueryBuilder();
        $query->select("e")
            ->from('AppBundle\Entity\Producto', 'e');

        // Create Count Query
        $countQuery = $this->_em->createQueryBuilder();
        $countQuery->select("count(e.id)")
            ->from('AppBundle\Entity\Producto', 'e');

        // Create inner joins
        $query->leftJoin('e.precios', 'p')
            ->leftJoin('p.precioLista', 'l');

        $countQuery->leftJoin('e.precios', 'p')
            ->leftJoin('p.precioLista', 'l');

        // Other conditions than the ones sent by the Ajax call ?
        if ($otherConditions === null) {
            // No
            // However, add a "always true" condition to keep an uniform treatment in all cases
            $query->where("e.activo=1");
            $countQuery->where("e.activo=1");
        }
        else {
            // Add condition
            $query->where($otherConditions);
            $countQuery->where($otherConditions);
        }

        if ($listaprecio) {
            $searchQuery = 'l.id=' . $listaprecio;
            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }


        if ($search['value']) {
            $searchItem = trim($search['value']);
            $searchQuery = ' e.nombre LIKE \'%' . $searchItem . '%\' OR e.codigo LIKE \'%' . $searchItem . '%\' ';
            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'nombre': {
                            $orderColumn = 'e.nombre';
                            break;
                        }
                    case 'codigo': {
                            $orderColumn = 'e.codigo';
                            break;
                        }
                }

                if ($orderColumn !== null) {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }
        // Execute
        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return array(
            "results" => $results,
            "countResult" => $countResult
        );
    }

    /**
     * para administracion de productos
     */
    public function indexCount($provId = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select("count(p.id)")
            ->from('AppBundle\Entity\Producto', 'p');
        if ($provId) {
            $query->innerJoin('p.proveedor', 'pr')
                ->andWhere('pr.id=' . $provId);
        }
        return $query->getQuery()->getSingleScalarResult();
    }

    public function getIndexDTData($start, $length, $orders, $search, $columns, $otherConditions, $provId = null) {
        // Create Main Query
        $query = $this->_em->createQueryBuilder();
        $query->select("p")
            ->from('AppBundle\Entity\Producto', 'p');

        // Create Count Query
        $countQuery = $this->_em->createQueryBuilder();
        $countQuery->select("count(p.id)")
            ->from('AppBundle\Entity\Producto', 'p');

        // Create inner joins
        $query->leftJoin('p.proveedor', 'pr')
            ->leftJoin('p.rubro', 'r');

        $countQuery->leftJoin('p.proveedor', 'pr')
            ->leftJoin('p.rubro', 'r');

        // Other conditions than the ones sent by the Ajax call ?
        /*        if ($otherConditions === null) {
          // No
          // However, add a "always true" condition to keep an uniform treatment in all cases
          $query->where("1=1");
          $countQuery->where("1=1");
          }
          else {
          // Add condition
          $query->where($otherConditions);
          $countQuery->where($otherConditions);
          } */
        if ($provId) {
            $searchQuery = 'pr.id=' . $provId;
            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }

        if ($search['value']) {
            $searchItem = trim($search['value']);
            $searchQuery = ' p.nombre LIKE \'%' . $searchItem . '%\' OR p.codigo LIKE \'%' . $searchItem . '%\' ' .
                ' OR pr.nombre LIKE \'%' . $searchItem . '%\'  OR  r.nombre LIKE \'%' . $searchItem . '%\' ';
            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'codigo': {
                            $orderColumn = 'p.codigo';
                            break;
                        }
                    case 'nombre': {
                            $orderColumn = 'p.nombre';
                            break;
                        }
                    case 'proveedor': {
                            $orderColumn = 'pr.nombre';
                            break;
                        }
                    case 'rubro': {
                            $orderColumn = 'r.nombre';
                            break;
                        }
                }

                if ($orderColumn !== null) {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }

        // Execute
        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return array(
            "results" => $results,
            "countResult" => $countResult
        );
    }

    // para autocompletar
    public function filterByTerm($key) {
        $query = $this->_em->createQueryBuilder();
        $query->select("p.id, p.codigo, p.nombre, concat(p.nombre,' | ',p.codigo) text")
            ->from('AppBundle:Producto', 'p')
            ->where('p.nombre LIKE :key')
            ->orWhere('p.codigo LIKE :key')
            ->orWhere('p.codigoBarra LIKE :key')
            ->andWhere('p.activo=1')
            ->orderBy('p.nombre')
            ->setParameter('key', '%' . $key . '%')
            ->setMaxResults(20);
        return $query->getQuery()->getArrayResult();
    }

}