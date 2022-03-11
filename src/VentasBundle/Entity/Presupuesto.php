<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\Presupuesto
 * @ORM\Table(name="ventas_presupuesto")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\PresupuestoRepository")
 */
class Presupuesto {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $nroPresupuesto
     * @ORM\Column(name="nro_presupuesto", type="string", length=30)
     */
    protected $nroPresupuesto;

    /**
     * @var datetime $fechaPresupuesto
     * @ORM\Column(name="fecha_presupuesto", type="datetime", nullable=false)
     */
    private $fechaPresupuesto;

    /**
     * Estados: EMITIDO - ANULADO
     */
    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado;

    /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=3 )
     */
    protected $total;

    /**
     * @var integer $descuento
     * @ORM\Column(name="descuento", type="decimal", scale=3 )
     */
    protected $descuento = 0;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="facturasVenta")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     */
    protected $cliente;
   /**
     * @var string $nombreCliente
     * @ORM\Column(name="nombre_cliente", type="string", nullable=true)
     */
    protected $nombreCliente;
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\FormaPago")
     * @ORM\JoinColumn(name="forma_pago_id", referencedColumnName="id")
     * */
    protected $formaPago;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrecioLista")
     * @ORM\JoinColumn(name="precio_lista_id", referencedColumnName="id")
     */
    protected $precioLista;

     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito;
    /**
     * @ORM\Column(name="descuenta_stock", type="boolean",nullable=true)
     */
    protected $descuentaStock = false;

    /**
     * @var integer $validez
     * validez en dias
     * @ORM\Column(name="validez", type="integer")
     */
    protected $validez;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\PresupuestoDetalle", mappedBy="presupuesto",cascade={"persist", "remove"})
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
    }

    public function getSubTotal(){
        $subtotal = 0;
        foreach ($this->detalles as $item) {
            $subtotal = $subtotal + $item->getTotal();
        }
        return $subtotal;
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
     * Set nroPresupuesto
     *
     * @param string $nroPresupuesto
     * @return Presupuesto
     */
    public function setNroPresupuesto($nroPresupuesto)
    {
        $this->nroPresupuesto = $nroPresupuesto;

        return $this;
    }

    /**
     * Get nroPresupuesto
     *
     * @return string
     */
    public function getNroPresupuesto()
    {
        return $this->nroPresupuesto;
    }

    /**
     * Set fechaPresupuesto
     *
     * @param \DateTime $fechaPresupuesto
     * @return Presupuesto
     */
    public function setFechaPresupuesto($fechaPresupuesto)
    {
        $this->fechaPresupuesto = $fechaPresupuesto;

        return $this;
    }

    /**
     * Get fechaPresupuesto
     *
     * @return \DateTime
     */
    public function getFechaPresupuesto()
    {
        return $this->fechaPresupuesto;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Presupuesto
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
     * @return Presupuesto
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
     * Set descuento
     *
     * @param string $descuento
     * @return Presupuesto
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
     * Set created
     *
     * @param \DateTime $created
     * @return Presupuesto
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
     * @return Presupuesto
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return Presupuesto
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
     * Set precioLista
     *
     * @param \AppBundle\Entity\PrecioLista $precioLista
     * @return Presupuesto
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
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Presupuesto
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
     * Add detalles
     *
     * @param \VentasBundle\Entity\PresupuestoDetalle $detalles
     * @return Presupuesto
     */
    public function addDetalle(\VentasBundle\Entity\PresupuestoDetalle $detalles)
    {
        $detalles->setPresupuesto($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \VentasBundle\Entity\PresupuestoDetalle $detalles
     */
    public function removeDetalle(\VentasBundle\Entity\PresupuestoDetalle $detalles)
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
     * @return Presupuesto
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
     * @return Presupuesto
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
     * Set formaPago
     *
     * @param \ConfigBundle\Entity\FormaPago $formaPago
     * @return Presupuesto
     */
    public function setFormaPago(\ConfigBundle\Entity\FormaPago $formaPago = null)
    {
        $this->formaPago = $formaPago;

        return $this;
    }

    /**
     * Get formaPago
     *
     * @return \ConfigBundle\Entity\FormaPago
     */
    public function getFormaPago()
    {
        return $this->formaPago;
    }

    /**
     * Set nombreCliente
     *
     * @param string $nombreCliente
     * @return Presupuesto
     */
    public function setNombreCliente($nombreCliente)
    {
        $this->nombreCliente = $nombreCliente;

        return $this;
    }

    /**
     * Get nombreCliente
     *
     * @return string
     */
    public function getNombreCliente()
    {
        return $this->nombreCliente;
    }

    /**
     * Set descuentaStock
     *
     * @param boolean $descuentaStock
     * @return Presupuesto
     */
    public function setDescuentaStock($descuentaStock)
    {
        $this->descuentaStock = $descuentaStock;

        return $this;
    }

    /**
     * Get descuentaStock
     *
     * @return boolean
     */
    public function getDescuentaStock()
    {
        return $this->descuentaStock;
    }

    /**
     * Set deposito
     *
     * @param \AppBundle\Entity\Deposito $deposito
     * @return Presupuesto
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
     * Set validez
     *
     * @param integer $validez
     * @return Presupuesto
     */
    public function setValidez($validez)
    {
        $this->validez = $validez;

        return $this;
    }

    /**
     * Get validez
     *
     * @return integer
     */
    public function getValidez()
    {
        return $this->validez;
    }
}
