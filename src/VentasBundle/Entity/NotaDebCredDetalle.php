<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * VentasBundle\Entity\NotaDebCredDetalle
 * @ORM\Table(name="ventas_nota_debcred_detalle")
 * @ORM\Entity()
 */
class NotaDebCredDetalle {
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
     * @var integer $textoProducto
     * @ORM\Column(name="texto_producto", type="string",nullable=true)
     */
    protected $textoProducto;

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
    protected $precio = 0;
    /**
     * @var integer $alicuota
     * @ORM\Column(name="alicuota", type="decimal", scale=3 )
     */
    protected $alicuota = 0;

    /**
     * @var integer $descuento
     * @ORM\Column(name="descuento", type="decimal", scale=3 )
     */
    protected $descuento = 0;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\NotaDebCred", inversedBy="detalles")
     * @ORM\JoinColumn(name="ventas_nota_debcred_id", referencedColumnName="id")
     */
    protected $notaDebCred;

    /** VALORES ITEM  */
    // valor del precio unitario segun categoria de iva
    public function getPrecioUnitarioItem(){
        $categIva = $this->getNotaDebCred()->getCliente()->getCategoriaIva()->getNombre();
        if( $categIva == 'I' || $categIva == 'M'){
            // precio sin iva convertido a la cotizacion
            $precio = $this->getPrecio() / $this->getNotaDebCred()->getCotizacion();
        }else{
            // precio con iva incluido convertido a la cotizacion
            $precio = ( $this->getPrecio() * ( 1 + ($this->getAlicuota() / 100)) ) / $this->getNotaDebCred()->getCotizacion();
        }
        return round( $precio, 3);
    }
    // monto del descuento del item para calcular iva y sumariar total si categoriaIva I o M
    public function getDtoRecItem(){
        $porcDtoRec = $this->getNotaDebCred()->getDescuentoRecargo();
        return ($this->getPrecio() * ($porcDtoRec / 100) ) ;
    }
    // monto del iva del item para sumariar total si categoriaIva I o M
    public function getIvaItem(){
        return ($this->getPrecio() + $this->getDtoRecItem() ) * ($this->getAlicuota() / 100);
    }
    // total del item
    public function getTotalItem(){
        return round( ($this->getPrecioUnitarioItem() * $this->getCantidad()) ,3);
    }
    /** FIN VALORES ITEM */

    /** Calculos * */
    public function getSubTotal() {
        return $this->precio * $this->cantidad;
    }
    public function getPrecioMasIva(){
        return $this->precio * (1 + ($this->alicuota/100));
    }

    public function getMontoIva() {
        return ($this->getSubTotal() + $this->getDescuento()) * ( $this->getAlicuota()/100 );
    }

    public function getTotal() {
        return ($this->getSubTotal() + $this->getDescuento()) + $this->getMontoIva();
    }

    // Cantidad para mostrar como texto
    public function getCantidadTxt() {
        if ($this->bulto) {
            return $this->getCantidad() . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            return $this->getCantidad() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
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
     * @return NotaDebCredDetalle
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
     * Set textoProducto
     *
     * @param string $textoProducto
     * @return NotaDebCredDetalle
     */
    public function setTextoProducto($textoProducto)
    {
        $this->textoProducto = $textoProducto;

        return $this;
    }

    /**
     * Get textoProducto
     *
     * @return string
     */
    public function getTextoProducto()
    {
        return $this->textoProducto;
    }

    /**
     * Set cantidad
     *
     * @param string $cantidad
     * @return NotaDebCredDetalle
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
     * @return NotaDebCredDetalle
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
     * @return NotaDebCredDetalle
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
     * @return NotaDebCredDetalle
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
     * Set descuento
     *
     * @param string $descuento
     * @return NotaDebCredDetalle
     */
    public function setDescuento($descuento)
    {
        $this->descuento = $descuento;

        return $this;
    }

    /**
     * Get descuento
     *
     * @return string
     */
    public function getDescuento()
    {
        return $this->descuento;
    }

    /**
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return NotaDebCredDetalle
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
     * Set notaDebCred
     *
     * @param \VentasBundle\Entity\NotaDebCred $notaDebCred
     * @return NotaDebCredDetalle
     */
    public function setNotaDebCred(\VentasBundle\Entity\NotaDebCred $notaDebCred = null)
    {
        $this->notaDebCred = $notaDebCred;

        return $this;
    }

    /**
     * Get notaDebCred
     *
     * @return \VentasBundle\Entity\NotaDebCred
     */
    public function getNotaDebCred()
    {
        return $this->notaDebCred;
    }

    /**
     * Set alicuota
     *
     * @param string $alicuota
     * @return NotaDebCredDetalle
     */
    public function setAlicuota($alicuota)
    {
        $this->alicuota = $alicuota;

        return $this;
    }

    /**
     * Get alicuota
     *
     * @return string
     */
    public function getAlicuota()
    {
        return $this->alicuota;
    }
}
