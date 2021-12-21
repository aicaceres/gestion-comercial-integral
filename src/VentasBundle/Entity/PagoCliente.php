<?php
namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * VentasBundle\Entity\PagoCliente
 * @ORM\Table(name="ventas_pago_cliente")
 * @ORM\Entity()
 */
class PagoCliente
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var datetime $fecha
     * @ORM\Column(name="fecha", type="datetime")
     */
    private $fecha;    
    /**
     * @var integer $prefijoNro
     * @ORM\Column(name="prefijo_nro", type="string", length=3)
     */
    protected $prefijoNro;
    /**
     * @var integer $pagoNro
     * @ORM\Column(name="pago_nro", type="string", length=6)
     */
    protected $pagoNro;    
    
    /**
     * @var integer $nroComprobante
     * @ORM\Column(name="nro_comprobante", type="string", length=15, nullable=true)
     */
    protected $nroComprobante;
    /**
     * @var string $concepto
     * @ORM\Column(name="concepto", type="text", nullable=false)
     */    
    protected $concepto;    
    /**
     * @var string $detalle
     * @ORM\Column(name="detalle", type="text", nullable=true)
     */    
    protected $detalle;
    
     /**
     * @var integer $importe
     * @ORM\Column(name="importe", type="decimal", scale=3 )
     */
    protected $importe;
    
     /**
     * @var integer $deposito
     * @ORM\Column(name="deposito", type="decimal", scale=3 )
     */
    protected $deposito;
    
     /**
     * @var integer $retencion
     * @ORM\Column(name="retencion", type="decimal", scale=3 )
     */
    protected $retencion;
    
     /**
     *@ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="pagos")
     *@ORM\JoinColumn(name="cliente_id", referencedColumnName="id") 
     */
    protected $cliente;
    
     /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\Cheque", mappedBy="pagoCliente", cascade={"persist"})
     */    
    private $chequesRecibidos;    

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
    
    
    private $conceptoTxt;    
    public function getConceptoTxt(){
        return $this->conceptoTxt;
    }
    public function setConceptoTxt($txt)
    {
        $this->conceptoTxt = $txt;    
        return $this;
    }    
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->chequesRecibidos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->retencion = 0;
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
     * @return PagoCliente
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
     * Set prefijoNro
     *
     * @param string $prefijoNro
     * @return PagoCliente
     */
    public function setPrefijoNro($prefijoNro)
    {
        $this->prefijoNro = $prefijoNro;
    
        return $this;
    }

    /**
     * Get prefijoNro
     *
     * @return string 
     */
    public function getPrefijoNro()
    {
        return $this->prefijoNro;
    }

    /**
     * Set pagoNro
     *
     * @param string $pagoNro
     * @return PagoCliente
     */
    public function setPagoNro($pagoNro)
    {
        $this->pagoNro = $pagoNro;
    
        return $this;
    }

    /**
     * Get pagoNro
     *
     * @return string 
     */
    public function getPagoNro()
    {
        return $this->pagoNro;
    }
    /**
     * Get nroPago
     * @return string 
     */
    public function getNroPago()
    {
        return $this->prefijoNro.'-'.$this->pagoNro;
    }  
    
    /**
     * Set nroComprobante
     *
     * @param string $nroComprobante
     * @return PagoCliente
     */
    public function setNroComprobante($nroComprobante)
    {
        $this->nroComprobante = $nroComprobante;
    
        return $this;
    }

    /**
     * Get nroComprobante
     *
     * @return string 
     */
    public function getNroComprobante()
    {
        return $this->nroComprobante;
    }

    /**
     * Set concepto
     *
     * @param string $concepto
     * @return PagoCliente
     */
    public function setConcepto($concepto)
    {
        $this->concepto = $concepto;
    
        return $this;
    }

    /**
     * Get concepto
     *
     * @return string 
     */
    public function getConcepto()
    {
        return $this->concepto;
    }

    /**
     * Set detalle
     *
     * @param string $detalle
     * @return PagoCliente
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;
    
        return $this;
    }

    /**
     * Get detalle
     *
     * @return string 
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * Set importe
     *
     * @param string $importe
     * @return PagoCliente
     */
    public function setImporte($importe)
    {
        $this->importe = $importe;
    
        return $this;
    }

    /**
     * Get importe
     *
     * @return string 
     */
    public function getImporte()
    {
        return $this->importe;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return PagoCliente
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return PagoCliente
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
     * Add chequesRecibidos
     *
     * @param \ConfigBundle\Entity\Cheque $chequesRecibidos
     * @return PagoCliente
     */
    public function addChequesRecibido(\ConfigBundle\Entity\Cheque $chequesRecibidos)
    {
        $chequesRecibidos->setPagoCliente($this);
        $this->chequesRecibidos[] = $chequesRecibidos;
        return $this;
    }

    /**
     * Remove chequesRecibidos
     *
     * @param \ConfigBundle\Entity\Cheque $chequesRecibidos
     */
    public function removeChequesRecibido(\ConfigBundle\Entity\Cheque $chequesRecibidos)
    {
        $this->chequesRecibidos->removeElement($chequesRecibidos);
    }

    /**
     * Get chequesRecibidos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChequesRecibidos()
    {
        return $this->chequesRecibidos;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return PagoCliente
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
    
    public function getTotalCheques(){
        $total = 0;
        foreach ($this->chequesRecibidos as $item) {
           // if(!$item->getDevuelto())
                $total = $total + $item->getValor();
        }
        return $total;
    }
    public function getTotal(){
        return $this->getTotalCheques() + $this->deposito + $this->retencion + $this->importe;
    }    

    /**
     * Set deposito
     *
     * @param string $deposito
     * @return PagoCliente
     */
    public function setDeposito($deposito)
    {
        $this->deposito = $deposito;
    
        return $this;
    }

    /**
     * Get deposito
     *
     * @return string 
     */
    public function getDeposito()
    {
        return $this->deposito;
    }

    /**
     * Set retencion
     *
     * @param string $retencion
     * @return PagoCliente
     */
    public function setRetencion($retencion)
    {
        $this->retencion = $retencion;
    
        return $this;
    }

    /**
     * Get retencion
     *
     * @return string 
     */
    public function getRetencion()
    {
        return $this->retencion;
    }
}