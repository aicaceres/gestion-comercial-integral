<?php

namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ComprasBundle\Entity\Pedido
 * @ORM\Table(name="compras_pedido")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\PedidoRepository")
 */
class Pedido {
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
     * Estados: NUEVO - PENDIENTE - ENTREGADO - CANCELADO
     */
    protected $estado;

    /**
     * @var string $descuentos
     * @ORM\Column(name="descuentos", type="string", nullable=true)
     */
    protected $descuentos;

    /**
     * @var integer $montoDescuento
     * @ORM\Column(name="monto_descuento", type="decimal", precision=20, scale=2, nullable=true )
     */
    protected $montoDescuento;

    /**
     * @var integer $montoIva
     * @ORM\Column(name="monto_iva", type="decimal", precision=20, scale=2, nullable=true )
     */
    protected $montoIva;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Proveedor", inversedBy="pedidos")
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     * @ORM\OrderBy({"nombre" = "ASC"})
     */
    protected $proveedor;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Transporte")
     * @ORM\JoinColumn(name="transporte_id", referencedColumnName="id")
     * @ORM\OrderBy({"nombre" = "ASC"})
     */
    protected $transporte;

    /**
     * @var string $formaPago
     * @ORM\Column(name="forma_pago", type="string", nullable=true)
     */
    protected $formaPago;

