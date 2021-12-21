<?php
namespace VentasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * VentasBundle\Entity\NotaDebitoDetalle
 * @ORM\Table(name="ventas_nota_debito_detalle")
 * @ORM\Entity()
 */
class NotaDebitoDetalle
{
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
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto")
     *@ORM\JoinColumn(name="producto_id", referencedColumnName="id") 
     */
    protected $producto;
    
    /**
     * @var integer $textoProducto
     * @ORM\Column(name="texto_producto", type="string",nullable=true)
     */
    protected $textoProducto;
    
     /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="integer")
     */
    protected $cantidad;
    
     /**
     * @var integer $precio
     * @ORM\Column(name="precio", type="decimal", scale=2 )
     */
    protected $precio;
    
     /**
     * @var integer $iva
     * @ORM\Column(name="iva", type="decimal", scale=2 )
     */
    protected $iva=21;
     /**
     * @var integer $descuento
     * @ORM\Column(name="descuento", type="decimal", scale=2 )
     */
    protected $descuento=0;

     /**
     *@ORM\ManyToOne(targetEntity="VentasBundle\Entity\NotaDebito", inversedBy="detalles")
     *@ORM\JoinColumn(name="ventas_nota_debito_id", referencedColumnName="id") 
     */
    protected $notaDebito;
    
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
     * @return NotaDebitoDetalle
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
     * @param integer $cantidad
     * @return NotaDebitoDetalle
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    
        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set precio
     *
     * @param string $precio
     * @return NotaDebitoDetalle
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
     * Set iva
     *
     * @param string $iva
     * @return NotaDebitoDetalle
     */
    public function setIva($iva)
    {
        $this->iva = $iva;
    
        return $this;
    }

    /**
     * Get iva
     *
     * @return string 
     */
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * Set descuento
     *
     * @param string $descuento
     * @return NotaDebitoDetalle
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
     * @return NotaDebitoDetalle
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
     * Set notaDebito
     *
     * @param \VentasBundle\Entity\NotaDebito $notaDebito
     * @return NotaDebitoDetalle
     */
    public function setNotaDebito(\VentasBundle\Entity\NotaDebito $notaDebito = null)
    {
        $this->notaDebito = $notaDebito;
    
        return $this;
    }

    /**
     * Get notaDebito
     *
     * @return \VentasBundle\Entity\NotaDebito 
     */
    public function getNotaDebito()
    {
        return $this->notaDebito;
    }
    
/** Calculos **/    
    public function getSubTotal(){
        return $this->precio * $this->cantidad;
    }
    public function getMontoIva(){
        return ($this->getSubTotal() - $this->getMontoDescuento()) * ($this->iva/100);
    }
    public function getMontoDescuento(){
        return $this->getSubTotal() * ($this->descuento/100);
    }
    public function getTotal(){
        return ($this->getSubTotal() - $this->getMontoDescuento()) + $this->getMontoIva();
    }    

    /**
     * Set textoProducto
     *
     * @param string $textoProducto
     * @return NotaDebitoDetalle
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
}