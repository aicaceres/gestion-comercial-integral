<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\Equipo
 * @ORM\Table(name="equipo")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\EquipoRepository")
 */
class Equipo {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     */    
    protected $nombre;
    /**
     * @var string $prefijo
     * @ORM\Column(name="prefijo", type="integer", nullable=true)
     */    
    protected $prefijo;
    /**
     * @var string $activo
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    protected $activo = true;  
    
    // COMPRA
    
    /**
     * @var integer $nroPedidoCompra
     * @ORM\Column(name="nro_pedido_compra", type="string", length=8, nullable=true)
     */
    protected $nroPedidoCompra;
    /**
     * @var integer $nroPedidoInterno
     * @ORM\Column(name="nro_pedido_interno", type="string", length=8, nullable=true)
     */
    protected $nroPedidoInterno;
    /**
     * @var integer $nroFacturaCompra
     * @ORM\Column(name="nro_factura_compra", type="string", length=8, nullable=true)
     */
    protected $nroFacturaCompra;
    /**
     * @var integer $nroNotaDebCredCompra
     * @ORM\Column(name="nro_nota_debcred_compra", type="string", length=8, nullable=true)
     */
    protected $nroNotaDebCredCompra;
    /**
     * @var integer $nroPagoCompra
     * @ORM\Column(name="nro_pago_compra", type="string", length=6, nullable=true)
     */
    protected $nroPagoCompra;
    
    /// VENTA
    
    /**
     * @var integer $nroPedidoVenta
     * @ORM\Column(name="nro_pedido_venta", type="string", length=8, nullable=true)
     */
    protected $nroPedidoVenta;
    /**
     * @var integer $nroPagoVenta
     * @ORM\Column(name="nro_pago_venta", type="string", length=6, nullable=true)
     */
    protected $nroPagoVenta;
    
    /**
     * @var integer $nroFacturaVentaA
     * @ORM\Column(name="nro_factura_venta_a", type="string", length=8, nullable=true)
     */
    protected $nroFacturaVentaA;
    /**
     * @var integer $nroFacturaVentaB
     * @ORM\Column(name="nro_factura_venta_b", type="string", length=8, nullable=true)
     */
    protected $nroFacturaVentaB;
    /**
     * @var integer $nroFacturaVentaC
     * @ORM\Column(name="nro_factura_venta_c", type="string", length=8, nullable=true)
     */
    protected $nroFacturaVentaC;
    
    /**
     * @var integer $nroNotaDebitoVentaA
     * @ORM\Column(name="nro_nota_debito_venta_a", type="string", length=8, nullable=true)
     */
    protected $nroNotaDebitoVentaA;    
    /**
     * @var integer $nroNotaDebitoVentaB
     * @ORM\Column(name="nro_nota_debito_venta_b", type="string", length=8, nullable=true)
     */
    protected $nroNotaDebitoVentaB;
    
    /**
     * @var integer $nroNotaCreditoVentaA
     * @ORM\Column(name="nro_nota_credito_venta_a", type="string", length=8, nullable=true)
     */
    protected $nroNotaCreditoVentaA;    
    /**
     * @var integer $nroNotaCreditoVentaB
     * @ORM\Column(name="nro_nota_credito_venta_b", type="string", length=8, nullable=true)
     */
    protected $nroNotaCreditoVentaB;
    
    
    /**
     * @var integer $nroInternoCheque
     * @ORM\Column(name="nro_interno_cheque", type="string", length=6, nullable=true)
     */
    protected $nroInternoCheque;
    
    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;
    
    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;   


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
     * Set nombre
     *
     * @param string $nombre
     * @return Equipo
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set prefijo
     *
     * @param integer $prefijo
     * @return Equipo
     */
    public function setPrefijo($prefijo)
    {
        $this->prefijo = $prefijo;
    
        return $this;
    }

