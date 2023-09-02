<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\Cobro
 * @ORM\Table(name="ventas_cobro")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\CobroRepository")
 */
class Cobro {
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
     * @var datetime $fechaCobro
     * @ORM\Column(name="fecha_cobro", type="datetime", nullable=false)
     */
    protected $fechaCobro;
    /**
     * Estados: PENDIENTE - FINALIZADO - ANULADO
     */

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado = 'PENDIENTE';

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="cobros")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     */
    protected $cliente;

    /**
     * @var string $nombreCliente
     * @ORM\Column(name="nombre_cliente", type="string", nullable=true)
     */
    protected $nombreCliente;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="tipo_documento_cliente", referencedColumnName="id")
     * */
    protected $tipoDocumentoCliente;

    /**
     * @var string $nroDocumentoCliente
     * @ORM\Column(name="nro_documento_cliente", type="string", length=13, nullable=true)
     */
    protected $nroDocumentoCliente;

    /**
     * @var string $direccionCliente
     * @ORM\Column(name="direccion_cliente", type="string", nullable=true)
     */
    protected $direccionCliente;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\FormaPago")
     * @ORM\JoinColumn(name="forma_pago_id", referencedColumnName="id")
     * */
    protected $formaPago;

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
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\CobroDetalle", mappedBy="cobro",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $detalles;

    /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\Venta", inversedBy="cobro")
     * @ORM\JoinColumn(name="venta_id", referencedColumnName="id")
     */
    protected $venta;

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
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\FacturaElectronica", mappedBy="cobro", cascade={"persist"})
     */
    protected $facturaElectronica;

    public function getPagoTxt() {
        return ($this->getFormaPago()->getCuentaCorriente()) ? '' : $this->getFormaPago()->getNombre();
    }

    //* datos para la impresion de la factura electronica
    public function getTextoPagosParaFactura() {
        $txt = '';
        foreach ($this->detalles as $det) {
            $aux = ($txt) ? ' - ' : '';
            $monto = $det->getMoneda()->getSimbolo() . ' ' . $det->getImporte();
            switch ($det->getTipoPago()) {
                case 'EFECTIVO':
                    $txt = $txt . $aux . 'EFECTIVO: ' . $monto;
                    break;
                case 'CHEQUE':
                    $txt = $txt . $aux . 'CHEQUE: ' . $monto;
                    break;
                case 'TARJETA':
                    $txt = $txt . $aux . $det->getDatosTarjeta()->getTarjeta()->getNombre() . ': ' . $monto;
                    break;
            }
        }
        return $txt;
    }

    public function getNombreClienteTxt() {
        return $this->getNombreCliente() ? $this->getNombreCliente() : $this->getCliente()->getNombre();
    }

    public function getSubtotal() {
        return $this->getVenta()->getSubtotal();
    }

    public function getRefVenta() {
        return "Ref. #" . $this->getVenta()->getNroOperacion();
    }

    public function getDescuentoRecargo() {
        return $this->getVenta()->getDescuentoRecargo();
    }

    public function getTotalDescuentoRecargo() {
        return $this->getVenta()->getTotalDescuentoRecargo();
    }

    public function getTotalIva() {
        return $this->getVenta()->getTotalIva();
    }

    public function getTotalIibb() {
        return $this->getVenta()->getTotalIibb();
    }

    public function getVentaDetalles() {
        return $this->getVenta()->getDetalles();
    }

    public function getConcepto() {
        return $this->getVenta()->getConcepto();
    }

    public function getMontoTotal() {
        return $this->getVenta()->getMontoTotal();
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
     * Set nroOperacion
     *
     * @param integer $nroOperacion
     * @return Cobro
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
     * Set fechaCobro
     *
     * @param \DateTime $fechaCobro
     * @return Cobro
     */
    public function setFechaCobro($fechaCobro) {
        $this->fechaCobro = $fechaCobro;

        return $this;
    }

    /**
     * Get fechaCobro
     *
     * @return \DateTime
     */
    public function getFechaCobro() {
        return $this->fechaCobro;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Cobro
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
     * Set created
     *
     * @param \DateTime $created
     * @return Cobro
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
     * @return Cobro
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
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Cobro
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return Cobro
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
     * Set formaPago
     *
     * @param \ConfigBundle\Entity\FormaPago $formaPago
     * @return Cobro
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
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return Cobro
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
     * Set venta
     *
     * @param \VentasBundle\Entity\Venta $venta
     * @return Cobro
     */
    public function setVenta(\VentasBundle\Entity\Venta $venta = null) {
        $this->venta = $venta;

        return $this;
    }

    /**
     * Get venta
     *
     * @return \VentasBundle\Entity\Venta
     */
    public function getVenta() {
        return $this->venta;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Cobro
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
     * @return Cobro
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
     * Set nombreCliente
     *
     * @param string $nombreCliente
     * @return Cobro
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
     * Set nroDocumentoCliente
     *
     * @param string $nroDocumentoCliente
     * @return Cobro
     */
    public function setNroDocumentoCliente($nroDocumentoCliente) {
        $this->nroDocumentoCliente = $nroDocumentoCliente;

        return $this;
    }

    /**
     * Get nroDocumentoCliente
     *
     * @return string
     */
    public function getNroDocumentoCliente() {
        return $this->nroDocumentoCliente;
    }

    /**
     * Set direccionCliente
     *
     * @param string $direccionCliente
     * @return Cobro
     */
    public function setDireccionCliente($direccionCliente) {
        $this->direccionCliente = $direccionCliente;

        return $this;
    }

    /**
     * Get direccionCliente
     *
     * @return string
     */
    public function getDireccionCliente() {
        return $this->direccionCliente;
    }

    /**
     * Set cotizacion
     *
     * @param string $cotizacion
     * @return Cobro
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
     * Set facturaElectronica
     *
     * @param \VentasBundle\Entity\FacturaElectronica $facturaElectronica
     * @return Cobro
     */
    public function setFacturaElectronica(\VentasBundle\Entity\FacturaElectronica $facturaElectronica = null) {
        $this->facturaElectronica = $facturaElectronica;

        return $this;
    }

    /**
     * Get facturaElectronica
     *
     * @return \VentasBundle\Entity\FacturaElectronica
     */
    public function getFacturaElectronica() {
        return $this->facturaElectronica;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add detalles
     *
     * @param \VentasBundle\Entity\CobroDetalle $detalles
     * @return Cobro
     */
    public function addDetalle(\VentasBundle\Entity\CobroDetalle $detalles) {
        $detalles->setCobro($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \VentasBundle\Entity\CobroDetalle $detalles
     */
    public function removeDetalle(\VentasBundle\Entity\CobroDetalle $detalles) {
        $detalles->setCobro($this);
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
     * Set tipoDocumentoCliente
     *
     * @param \ConfigBundle\Entity\Parametro $tipoDocumentoCliente
     * @return Cobro
     */
    public function setTipoDocumentoCliente(\ConfigBundle\Entity\Parametro $tipoDocumentoCliente = null) {
        $this->tipoDocumentoCliente = $tipoDocumentoCliente;

        return $this;
    }

    /**
     * Get tipoDocumentoCliente
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getTipoDocumentoCliente() {
        return $this->tipoDocumentoCliente;
    }

}