    /**
     * @ORM\OneToMany(targetEntity="ComprasBundle\Entity\PedidoDetalle", mappedBy="pedido",cascade={"persist", "remove"})
     */
    protected $detalles;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="calificacion_proveedor_id", referencedColumnName="id")
     * */
    protected $calificacionProveedor;

    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

    /**
     * @ORM\Column(name="obs_recepcion", type="text", nullable=true)
     */
    protected $obsRecepcion;

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
    public function __construct() {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fechaPedido = new \DateTime();
        $this->estado = 'NUEVO';
        $this->descuentos = '';
        $this->montoDescuento = 0;
        $this->montoIva = 0;
    }

    public function __clone() {
        $this->id = null;
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
     * Set prefijoNro
     *
     * @param string $prefijoNro
     * @return Pedido
     */
    public function setPrefijoNro($prefijoNro) {
        $this->prefijoNro = $prefijoNro;

        return $this;
    }

    /**
     * Get prefijoNro
     *
     * @return string
     */
    public function getPrefijoNro() {
        return $this->prefijoNro;
    }

    /**
     * Set pedidoNro
     *
     * @param string $pedidoNro
     * @return Pedido
     */
    public function setPedidoNro($pedidoNro) {
        $this->pedidoNro = $pedidoNro;

        return $this;
    }

    /**
     * Get pedidoNro
     *
     * @return string
     */
    public function getPedidoNro() {
        return $this->pedidoNro;
    }

    /**
     * Get nroPedido
     *
     * @return string
     */
    public function getNroPedido() {
        return $this->prefijoNro . '-' . $this->pedidoNro;
    }

    /**
     * Set fechaEntrega
     *
     * @param \DateTime $fechaEntrega
     * @return Pedido
     */
    public function setFechaEntrega($fechaEntrega) {
        $this->fechaEntrega = $fechaEntrega;

        return $this;
    }

    /**
     * Get fechaEntrega
     *
     * @return \DateTime
     */
    public function getFechaEntrega() {
        return $this->fechaEntrega;
    }

    /**
     * Set fechaPedido
     * @param \DateTime $fechaPedido
     * @return Pedido
     */
    public function setFechaPedido($fechaPedido) {
        $this->fechaPedido = $fechaPedido;

        return $this;
    }

    /**
     * Get fechaPedido
     *
     * @return \DateTime
     */
    public function getFechaPedido() {
        return $this->fechaPedido;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Pedido
     */
    public function setEstado($estado) {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado() {
        return $this->estado;
    }

    /**
     * Set descuentos
     *
     * @param string $descuentos
     * @return Pedido
     */
    public function setDescuentos($descuentos) {
        $this->descuentos = $descuentos;

        return $this;
    }

    /**
     * Get descuentos
     *
     * @return string
     */
    public function getDescuentos() {
        return $this->descuentos;
    }

    /**
     * Set montoDescuento
     *
     * @param string $montoDescuento
     * @return PedidoDetalle
     */
    public function setMontoDescuento($montoDescuento) {
        $this->montoDescuento = $montoDescuento;

        return $this;
    }

    /**
     * Get montoDescuento
     *
     * @return string
     */
    public function getMontoDescuento() {
        return $this->montoDescuento;
    }

    /**
     * Set montoIva
     *
     * @param string $montoIva
     * @return PedidoDetalle
     */
    public function setMontoIva($montoIva) {
        $this->montoIva = $montoIva;

        return $this;
    }

    /**
     * Get montoIva
     *
     * @return string
     */
    public function getMontoIva() {
        return $this->montoIva;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Pedido
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Pedido
     */
    public function setUpdated($updated) {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Set proveedor
     *
     * @param \ComprasBundle\Entity\Proveedor $proveedor
     * @return Pedido
     */
    public function setProveedor(\ComprasBundle\Entity\Proveedor $proveedor = null) {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \ComprasBundle\Entity\Proveedor
     */
    public function getProveedor() {
        return $this->proveedor;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Pedido
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return Pedido
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    /**
     * Add detalles
     *
     * @param \ComprasBundle\Entity\PedidoDetalle $detalles
     * @return Pedido
     */
    public function addDetalle(\ComprasBundle\Entity\PedidoDetalle $detalles) {
        $detalles->setPedido($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \ComprasBundle\Entity\PedidoDetalle $detalles
     */
    public function removeDetalle(\ComprasBundle\Entity\PedidoDetalle $detalles) {
        $this->detalles->removeElement($detalles);
    }

    /**
     * Get detalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalles() {
        return $this->detalles;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Pedido
     */
    public function setUnidadNegocio(\ConfigBundle\Entity\UnidadNegocio $unidadNegocio = null) {
        $this->unidadNegocio = $unidadNegocio;

        return $this;
    }

    /**
     * Get unidadNegocio
     *
     * @return \ConfigBundle\Entity\UnidadNegocio
     */
    public function getUnidadNegocio() {
        return $this->unidadNegocio;
    }

    /**
     *  Calculos
     */

    /**
     * Calcula monto total del pedido
     */
    public function getCostoTotal() {
        $tot = 0;
        //PEDIDO RECIBIDO - calcular en base a recibido - sino calcular en base a cantidad
        foreach ($this->detalles as $detalle) {
            $cantidad = ($this->estado == 'RECIBIDO') ? $detalle->getEntregado() : $detalle->getCantidad();
            $tot += ($detalle->getPrecio() * $cantidad);
        }
        return $tot;
    }

    // Costo total del item del pedido original
    public function getCostoTotalOriginal() {
        $tot = 0;
        foreach ($this->detalles as $detalle) {
            $tot += ($detalle->getPrecio() * $detalle->getCantidad());
        }
        return $tot;
    }

    public function getDescuentosTxt() {
        $descuentos = explode(',', $this->getDescuentos());
        $txt = '';
        if ($descuentos) {
            foreach ($descuentos as $desc) {
                $txt = ($txt ? $txt . ' + ' : '') . $desc . '%';
            }
        }
        return $txt === '%' ? '0.00%' : $txt;
    }

    public function getMontoTotal() {
        return $this->getCostoTotal() - $this->getMontoDescuento() + $this->getMontoIva();
    }

    /*   public function getPendientes(){
      $cant = 0;
      foreach ($this->detalles as $detalle) {
      $cant = $cant + $detalle->getPendientes();
      }
      return $cant;
      }
     */

    /**
     * Set deposito
     *
     * @param \AppBundle\Entity\Deposito $deposito
     * @return Pedido
     */
    public function setDeposito(\AppBundle\Entity\Deposito $deposito = null) {
        $this->deposito = $deposito;

        return $this;
    }

    /**
     * Get deposito
     *
     * @return \AppBundle\Entity\Deposito
     */
    public function getDeposito() {
        return $this->deposito;
    }

    /**
     * Set obsRecepcion
     *
     * @param string $obsRecepcion
     * @return Pedido
     */
    public function setObsRecepcion($obsRecepcion) {
        $this->obsRecepcion = $obsRecepcion;

        return $this;
    }

    /**
     * Get obsRecepcion
     *
     * @return string
     */
    public function getObsRecepcion() {
        return $this->obsRecepcion;
    }

    /**
     * Set calificacion_proveedor
     *
     * @param \ConfigBundle\Entity\Parametro $calificacionProveedor
     * @return Pedido
     */
    public function setCalificacionProveedor(\ConfigBundle\Entity\Parametro $calificacionProveedor = null) {
        $this->calificacionProveedor = $calificacionProveedor;

        return $this;
    }

    /**
     * Get calificacion_proveedor
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getCalificacionProveedor() {
        return $this->calificacionProveedor;
    }

    /**
     * Set formaPago
     *
     * @param string $formaPago
     * @return Pedido
     */
    public function setFormaPago($formaPago) {
        $this->formaPago = $formaPago;

        return $this;
    }

    /**
     * Get formaPago
     *
     * @return string
     */
    public function getFormaPago() {
        return $this->formaPago;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Pedido
     */
    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones() {
        return $this->observaciones;
    }

    /**
     * Set transporte
     *
     * @param \ConfigBundle\Entity\Transporte $transporte
     * @return Pedido
     */
    public function setTransporte(\ConfigBundle\Entity\Transporte $transporte = null) {
        $this->transporte = $transporte;

        return $this;
    }

    /**
     * Get transporte
     *
     * @return \ConfigBundle\Entity\Transporte
     */
    public function getTransporte() {
        return $this->transporte;
    }

}