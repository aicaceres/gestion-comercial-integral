<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AppBundle\Entity\Pedido
 * @ORM\Table(name="stock_pedido")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\PedidoRepository")
 */
class Pedido
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
     * @var integer $pedidoNro
     * @ORM\Column(name="pedido_nro", type="string", length=8)
     */
    protected $pedidoNro;

    /**
     * @var date $fechaPedido
     * @ORM\Column(name="fecha_pedido", type="date", nullable=false)
     */
    private $fechaPedido;    
    
    /**
     * @var date $fechaEntrega
     * @ORM\Column(name="fecha_entrega", type="date", nullable=true)
     */
    private $fechaEntrega;    
    
    /** 
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    /* NUEVO - PENDIENTE - DESPACHADO - ENTREGADO - CANCELADO */
    protected $estado;
    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;     
    
     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito", inversedBy="pedidosSolicitados")
     * @ORM\JoinColumn(name="deposito_origen_id", referencedColumnName="id")
     */
    // Origen del Pedido
    protected $depositoOrigen;     
    
     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito", inversedBy="pedidosDemandados")
     * @ORM\JoinColumn(name="deposito_destino_id", referencedColumnName="id")
     */
    // Destino del pedido
    protected $depositoDestino;     

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PedidoDetalle", mappedBy="pedido",cascade={"persist", "remove"})
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
 * Estados: NUEVO - PENDIENTE - ENTREGADO - CANCELADO
 */
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fechaPedido = new \DateTime(); 
        $this->estado = 'NUEVO';
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
     * @return Pedido
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
     * Set pedidoNro
     *
     * @param string $pedidoNro
     * @return Pedido
     */
    public function setPedidoNro($pedidoNro)
    {
        $this->pedidoNro = $pedidoNro;
    
        return $this;
    }

    /**
     * Get pedidoNro
     *
     * @return string 
     */
    public function getPedidoNro()
    {
        return $this->pedidoNro;
    }
    
    /**
     * Get nroPedido
     *
     * @return string 
     */
    public function getNroPedido()
    {
        return $this->prefijoNro.'-'.$this->pedidoNro;
    }

    /**
     * Set fechaEntrega
     *
     * @param \DateTime $fechaEntrega
     * @return Pedido
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
     * Set fechaPedido
     * @param \DateTime $fechaPedido
     * @return Pedido
     */
    public function setFechaPedido($fechaPedido)
    {
        $this->fechaPedido = $fechaPedido;
    
        return $this;
    }

    /**
     * Get fechaPedido
     *
     * @return \DateTime 
     */
    public function getFechaPedido()
    {
        return $this->fechaPedido;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Pedido
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
     * @return Pedido
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
     * @return Pedido
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Pedido
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
     * @return Pedido
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
     * Add detalles
     *
     * @param \AppBundle\Entity\PedidoDetalle $detalles
     * @return Pedido
     */
    public function addDetalle(\AppBundle\Entity\PedidoDetalle $detalles)
    {
        $detalles->setPedido($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\PedidoDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\PedidoDetalle $detalles)
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
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Pedido
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
 *  Calculos
 */    

    /**
     * Calcula items del pedido
     */
    public function getCantidadItems(){
        $cant = 0;
        foreach ($this->detalles as $detalle) {
            $cant = $cant + $detalle->getCantidad();
        }
        return $cant;
    }
    /**
     * Calcula items del pedido
     */
    public function getRecibidos(){
        $cant = 0;
        foreach ($this->detalles as $detalle) {
            $cant = $cant + $detalle->getEntregado();
        }
        return $cant;
    }
    
    public function getItemsPendientes(){
        $cant = 0;
        foreach ($this->detalles as $detalle) {
            $cant = $cant + $detalle->getItemsPendientes();
        }
        return $cant;
    }

    public function getEnDespacho(){
        $res = false;
        if( $this->getEstado()=='PENDIENTE' ){
            foreach ($this->detalles as $detalle) {
                if( $detalle->getDespachoDetalle() )
                    return true;
            }
        }
        return $res;
    }

    /**
     * Set depositoOrigen
     *
     * @param \AppBundle\Entity\Deposito $depositoOrigen
     * @return Pedido
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
     * @return Pedido
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
}
