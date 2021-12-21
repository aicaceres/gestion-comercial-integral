<?php
namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\NotaDebito
 * @ORM\Table(name="ventas_nota_debito")
 * @ORM\Entity()
 */
class NotaDebito
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var integer $debitoCredito
     * @ORM\Column(name="debito_credito", type="string", length=1)
     */
    protected $debitoCredito = 'D';
    
    /**
     * @var integer $tipoNotaDebito
     * @ORM\Column(name="tipo_nota_debito", type="string", length=1)
     */
    protected $tipoNotaDebito;

    /**
     * @var integer $nroNotaDebito
     * @ORM\Column(name="nro_nota_debito", type="string", length=15)
     */
    protected $nroNotaDebito;

    /**
     * @var datetime $fecha
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;    
    
    /** 
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado;
    /**
     * @var integer $comisionado
     * @ORM\Column(name="comisionado", type="decimal", scale=3, nullable=true )
     */
    protected $comisionado;
     /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=2 )
     */
    protected $total;
    
     /**
     *@ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="notasDebitoVenta")
     *@ORM\JoinColumn(name="cliente_id", referencedColumnName="id") 
     */
    protected $cliente;
    
     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     *@ORM\JoinColumn(name="deposito_id", referencedColumnName="id") 
     */
    protected $deposito;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrecioLista")
     * @ORM\JoinColumn(name="precio_lista_id", referencedColumnName="id")
     */
    protected $precioLista; 
    
    /** 
     * @var string $prefijoCompAsoc
     * @ORM\Column(name="prefijo_comp_asoc", type="string", nullable=true)
     */
    protected $prefijoCompAsoc;
    /** 
     * @var string $tipoCompAsoc
     * @ORM\Column(name="tipo_comp_asoc", type="string", nullable=true)
     */
    protected $tipoCompAsoc;
    /** 
     * @var string $nroCompAsoc
     * @ORM\Column(name="nro_comp_asoc", type="string", nullable=true)
     */
    protected $nroCompAsoc;
    
    /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\FacturaElectronica", mappedBy="notaDebitoCredito")
     */    
    private $notaElectronica; 
    
     /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\NotaDebitoDetalle", mappedBy="notaDebito",cascade={"persist", "remove"})
     */
    protected $detalles;
    
    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;
    
    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User $updatedBy
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;     

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comisionado = 0;
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return NotaDebito
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return NotaDebito
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return NotaDebito
     */
    public function setTotal($total)
    {
        $this->total = $total;
    
        return $this;
    }

    /**
     * Get total
     *
     * @return string 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return NotaDebito
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
     * Set updated
     *
     * @param \DateTime $updated
     * @return NotaDebito
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Add detalles
     *
     * @param \VentasBundle\Entity\NotaDebitoDetalle $detalles
     * @return NotaDebito
     */
    public function addDetalle(\VentasBundle\Entity\NotaDebitoDetalle $detalles)
    {
        $detalles->setNotaDebito($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \VentasBundle\Entity\NotaDebitoDetalle $detalles
     */
    public function removeDetalle(\VentasBundle\Entity\NotaDebitoDetalle $detalles)
    {
        $this->detalles->removeElement($detalles);
    }

    /**
     * Get detalles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDetalles()
    {
        return $this->detalles;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return NotaDebito
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
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return NotaDebito
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;
    
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
    

    public function getCantidadItems(){
        $cant = 0;
        foreach ($this->detalles as $detalle) {
            $cant = $cant + $detalle->getCantidad();
        }
        return $cant;
    }

    public function getIva(){
        $iva = 0;
        foreach ($this->detalles as $item) {
            $iva = $iva + $item->getMontoIva();
        }
        return $iva;
    }
    public function getDescuento(){
        $descuento = 0;
        foreach ($this->detalles as $item) {
            $descuento = $descuento + $item->getMontoDescuento();
        }
        return $descuento;
    }
    public function getSubTotal(){
        $subtotal = 0;
        foreach ($this->detalles as $item) {
            $subtotal = $subtotal + $item->getSubTotal();
        }
        return $subtotal;
    }
    public function getMontoTotal(){
        return ($this->getSubTotal() - $this->getDescuento()) + $this->getIva();
    }    

    /**
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return NotaDebito
     */
    public function setCliente(\VentasBundle\Entity\Cliente $cliente = null)
    {
        $this->cliente = $cliente;
    
        return $this;
    }

    /**
     * Get cliente
     *
     * @return \VentasBundle\Entity\Cliente 
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * Set deposito
     *
     * @param \AppBundle\Entity\Deposito $deposito
     * @return NotaDebito
     */
    public function setDeposito(\AppBundle\Entity\Deposito $deposito = null)
    {
        $this->deposito = $deposito;    
        return $this;
    }

    /**
     * Get deposito
     *
     * @return \AppBundle\Entity\Deposito 
     */
    public function getDeposito()
    {
        return $this->deposito;
    }

    /**
     * Set precioLista
     *
     * @param \AppBundle\Entity\PrecioLista $precioLista
     * @return NotaDebito
     */
    public function setPrecioLista(\AppBundle\Entity\PrecioLista $precioLista = null)
    {
        $this->precioLista = $precioLista;    
        return $this;
    }

    /**
     * Get precioLista
     *
     * @return \AppBundle\Entity\PrecioLista 
     */
    public function getPrecioLista()
    {
        return $this->precioLista;
    }

    /**
     * Set tipoNotaDebito
     *
     * @param string $tipoNotaDebito
     * @return NotaDebito
     */
    public function setTipoNotaDebito($tipoNotaDebito)
    {
        $this->tipoNotaDebito = $tipoNotaDebito;
    
        return $this;
    }

    /**
     * Get tipoNotaDebito
     *
     * @return string 
     */
    public function getTipoNotaDebito()
    {
        return $this->tipoNotaDebito;
    }

    /**
     * Set nroNotaDebito
     *
     * @param string $nroNotaDebito
     * @return NotaDebito
     */
    public function setNroNotaDebito($nroNotaDebito)
    {
        $this->nroNotaDebito = $nroNotaDebito;
    
        return $this;
    }

    /**
     * Get nroNotaDebito
     *
     * @return string 
     */
    public function getNroNotaDebito()
    {
        return $this->nroNotaDebito;
    }
    
    /**
     * Set debitoCredito
     *
     * @param string $debitoCredito
     * @return NotaDebito
     */
    public function setDebitoCredito($debitoCredito)
    {
        $this->debitoCredito = $debitoCredito;
    
        return $this;
    }

    /**
     * Get debitoCredito
     *
     * @return string 
     */
    public function getDebitoCredito()
    {
        if($this->debitoCredito){
            return $this->debitoCredito;
           } 
        else{  
            return "C"; 
        }    
    }

    /**
     * Set prefijoCompAsoc
     *
     * @param string $prefijoCompAsoc
     * @return NotaDebito
     */
    public function setPrefijoCompAsoc($prefijoCompAsoc)
    {
        $this->prefijoCompAsoc = $prefijoCompAsoc;
    
        return $this;
    }

    /**
     * Get prefijoCompAsoc
     *
     * @return string 
     */
    public function getPrefijoCompAsoc()
    {
        return $this->prefijoCompAsoc;
    }

    /**
     * Set tipoCompAsoc
     *
     * @param string $tipoCompAsoc
     * @return NotaDebito
     */
    public function setTipoCompAsoc($tipoCompAsoc)
    {
        $this->tipoCompAsoc = $tipoCompAsoc;
    
        return $this;
    }

    /**
     * Get tipoCompAsoc
     *
     * @return string 
     */
    public function getTipoCompAsoc()
    {
        return $this->tipoCompAsoc;
    }

    /**
     * Set nroCompAsoc
     *
     * @param string $nroCompAsoc
     * @return NotaDebito
     */
    public function setNroCompAsoc($nroCompAsoc)
    {
        $this->nroCompAsoc = $nroCompAsoc;
    
        return $this;
    }

    /**
     * Get nroCompAsoc
     *
     * @return string 
     */
    public function getNroCompAsoc()
    {
        return $this->nroCompAsoc;
    }

    /**
     * Set notaElectronica
     *
     * @param \VentasBundle\Entity\FacturaElectronica $notaElectronica
     * @return NotaDebito
     */
    public function setNotaElectronica(\VentasBundle\Entity\FacturaElectronica $notaElectronica = null)
    {
        $this->notaElectronica = $notaElectronica;
    
        return $this;
    }

    /**
     * Get notaElectronica
     *
     * @return \VentasBundle\Entity\FacturaElectronica 
     */
    public function getNotaElectronica()
    {
        return $this->notaElectronica;
    }
}