    /**
     * Get prefijo
     *
     * @return integer 
     */
    public function getPrefijo()
    {
        return $this->prefijo;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return Equipo
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    
        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean 
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set nroPedidoCompra
     *
     * @param string $nroPedidoCompra
     * @return Equipo
     */
    public function setNroPedidoCompra($nroPedidoCompra)
    {
        $this->nroPedidoCompra = $nroPedidoCompra;
    
        return $this;
    }

    /**
     * Get nroPedidoCompra
     *
     * @return string 
     */
    public function getNroPedidoCompra()
    {
        return $this->nroPedidoCompra;
    }

    /**
     * Set nroFacturaCompra
     *
     * @param string $nroFacturaCompra
     * @return Equipo
     */
    public function setNroFacturaCompra($nroFacturaCompra)
    {
        $this->nroFacturaCompra = $nroFacturaCompra;
    
        return $this;
    }

    /**
     * Get nroFacturaCompra
     *
     * @return string 
     */
    public function getNroFacturaCompra()
    {
        return $this->nroFacturaCompra;
    }

    /**
     * Set nroNotaDebCredCompra
     *
     * @param string $nroNotaDebCredCompra
     * @return Equipo
     */
    public function setNroNotaDebCredCompra($nroNotaDebCredCompra)
    {
        $this->nroNotaDebCredCompra = $nroNotaDebCredCompra;
    
        return $this;
    }

    /**
     * Get nroNotaDebCredCompra
     *
     * @return string 
     */
    public function getNroNotaDebCredCompra()
    {
        return $this->nroNotaDebCredCompra;
    }

    /**
     * Set nroPagoCompra
     *
     * @param string $nroPagoCompra
     * @return Equipo
     */
    public function setNroPagoCompra($nroPagoCompra)
    {
        $this->nroPagoCompra = $nroPagoCompra;
    
        return $this;
    }

    /**
     * Get nroPagoCompra
     *
     * @return string 
     */
    public function getNroPagoCompra()
    {
        return $this->nroPagoCompra;
    }

    /**
     * Set nroPedidoVenta
     *
     * @param string $nroPedidoVenta
     * @return Equipo
     */
    public function setNroPedidoVenta($nroPedidoVenta)
    {
        $this->nroPedidoVenta = $nroPedidoVenta;
    
        return $this;
    }

    /**
     * Get nroPedidoVenta
     *
     * @return string 
     */
    public function getNroPedidoVenta()
    {
        return $this->nroPedidoVenta;
    }

    /**
     * Set nroPagoVenta
     *
     * @param string $nroPagoVenta
     * @return Equipo
     */
    public function setNroPagoVenta($nroPagoVenta)
    {
        $this->nroPagoVenta = $nroPagoVenta;
    
        return $this;
    }

    /**
     * Get nroPagoVenta
     *
     * @return string 
     */
    public function getNroPagoVenta()
    {
        return $this->nroPagoVenta;
    }

    /**
     * Set nroFacturaVentaA
     *
     * @param string $nroFacturaVentaA
     * @return Equipo
     */
    public function setNroFacturaVentaA($nroFacturaVentaA)
    {
        $this->nroFacturaVentaA = $nroFacturaVentaA;
    
        return $this;
    }

    /**
     * Get nroFacturaVentaA
     *
     * @return string 
     */
    public function getNroFacturaVentaA()
    {
        return $this->nroFacturaVentaA;
    }

    /**
     * Set nroFacturaVentaB
     *
     * @param string $nroFacturaVentaB
     * @return Equipo
     */
    public function setNroFacturaVentaB($nroFacturaVentaB)
    {
        $this->nroFacturaVentaB = $nroFacturaVentaB;
    
        return $this;
    }

    /**
     * Get nroFacturaVentaB
     *
     * @return string 
     */
    public function getNroFacturaVentaB()
    {
        return $this->nroFacturaVentaB;
    }

    /**
     * Set nroFacturaVentaC
     *
     * @param string $nroFacturaVentaC
     * @return Equipo
     */
    public function setNroFacturaVentaC($nroFacturaVentaC)
    {
        $this->nroFacturaVentaC = $nroFacturaVentaC;
    
        return $this;
    }

    /**
     * Get nroFacturaVentaC
     *
     * @return string 
     */
    public function getNroFacturaVentaC()
    {
        return $this->nroFacturaVentaC;
    }

    /**
     * Set nroNotaDebitoVentaA
     *
     * @param string $nroNotaDebitoVentaA
     * @return Equipo
     */
    public function setNroNotaDebitoVentaA($nroNotaDebitoVentaA)
    {
        $this->nroNotaDebitoVentaA = $nroNotaDebitoVentaA;
    
        return $this;
    }

    /**
     * Get nroNotaDebitoVentaA
     *
     * @return string 
     */
    public function getNroNotaDebitoVentaA()
    {
        return $this->nroNotaDebitoVentaA;
    }

    /**
     * Set nroNotaDebitoVentaB
     *
     * @param string $nroNotaDebitoVentaB
     * @return Equipo
     */
    public function setNroNotaDebitoVentaB($nroNotaDebitoVentaB)
    {
        $this->nroNotaDebitoVentaB = $nroNotaDebitoVentaB;
    
        return $this;
    }

    /**
     * Get nroNotaDebitoVentaB
     *
     * @return string 
     */
    public function getNroNotaDebitoVentaB()
    {
        return $this->nroNotaDebitoVentaB;
    }

    /**
     * Set nroNotaCreditoVentaA
     *
     * @param string $nroNotaCreditoVentaA
     * @return Equipo
     */
    public function setNroNotaCreditoVentaA($nroNotaCreditoVentaA)
    {
        $this->nroNotaCreditoVentaA = $nroNotaCreditoVentaA;
    
        return $this;
    }

    /**
     * Get nroNotaCreditoVentaA
     *
     * @return string 
     */
    public function getNroNotaCreditoVentaA()
    {
        return $this->nroNotaCreditoVentaA;
    }

    /**
     * Set nroNotaCreditoVentaB
     *
     * @param string $nroNotaCreditoVentaB
     * @return Equipo
     */
    public function setNroNotaCreditoVentaB($nroNotaCreditoVentaB)
    {
        $this->nroNotaCreditoVentaB = $nroNotaCreditoVentaB;
    
        return $this;
    }

    /**
     * Get nroNotaCreditoVentaB
     *
     * @return string 
     */
    public function getNroNotaCreditoVentaB()
    {
        return $this->nroNotaCreditoVentaB;
    }

    /**
     * Set nroInternoCheque
     *
     * @param string $nroInternoCheque
     * @return Equipo
     */
    public function setNroInternoCheque($nroInternoCheque)
    {
        $this->nroInternoCheque = $nroInternoCheque;
    
        return $this;
    }

    /**
     * Get nroInternoCheque
     *
     * @return string 
     */
    public function getNroInternoCheque()
    {
        return $this->nroInternoCheque;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Equipo
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Equipo
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set nroPedidoInterno
     *
     * @param string $nroPedidoInterno
     * @return Equipo
     */
    public function setNroPedidoInterno($nroPedidoInterno)
    {
        $this->nroPedidoInterno = $nroPedidoInterno;

        return $this;
    }

    /**
     * Get nroPedidoInterno
     *
     * @return string 
     */
    public function getNroPedidoInterno()
    {
        return $this->nroPedidoInterno;
    }
}
