<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * VentasBundle\Entity\PresupuestoDetalle
 * @ORM\Table(name="ventas_presupuesto_detalle")
 * @ORM\Entity()
 * @Gedmo\Loggable()
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto", inversedBy="presupuestos")
     * @ORM\JoinColumn(name="producto_id", referencedColumnName="id")
     * @Gedmo\Versioned()
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
     * @ORM\Column(name="cantidad", type="decimal", precision=20, scale=3)
     * @Gedmo\Versioned()
     */
    protected $cantidad = 1;

    /**
     * @ORM\Column(name="bulto", type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $bulto = false;

    /**
     * @var integer $cantidadxBulto
     * @ORM\Column(name="cantidad_x_bulto", type="integer", nullable=true )
     * @Gedmo\Versioned()
     */
    protected $cantidadxBulto;

    /**
     * @var integer $precio
     * @ORM\Column(name="precio", type="decimal", precision=20, scale=3 )
     * @Gedmo\Versioned()
     */
    protected $precio;

    /**
     * @var integer $alicuota
     * @ORM\Column(name="alicuota", type="decimal", precision=20, scale=3 )
     * @Gedmo\Versioned()
     */
    protected $alicuota = 0;

    /**
     * @var integer $dtoRec
     * monto descuento o recargo
     * @ORM\Column(name="dtoRec", type="decimal", precision=20, scale=3 )
     * @Gedmo\Versioned()
     */
    protected $dtoRec = 0;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Presupuesto", inversedBy="detalles")
     * @ORM\JoinColumn(name="ventas_presupuesto_id", referencedColumnName="id")
     */
    protected $presupuesto;

    public function getPrecioUnitarioItem() {
        $categIva = $this->getPresupuesto()->getCliente()->getCategoriaIva()->getNombre();
        if ($categIva == 'I' || $categIva == 'M') {
            // precio sin iva convertido a la cotizacion
            $precio = $this->getPrecio();
        }
        else {
            // precio con iva incluido convertido a la cotizacion
            $precio = ( $this->getPrecio() * ( 1 + ($this->getAlicuota() / 100)) );
        }
        return round($precio, 3);
    }

    // monto del descuento del item para calcular iva y sumariar total si categoriaIva I o M
    public function getDtoRecItem() {
        $porcDtoRec = $this->getPresupuesto()->getDescuentoRecargo();
        return ($this->getPrecio() * ($porcDtoRec / 100) );
    }

    // total del descuento
    public function getTotalDtoRecItem() {
        $porcDtoRec = $this->getPresupuesto()->getDescuentoRecargo();
        return ($this->getPrecio() * ($porcDtoRec / 100) ) * $this->getCantidad();
    }

    // monto del iva del item para sumariar total si categoriaIva I o M
    public function getIvaItem() {
        return ($this->getPrecio() + $this->getDtoRecItem() ) * ($this->getAlicuota() / 100);
    }

    // total del iva x item
    public function getTotalIvaItem() {
        return (($this->getPrecio() + $this->getDtoRecItem() ) * ($this->getAlicuota() / 100)) * $this->getCantidad();
    }

    // total del item
    public function getTotalItem() {
        return round(($this->getPrecioUnitarioItem() * $this->getCantidad()), 3);
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
     * @return PresupuestoDetalle
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
     * @return PresupuestoDetalle
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
     * @return PresupuestoDetalle
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
     * @return PresupuestoDetalle
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
     * @return PresupuestoDetalle
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
     * @return PresupuestoDetalle
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
     * Set presupuesto
     *
     * @param \VentasBundle\Entity\Presupuesto $presupuesto
     * @return PresupuestoDetalle
     */
    public function setPresupuesto(\VentasBundle\Entity\Presupuesto $presupuesto = null) {
        $this->presupuesto = $presupuesto;

        return $this;
    }

    /**
     * Get presupuesto
     *
     * @return \VentasBundle\Entity\Presupuesto
     */
    public function getPresupuesto() {
        return $this->presupuesto;
    }

    /**
     * Set alicuota
     *
     * @param string $alicuota
     * @return PresupuestoDetalle
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
     * Set dtoRec
     *
     * @param string $dtoRec
     * @return PresupuestoDetalle
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
     * Set textoComodin
     *
     * @param string $textoComodin
     * @return PresupuestoDetalle
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