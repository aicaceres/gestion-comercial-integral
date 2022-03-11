<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * VentasBundle\Entity\PresupuestoDetalle
 * @ORM\Table(name="ventas_presupuesto_detalle")
 * @ORM\Entity()
 */
class PresupuestoDetalle {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $orden
     * @ORM\Column(name="orden", type="integer",nullable=true)
     */
    protected $orden;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto")
     * @ORM\JoinColumn(name="producto_id", referencedColumnName="id")
     */
    protected $producto;

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=3)
     */
    protected $cantidad = 1;

    /**
     * @ORM\Column(name="bulto", type="boolean", nullable=true)
     */
    protected $bulto = false;

    /**
     * @var integer $cantidadxBulto
     * @ORM\Column(name="cantidad_x_bulto", type="integer", nullable=true )
     */
    protected $cantidadxBulto;

    /**
     * @var integer $precio
     * @ORM\Column(name="precio", type="decimal", scale=3 )
     */
    protected $precio;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Presupuesto", inversedBy="detalles")
     * @ORM\JoinColumn(name="ventas_presupuesto_id", referencedColumnName="id")
     */
    protected $presupuesto;

    public function getTotal() {
        return $this->getPrecio() * $this->getCantidad();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return PresupuestoDetalle
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set cantidad
     *
     * @param string $cantidad
     * @return PresupuestoDetalle
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return string
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set bulto
     *
     * @param boolean $bulto
     * @return PresupuestoDetalle
     */
    public function setBulto($bulto)
    {
        $this->bulto = $bulto;

        return $this;
    }

    /**
     * Get bulto
     *
     * @return boolean
     */
    public function getBulto()
    {
        return $this->bulto;
    }

    /**
     * Set cantidadxBulto
     *
     * @param integer $cantidadxBulto
     * @return PresupuestoDetalle
     */
    public function setCantidadxBulto($cantidadxBulto)
    {
        $this->cantidadxBulto = $cantidadxBulto;

        return $this;
    }

    /**
     * Get cantidadxBulto
     *
     * @return integer
     */
    public function getCantidadxBulto()
    {
        return $this->cantidadxBulto;
    }

    /**
     * Set precio
     *
     * @param string $precio
     * @return PresupuestoDetalle
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return string
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return PresupuestoDetalle
     */
    public function setProducto(\AppBundle\Entity\Producto $producto = null)
    {
        $this->producto = $producto;

        return $this;
    }

    /**
     * Get producto
     *
     * @return \AppBundle\Entity\Producto
     */
    public function getProducto()
    {
        return $this->producto;
    }

    /**
     * Set presupuesto
     *
     * @param \VentasBundle\Entity\Presupuesto $presupuesto
     * @return PresupuestoDetalle
     */
    public function setPresupuesto(\VentasBundle\Entity\Presupuesto $presupuesto = null)
    {
        $this->presupuesto = $presupuesto;

        return $this;
    }

    /**
     * Get presupuesto
     *
     * @return \VentasBundle\Entity\Presupuesto
     */
    public function getPresupuesto()
    {
        return $this->presupuesto;
    }
}
