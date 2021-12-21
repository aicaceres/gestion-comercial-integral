<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AppBundle\Entity\Embarque
 * @ORM\Table(name="stock_despacho")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\DespachoRepository")
 */
class Despacho
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var integer $prefijoNro
     * @ORM\Column(name="prefijo_nro", type="string", length=3)
     */
    protected $prefijoNro;
    /**
     * @var integer $despachoNro
     * @ORM\Column(name="despacho_nro", type="string", length=6)
     */
    protected $despachoNro;    

    /**
     * @var date $fechaDespacho
     * @ORM\Column(name="fecha_despacho", type="date", nullable=false)
     */
    private $fechaDespacho ;     

    /**
     * @var date $fechaEntrega
     * @ORM\Column(name="fecha_entrega", type="date", nullable=true)
     */
    private $fechaEntrega;     
    
    /** 
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    /* NUEVO - DESPACHADO - ENTREGADO - CANCELADO */
    protected $estado;
    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;     
    
     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="deposito_origen_id", referencedColumnName="id")
     */
    // Quien envía la mercaderia
    protected $depositoOrigen;     
    
     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="deposito_destino_id", referencedColumnName="id")
     */
    // Quien recibe la mercaderia
    protected $depositoDestino;    
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\DespachoDetalle", mappedBy="despacho",cascade={"persist", "remove"})
     */
    protected $detalles;
    
    /**
     * @ORM\Column(name="observDespacho", type="text", nullable=true)
     */
    protected $observDespacho;    
    /**
     * @ORM\Column(name="observRecepcion", type="text", nullable=true)
     */
    protected $observRecepcion;    
    
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
 * Estados: TRANSITO - ENTREGADO - CANCELADO
 */

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->estado = 'NUEVO';
        $this->fechaDespacho = new \DateTime;
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
     * Set prefijoNro
     *
     * @param string $prefijoNro
     * @return Despacho
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
     * Set despachoNro
     *
     * @param string $despachoNro
     * @return Despacho
     */
    public function setDespachoNro($despachoNro)
    {
        $this->despachoNro = $despachoNro;

        return $this;
    }

    /**
     * Get despachoNro
     *
     * @return string 
     */
    public function getDespachoNro()
    {
        return $this->despachoNro;
    }
    
    /**
     * Get nroDespacho
     * @return string 
     */
    public function getNroDespacho()
    {
        return $this->prefijoNro.'-'.$this->despachoNro;
    }    

    /**
     * Set fechaDespacho
     *
     * @param \DateTime $fechaDespacho
     * @return Despacho
     */
    public function setFechaDespacho($fechaDespacho)
    {
        $this->fechaDespacho = $fechaDespacho;

        return $this;
    }

    /**
     * Get fechaDespacho
     *
     * @return \DateTime 
     */
    public function getFechaDespacho()
    {
        return $this->fechaDespacho;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Despacho
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
     * Set created
     *
     * @param \DateTime $created
     * @return Despacho
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
     * @return Despacho
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
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Despacho
     */
    public function setUnidadNegocio(\ConfigBundle\Entity\UnidadNegocio $unidadNegocio = null)
    {
        $this->unidadNegocio = $unidadNegocio;

        return $this;
    }

    /**
     * Get unidadNegocio
     *
     * @return \ConfigBundle\Entity\UnidadNegocio 
     */
    public function getUnidadNegocio()
    {
        return $this->unidadNegocio;
    }

    /**
     * Set depositoOrigen
     *
     * @param \AppBundle\Entity\Deposito $depositoOrigen
     * @return Despacho
     */
    public function setDepositoOrigen(\AppBundle\Entity\Deposito $depositoOrigen = null)
    {
        $this->depositoOrigen = $depositoOrigen;

        return $this;
    }

    /**
     * Get depositoOrigen
     *
     * @return \AppBundle\Entity\Deposito 
     */
    public function getDepositoOrigen()
    {
        return $this->depositoOrigen;
    }

    /**
     * Set depositoDestino
     *
     * @param \AppBundle\Entity\Deposito $depositoDestino
     * @return Despacho
     */
    public function setDepositoDestino(\AppBundle\Entity\Deposito $depositoDestino = null)
    {
        $this->depositoDestino = $depositoDestino;

        return $this;
    }

    /**
     * Get depositoDestino
     *
     * @return \AppBundle\Entity\Deposito 
     */
    public function getDepositoDestino()
    {
        return $this->depositoDestino;
    }

    /**
     * Add detalles
     *
     * @param \AppBundle\Entity\DespachoDetalle $detalles
     * @return Despacho
     */
    public function addDetalle(\AppBundle\Entity\DespachoDetalle $detalles)
    {
        $detalles->setDespacho($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\DespachoDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\DespachoDetalle $detalles)
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
     * @return Despacho
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
     * @return Despacho
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

    /**
     * Set fechaEntrega
     *
     * @param \DateTime $fechaEntrega
     * @return Despacho
     */
    public function setFechaEntrega($fechaEntrega)
    {
        $this->fechaEntrega = $fechaEntrega;

        return $this;
    }

    /**
     * Get fechaEntrega
     *
     * @return \DateTime 
     */
    public function getFechaEntrega()
    {
        return $this->fechaEntrega;
    }

    /**
     * Set observDespacho
     *
     * @param string $observDespacho
     * @return Despacho
     */
    public function setObservDespacho($observDespacho)
    {
        $this->observDespacho = $observDespacho;

        return $this;
    }

    /**
     * Get observDespacho
     *
     * @return string 
     */
    public function getObservDespacho()
    {
        return $this->observDespacho;
    }
    /**
     * Set observRecepcion
     *
     * @param string $observRecepcion
     * @return Despacho
     */
    public function setObservRecepcion($observRecepcion)
    {
        $this->observRecepcion = $observRecepcion;

        return $this;
    }

    /**
     * Get observRecepcion
     *
     * @return string 
     */
    public function getObservRecepcion()
    {
        return $this->observRecepcion;
    }
    
    public function hayInconsistencia(){
        foreach ($this->getDetalles() as $detalle) {
            if ($detalle->hayInconsistencia() )
                return true;            
        }
        return false;
    }
    
    public function desdePedido(){
        foreach ($this->getDetalles() as $detalle) {
            if ($detalle->getPedidoDetalle() )
                return true;            
        }
        return false;
    }
}
