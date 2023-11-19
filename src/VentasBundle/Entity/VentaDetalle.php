<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * VentasBundle\Entity\VentaDetalle
 * @ORM\Table(name="ventas_venta_detalle")
 * @ORM\Entity()
 * @Gedmo\Loggable()
 */
class VentaDetalle {
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto", inversedBy="ventas")
     * @ORM\JoinColumn(name="producto_id", referencedColumnName="id")
     */
    protected $producto;

    /**
     * @var string $textoComodin
     * @ORM\Column(name="texto_comodin", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $textoComodin;

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=3)
     * @Gedmo\Versioned()
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
     * @ORM\Column(name="precio", type="decimal", precision=15, scale=3 )
     * @Gedmo\Versioned()
     */
    protected $precio = 0;

    /**
     * @var integer $alicuota
     * @ORM\Column(name="alicuota", type="decimal", scale=3 )
     * @Gedmo\Versioned()
     */
    protected $alicuota = 0;

    /**
     * @var integer $dtoRec
     * monto descuento o recargo
     * @ORM\Column(name="dtoRec", type="decimal", scale=3 )
     * @Gedmo\Versioned()
     */
    protected $dtoRec = 0;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Venta", inversedBy="detalles")
     * @ORM\JoinColumn(name="ventas_venta_id", referencedColumnName="id")
     */
    protected $venta;

    /** VALORES ITEM  */
    // valor del precio unitario segun categoria de iva
    public function getPrecioUnitarioItem() {
        $categIva = $this->getVenta()->getCliente()->getCategoriaIva()->getNombre();
        if ($categIva == 'I' || $categIva == 'M') {
            // precio sin iva convertido a la cotizacion
            $precio = $this->getPrecio() / $this->getVenta()->getCotizacion();
        }
        else {
            // precio con iva incluido convertido a la cotizacion
            $precio = ( $this->getPrecio() * ( 1 + ($this->getAlicuota() / 100)) ) / $this->getVenta()->getCotizacion();
        }
        return round($precio, 3);
    }

    // monto del descuento del item para calcular iva y sumariar total si categoriaIva I o M
    public function getDtoRecItem() {
        $porcDtoRec = $this->getVenta()->getDescuentoRecargo();
        return ($this->getPrecio() * ($porcDtoRec / 100) );
    }

    // total del descuento
    public function getTotalDtoRecItem() {
        $porcDtoRec = $this->getVenta()->getDescuentoRecargo();
        return ($this->getPrecio() * ($porcDtoRec / 100) ) * $this->getCantidad();
    }

    // monto del iva del item para sumariar total si categoriaIva I o M
    public function getIvaItem() {
        return (($this->getPrecio() + $this->getDtoRecItem() ) * ($this->getAlicuota() / 100));
    }

    // total del iva x item
    public function getTotalIvaItem() {
        return (($this->getPrecio() + $this->getDtoRecItem() ) * ($this->getAlicuota() / 100)) * $this->getCantidad();
    }

    // total del item
    public function getTotalItem() {
        return round(($this->getPrecioUnitarioItem() * $this->getCantidad()), 3);
    }

    public function getBaseImponibleItem() {
        $precio = ($this->getPrecio() / $this->getVenta()->getCotizacion()) * $this->getCantidad();
        return round($precio, 3);
    }

    /** FIN VALORES ITEM */
    public function getNombreProducto() {
        return ( $this->getProducto()->getComodin() ) ? $this->getTextoComodin() : $this->getProducto()->getNombre();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return VentaDetalle
     */
    public function setOrden($orden) {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden() {
        return $this->orden;
    }

    /**
     * Set cantidad
     *
     * @param string $cantidad
     * @return VentaDetalle
     */
    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return string
     */
    public function getCantidad() {
        return $this->cantidad;
    }

    /**
     * Set bulto
     *
     * @param boolean $bulto
     * @return VentaDetalle
     */
    public function setBulto($bulto) {
        $this->bulto = $bulto;

        return $this;
    }

    /**
     * Get bulto
     *
     * @return boolean
     */
    public function getBulto() {
        return $this->bulto;
    }

    /**
     * Set cantidadxBulto
     *
     * @param integer $cantidadxBulto
     * @return VentaDetalle
     */
    public function setCantidadxBulto($cantidadxBulto) {
        $this->cantidadxBulto = $cantidadxBulto;

        return $this;
    }

    /**
     * Get cantidadxBulto
     *
     * @return integer
     */
    public function getCantidadxBulto() {
        return $this->cantidadxBulto;
    }

    /**
     * Set precio
     *
     * @param string $precio
     * @return VentaDetalle
     */
    public function setPrecio($precio) {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return string
     */
    public function getPrecio() {
        return $this->precio;
    }

    /**
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return VentaDetalle
     */
    public function setProducto(\AppBundle\Entity\Producto $producto = null) {
        $this->producto = $producto;

        return $this;
    }

    /**
     * Get producto
     *
     * @return \AppBundle\Entity\Producto
     */
    public function getProducto() {
        return $this->producto;
    }

    /**
     * Set venta
     *
     * @param \VentasBundle\Entity\Venta $venta
     * @return VentaDetalle
     */
    public function setVenta(\VentasBundle\Entity\Venta $venta = null) {
        $this->venta = $venta;

        return $this;
    }

    /**
     * Get venta
     *
     * @return \VentasBundle\Entity\Venta
     */
    public function getVenta() {
        return $this->venta;
    }

    /**
     * Set dtoRec
     *
     * @param string $dtoRec
     * @return VentaDetalle
     */
    public function setDtoRec($dtoRec) {
        $this->dtoRec = $dtoRec;

        return $this;
    }

    /**
     * Get dtoRec
     *
     * @return string
     */
    public function getDtoRec() {
        return $this->dtoRec;
    }

    /**
     * Set alicuota
     *
     * @param string $alicuota
     * @return VentaDetalle
     */
    public function setAlicuota($alicuota) {
        $this->alicuota = $alicuota;

        return $this;
    }

    /**
     * Get alicuota
     *
     * @return string
     */
    public function getAlicuota() {
        return $this->alicuota;
    }

    /**
     * Set textoComodin
     *
     * @param string $textoComodin
     * @return VentaDetalle
     */
    public function setTextoComodin($textoComodin) {
        $this->textoComodin = $textoComodin;

        return $this;
    }

    /**
     * Get textoComodin
     *
     * @return string
     */
    public function getTextoComodin() {
        return $this->textoComodin;
    }

}