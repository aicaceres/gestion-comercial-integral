<?php
namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ComprasBundle\Entity\PagoProveedor
 * @ORM\Table(name="compras_pago_proveedor")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\ProveedorRepository")
 */
class PagoProveedor
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
     * @ORM\Column(name="nro_comprobante", type="string", length=20, nullable=true)
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
     *@ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Proveedor", inversedBy="pagos")
     *@ORM\JoinColumn(name="proveedor_id", referencedColumnName="id") 
     */
    protected $proveedor;
    
     /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\Cheque", mappedBy="pagoProveedor",cascade={"persist"})
     */    
    private $chequesPagados;     

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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->chequesPagados = new \Doctrine\Common\Collections\ArrayCollection();
        $this->deposito = 0;
        $this->fecha = new \DateTime();  
    }
    
    public function getConceptoTxt(){
        return $this->conceptoTxt;
    }
    public function setConceptoTxt($txt)
    {
        $this->conceptoTxt = $txt;    
        return $this;
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
     * @return PagoProveedor
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
     * @return PagoProveedor
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
     * @return PagoProveedor
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
     * @return PagoProveedor
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
     * @return PagoProveedor
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
     * @return PagoProveedor
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
     * @return PagoProveedor
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
     * @return PagoProveedor
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
     * Set proveedor
     *
     * @param \ComprasBundle\Entity\Proveedor $proveedor
     * @return PagoProveedor
     */
    public function setProveedor(\ComprasBundle\Entity\Proveedor $proveedor = null)
    {
        $this->proveedor = $proveedor;
    
        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \ComprasBundle\Entity\Proveedor 
     */
    public function getProveedor()
    {
        return $this->proveedor;
    }

    /**
     * Add chequesPagados
     *
     * @param \ConfigBundle\Entity\Cheque $chequesPagados
     * @return PagoProveedor
     */
    public function addChequesPagado(\ConfigBundle\Entity\Cheque $chequesPagados)
    {
        $chequesPagados->setPagoProveedor($this);
        $this->chequesPagados[] = $chequesPagados;
        return $this;
    }

    /**
     * Remove chequesPagados
     *
     * @param \ConfigBundle\Entity\Cheque $chequesPagados
     */
    public function removeChequesPagado(\ConfigBundle\Entity\Cheque $chequesPagados)
    {
        $this->chequesPagados->removeElement($chequesPagados);
    }

    /**
     * Get chequesPagados
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChequesPagados()
    {
        return $this->chequesPagados;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return PagoProveedor
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
        $cant = 0;
        foreach ($this->chequesPagados as $cheque) {
            //if(!$cheque->getDevuelto())
                $cant = $cant + $cheque->getValor();
        }
        return $cant;
    }
    public function getTotal(){
        return $this->getTotalCheques() + $this->deposito + $this->importe;
    }    

    /**
     * Set deposito
     *
     * @param string $deposito
     * @return PagoProveedor
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
}