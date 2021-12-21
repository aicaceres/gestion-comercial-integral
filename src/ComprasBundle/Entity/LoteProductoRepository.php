<?php
namespace ComprasBundle\Entity;
use Doctrine\ORM\EntityRepository;

class LoteProductoRepository extends EntityRepository
{
    public function getByProductoId($producto_id)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT lote
            FROM ComprasBundle:LoteProducto lote
            INNER JOIN lote.producto producto
            WHERE producto.id = :producto_id
            AND lote.activo=1
        ")->setParameter('producto_id', $producto_id);

        return $query->getResult();
    }
}