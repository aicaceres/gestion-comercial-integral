<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\Venta
 * @ORM\Table(name="ventas_venta")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\VentaRepository")
 */
class Venta {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $nroOperacion
     * @ORM\Column(name="nro_operacion", type="integer")
     */
    protected $nroOperacion = '';

    /**
     * @var datetime $fechaVenta
     * @ORM\Column(name="fecha_venta", type="datetime", nullable=false)
     */
    protected $fechaVenta;
    /**
     * Estados: PENDIENTE - COBRADO - FACTURADO - ANULADO
     */

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado = 'PENDIENTE';

    /**
     * @ORM\Column(name="descuenta_stock", type="boolean",nullable=true)
     */
    protected $descuentaStock = true;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="Ventas")
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
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Moneda")
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     */
    protected $moneda;

    /**
     * @var string $cotizacion
     * @ORM\Column(name="cotizacion", type="decimal", scale=2, nullable=true)
     */
    protected $cotizacion = 0;

    /**
     * @var integer $descuentoRecargo
     * @ORM\Column(name="descuentoRecargo", type="decimal", scale=2,nullable=true )
     */
    protected $descuentoRecargo;

    /**
     * @ORM\Column(name="concepto", type="text", nullable=true)
     */
    protected $concepto;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Transporte")
     * @ORM\JoinColumn(name="transporte_id", referencedColumnName="id")
     */
    protected $transporte;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\VentaDetalle", mappedBy="venta",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $detalles;

    /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\Cobro", mappedBy="venta")
     */
    protected $cobro;

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

    public function __clone() {
        $this->id = null;
    }

    public function __toString() {
        return strval($this->getNroOperacion());
    }

    /**
     *  TOTALIZADOS DE LA OPERACION
     */
    public function getSubTotal() {
        $total = 0;
        foreach ($this->detalles as $item) {
            $total = $total + $item->getTotalItem();
        }
        return $total;
    }

    public function getTotalDescuentoRecargo() {
        $total = 0;
        $categIva = $this->getCliente()->getCategoriaIva()->getNombre();
        if ($categIva == 'I' || $categIva == 'M') {
            // suma de descuentos x item
            foreach ($this->detalles as $item) {
                $total = $total + $item->getTotalDtoRecItem();
            }
            $total = $total / $this->getCotizacion();
        }
        else {
            // descuento sobre el subtotal
            $total = $this->getSubTotal() * ( $this->getDescuentoRecargo() / 100 );
        }
        return round(($total), 3);
    }

    public function getTotalIva() {
        $total = 0;
        $categIva = $this->getCliente()->getCategoriaIva()->getNombre();
        if ($categIva == 'E') {
            return 0;
        }
        else {
            foreach ($this->detalles as $item) {
                $total = $total + $item->getTotalIvaItem();
            }
            return round(($total / $this->getCotizacion()), 3);
        }
    }

    public function getTotalIibb($iibbPercent = 3.5) {
        $iibbPercent = $this->getCliente()->getPercepcionRentas();
        $monto = $this->getSubTotal() + $this->getTotalDescuentoRecargo();
        return $monto * $iibbPercent / 100;
    }

    public function getMontoTotal() {
        $categIva = $this->getCliente()->getCategoriaIva()->getNombre();
        $retRentas = $this->getCliente()->getCategoriaRentas() ? $this->getCliente()->getCategoriaRentas()->getRetencion() : null;
        if ($categIva == 'I' || $categIva == 'M') {
            // total con iva e iibb
            $total = $this->getSubTotal() + $this->getTotalDescuentoRecargo() + $this->getTotalIva();
            if ($categIva == 'I' && $retRentas > 0) {
                $total = $total + $this->getTotalIibb();
            }
        }
        else {
            // subtotal +/- descuentoRecargo
            $descRec = $this->getSubTotal() * ( $this->getDescuentoRecargo() / 100 );
            $total = $this->getSubTotal() + $descRec;
        }
        return round($total, 3);
    }

    /**
     *  FIN TOTALIZADOS
     */

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fechaVenta
     *
     * @param \DateTime $fechaVenta
     * @return Venta
     */
    public function setFechaVenta($fechaVenta) {
        $this->fechaVenta = $fechaVenta;

        return $this;
    }

    /**
     * Get fechaVenta
     *
     * @return \DateTime
     */
    public function getFechaVenta() {
        return $this->fechaVenta;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Venta
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return Venta
     */
    public function setCliente(\VentasBundle\Entity\Cliente $cliente = null) {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get cliente
     *
     * @return \VentasBundle\Entity\Cliente
     */
    public function getCliente() {
        return $this->cliente;
    }

    /**
     * Set nombreCliente
     *
     * @param string $nombreCliente
     * @return Cliente
     */
    public function setNombreCliente($nombreCliente) {
        $this->nombreCliente = $nombreCliente;

        return $this;
    }

    /**
     * Get nombreCliente
     *
     * @return string
     */
    public function getNombreCliente() {
        return $this->nombreCliente;
    }

    /**
     * Set formaPago
     *
     * @param \ConfigBundle\Entity\FormaPago $formaPago
     * @return Venta
     */
    public function setFormaPago(\ConfigBundle\Entity\FormaPago $formaPago = null) {
        $this->formaPago = $formaPago;

        return $this;
    }

    /**
     * Get formaPago
     *
     * @return \ConfigBundle\Entity\FormaPago
     */
    public function getFormaPago() {
        return $this->formaPago;
    }

    /**
     * Set precioLista
     *
     * @param \AppBundle\Entity\PrecioLista $precioLista
     * @return Venta
     */
    public function setPrecioLista(\AppBundle\Entity\PrecioLista $precioLista = null) {
        $this->precioLista = $precioLista;

        return $this;
    }

    /**
     * Get precioLista
     *
     * @return \AppBundle\Entity\PrecioLista
     */
    public function getPrecioLista() {
        return $this->precioLista;
    }

    /**
     * Set transporte
     *
     * @param \ConfigBundle\Entity\Transporte $transporte
     * @return Venta
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

    /**
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return Venta
     */
    public function setMoneda(\ConfigBundle\Entity\Moneda $moneda = null) {
        $this->moneda = $moneda;

        return $this;
    }

    /**
     * Get moneda
     *
     * @return \ConfigBundle\Entity\Moneda
     */
    public function getMoneda() {
        return $this->moneda;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->estado = 'PENDIENTE';
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add detalles
     *
     * @param \VentasBundle\Entity\VentaDetalle $detalles
     * @return Venta
     */
    public function addDetalle(\VentasBundle\Entity\VentaDetalle $detalles) {
        $detalles->setVenta($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \VentasBundle\Entity\VentaDetalle $detalles
     */
    public function removeDetalle(\VentasBundle\Entity\VentaDetalle $detalles) {
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
     * Set nroOperacion
     *
     * @param integer $nroOperacion
     * @return Venta
     */
    public function setNroOperacion($nroOperacion) {
        $this->nroOperacion = $nroOperacion;

        return $this;
    }

    /**
     * Get nroOperacion
     *
     * @return integer
     */
    public function getNroOperacion() {
        return $this->nroOperacion;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Venta
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
     * @return Venta
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Venta
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
     * @return Venta
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
     * Set deposito
     *
     * @param \AppBundle\Entity\Deposito $deposito
     * @return Venta
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
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Venta
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
     * Set cotizacion
     *
     * @param string $cotizacion
     * @return Venta
     */
    public function setCotizacion($cotizacion) {
        $this->cotizacion = $cotizacion;

        return $this;
    }

    /**
     * Get cotizacion
     *
     * @return string
     */
    public function getCotizacion() {
        return $this->cotizacion;
    }

    /**
     * Set descuentoRecargo
     *
     * @param string $descuentoRecargo
     * @return Venta
     */
    public function setDescuentoRecargo($descuentoRecargo) {
        $this->descuentoRecargo = $descuentoRecargo;

        return $this;
    }

    /**
     * Get descuentoRecargo
     *
     * @return string
     */
    public function getDescuentoRecargo() {
        return $this->descuentoRecargo;
    }

    /**
     * Set cobro
     *
     * @param \VentasBundle\Entity\Cobro $cobro
     * @return Venta
     */
    public function setCobro(\VentasBundle\Entity\Cobro $cobro = null) {
        $this->cobro = $cobro;

        return $this;
    }

    /**
     * Get cobro
     *
     * @return \VentasBundle\Entity\Cobro
     */
    public function getCobro() {
        return $this->cobro;
    }

    /**
     * Set concepto
     *
     * @param string $concepto
     * @return Venta
     */
    public function setConcepto($concepto) {
        $this->concepto = $concepto;

        return $this;
    }

    /**
     * Get concepto
     *
     * @return string
     */
    public function getConcepto() {
        return $this->concepto;
    }

    /**
     * Set descuentaStock
     *
     * @param boolean $descuentaStock
     * @return Venta
     */
    public function setDescuentaStock($descuentaStock) {
        $this->descuentaStock = $descuentaStock;

        return $this;
    }

    /**
     * Get descuentaStock
     *
     * @return boolean
     */
    public function getDescuentaStock() {
        return $this->descuentaStock;
    }

}