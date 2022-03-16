<?php

namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ComprasBundle\Entity\Factura
 * @ORM\Table(name="compras_factura")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\FacturaRepository")
 * @UniqueEntity(
 *     fields={"unidadNegocio","proveedor","tipoFactura","nroComprobante"},
 *     errorPath="nroComprobante",
 *     message="El N° de Comprobante ya existe para este Proveedor en esta Unidad de Negocio."
 * )
 */
class Factura {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $tipoFactura
     * @ORM\Column(name="tipo_factura", type="string", length=1)
     */
    protected $tipoFactura;

    /**
     * @var integer $prefijoNro
     * @ORM\Column(name="prefijo_nro", type="string", length=3)
     */
    protected $prefijoNro;

    /**
     * @var integer $facturaNro
     * @ORM\Column(name="factura_nro", type="string", length=8)
     */
    protected $facturaNro;

    /**
     * @var integer $nroComprobante
     * @ORM\Column(name="nro_comprobante", type="string", length=30,nullable=true)
     */
    protected $nroComprobante;

    /**
     * @var datetime $fechaFactura
     * @ORM\Column(name="fecha_factura", type="datetime", nullable=false)
     */
    private $fechaFactura;

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado;

    /**
     * @var integer $saldo
     * @ORM\Column(name="saldo", type="decimal", scale=3 )
     */
    protected $saldo;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Proveedor", inversedBy="facturasCompra")
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     */
    protected $proveedor;

    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="string", nullable=true)
     */
    protected $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="condicion_venta_id", referencedColumnName="id")
     * */
    protected $condicion_venta;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrecioLista")
     * @ORM\JoinColumn(name="precio_lista_id", referencedColumnName="id")
     */
    protected $precioLista;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\Column(name="modifica_stock", type="boolean",nullable=true)
     */
    protected $modificaStock = false;

// TOTALES DE LA FACTURA

    /**
     * @var integer $subtotal
     * @ORM\Column(name="subtotal", type="decimal", scale=3,nullable=true )
     */
    protected $subtotal;

    /**
     * @var integer $subtotalNeto
     * @ORM\Column(name="subtotal_neto", type="decimal", scale=3,nullable=true )
     */
    protected $subtotalNeto;

    /**
     * @var integer $impuestoInterno
     * @ORM\Column(name="impuesto_interno", type="decimal", scale=3,nullable=true )
     */
    protected $impuestoInterno;

    /**
     * @var integer $iva
     * @ORM\Column(name="iva", type="decimal", scale=3,nullable=true )
     */
    protected $iva;

    /**
     * @var integer $percepcionIva
     * @ORM\Column(name="percepcion_iva", type="decimal", scale=3,nullable=true )
     */
    protected $percepcionIva;

    /**
     * @var integer $percepcionDgr
     * @ORM\Column(name="percepcion_dgr", type="decimal", scale=3,nullable=true )
     */
    protected $percepcionDgr;

    /**
     * @var integer $percepcionMunicipal
     * @ORM\Column(name="percepcion_municipal", type="decimal", scale=3,nullable=true )
     */
    protected $percepcionMunicipal;

    /**
     * @var integer $totalBonificado
     * @ORM\Column(name="total_bonificado", type="decimal", scale=3,nullable=true )
     */
    protected $totalBonificado;

    /**
     * @var integer $tmc
     * @ORM\Column(name="tmc", type="decimal", scale=3,nullable=true )
     */
    protected $tmc;

    /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=3 )
     */
    protected $total;

    /**
     * @ORM\OneToMany(targetEntity="ComprasBundle\Entity\FacturaDetalle", mappedBy="factura",cascade={"persist", "remove"})
     */
    protected $detalles;

    /*
     * DATOS PARA AFIP
     */

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\AfipComprobante")
     * @ORM\JoinColumn(name="afip_comprobante_id", referencedColumnName="id")
     * */
    protected $afipComprobante;

    /**
     * @var integer $afipPuntoVenta
     * @ORM\Column(name="afip_punto_venta", type="string", length=5)
     */
    protected $afipPuntoVenta;

    /**
     * @var integer $afipNroComprobante
     * @ORM\Column(name="afip_nro_comprobante", type="string", length=20)
     */
    protected $afipNroComprobante;

    /*
     * ---------------
     */

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

    public function getTotalsinBonificacion() {
        return $this->getSubtotalNeto() + $this->getIva() + $this->getPercepcionIva() +
                $this->getPercepcionDgr() + $this->getPercepcionMunicipal() +
                $this->getImpuestoInterno() + $this->getTmc();
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set tipoFactura
     *
     * @param string $tipoFactura
     * @return Factura
     */
    public function setTipoFactura($tipoFactura) {
        $this->tipoFactura = $tipoFactura;

        return $this;
    }

    /**
     * Get tipoFactura
     *
     * @return string
     */
    public function getTipoFactura() {
        return $this->tipoFactura;
    }

    /**
     * Set prefijoNro
     *
     * @param string $prefijoNro
     * @return Factura
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
     * Set facturaNro
     *
     * @param string $facturaNro
     * @return Factura
     */
    public function setFacturaNro($facturaNro) {
        $this->facturaNro = $facturaNro;

        return $this;
    }

    /**
     * Get facturaNro
     *
     * @return string
     */
    public function getFacturaNro() {
        return $this->facturaNro;
    }

    /**
     * Set nroComprobante
     *
     * @param string $nroComprobante
     * @return Factura
     */
    public function setNroComprobante($nroComprobante) {
        $this->nroComprobante = $nroComprobante;

        return $this;
    }

    /**
     * Get nroComprobante
     *
     * @return string
     */
    public function getNroComprobante() {
        return $this->nroComprobante;
    }

    /**
     * Set fechaFactura
     *
     * @param \DateTime $fechaFactura
     * @return Factura
     */
    public function setFechaFactura($fechaFactura) {
        $this->fechaFactura = $fechaFactura;

        return $this;
    }

    /**
     * Get fechaFactura
     *
     * @return \DateTime
     */
    public function getFechaFactura() {
        return $this->fechaFactura;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Factura
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
     * Set saldo
     *
     * @param string $saldo
     * @return Factura
     */
    public function setSaldo($saldo) {
        $this->saldo = $saldo;

        return $this;
    }

    /**
     * Get saldo
     *
     * @return string
     */
    public function getSaldo() {
        return $this->saldo;
    }

    /**
     * Set subtotal
     *
     * @param string $subtotal
     * @return Factura
     */
    public function setSubtotal($subtotal) {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * Get subtotal
     *
     * @return string
     */
    public function getSubtotal() {
        return $this->subtotal;
    }

    /**
     * Set subtotalNeto
     *
     * @param string $subtotalNeto
     * @return Factura
     */
    public function setSubtotalNeto($subtotalNeto) {
        $this->subtotalNeto = $subtotalNeto;

        return $this;
    }

    /**
     * Get subtotalNeto
     *
     * @return string
     */
    public function getSubtotalNeto() {
        return $this->subtotalNeto;
    }

    /**
     * Set impuestoInterno
     *
     * @param string $impuestoInterno
     * @return Factura
     */
    public function setImpuestoInterno($impuestoInterno) {
        $this->impuestoInterno = $impuestoInterno;

        return $this;
    }

    /**
     * Get impuestoInterno
     *
     * @return string
     */
    public function getImpuestoInterno() {
        return $this->impuestoInterno;
    }

    /**
     * Set iva
     *
     * @param string $iva
     * @return Factura
     */
    public function setIva($iva) {
        $this->iva = $iva;

        return $this;
    }

    /**
     * Get iva
     *
     * @return string
     */
    public function getIva() {
        return $this->iva;
    }

    /**
     * Set percepcionIva
     *
     * @param string $percepcionIva
     * @return Factura
     */
    public function setPercepcionIva($percepcionIva) {
        $this->percepcionIva = $percepcionIva;

        return $this;
    }

    /**
     * Get percepcionIva
     *
     * @return string
     */
    public function getPercepcionIva() {
        return $this->percepcionIva;
    }

    /**
     * Set percepcionDgr
     *
     * @param string $percepcionDgr
     * @return Factura
     */
    public function setPercepcionDgr($percepcionDgr) {
        $this->percepcionDgr = $percepcionDgr;

        return $this;
    }

    /**
     * Get percepcionDgr
     *
     * @return string
     */
    public function getPercepcionDgr() {
        return $this->percepcionDgr;
    }

    /**
     * Set percepcionMunicipal
     *
     * @param string $percepcionMunicipal
     * @return Factura
     */
    public function setPercepcionMunicipal($percepcionMunicipal) {
        $this->percepcionMunicipal = $percepcionMunicipal;

        return $this;
    }

    /**
     * Get percepcionMunicipal
     *
     * @return string
     */
    public function getPercepcionMunicipal() {
        return $this->percepcionMunicipal;
    }

    /**
     * Set totalBonificado
     *
     * @param string $totalBonificado
     * @return Factura
     */
    public function setTotalBonificado($totalBonificado) {
        $this->totalBonificado = $totalBonificado;

        return $this;
    }

    /**
     * Get totalBonificado
     *
     * @return string
     */
    public function getTotalBonificado() {
        return $this->totalBonificado;
    }

    /**
     * Set tmc
     *
     * @param string $tmc
     * @return Factura
     */
    public function setTmc($tmc) {
        $this->tmc = $tmc;

        return $this;
    }

    /**
     * Get tmc
     *
     * @return string
     */
    public function getTmc() {
        return $this->tmc;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return Factura
     */
    public function setTotal($total) {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal() {
        return $this->total;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Factura
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
     * @return Factura
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
     * @return Factura
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
     * Set condicion_venta
     *
     * @param \ConfigBundle\Entity\Parametro $condicionVenta
     * @return Factura
     */
    public function setCondicionVenta(\ConfigBundle\Entity\Parametro $condicionVenta = null) {
        $this->condicion_venta = $condicionVenta;

        return $this;
    }

    /**
     * Get condicion_venta
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getCondicionVenta() {
        return $this->condicion_venta;
    }

    /**
     * Set precioLista
     *
     * @param \AppBundle\Entity\PrecioLista $precioLista
     * @return Factura
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
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Factura
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
     * Add detalles
     *
     * @param \ComprasBundle\Entity\FacturaDetalle $detalles
     * @return Factura
     */
    public function addDetalle(\ComprasBundle\Entity\FacturaDetalle $detalles) {
        $detalles->setFactura($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \ComprasBundle\Entity\FacturaDetalle $detalles
     */
    public function removeDetalle(\ComprasBundle\Entity\FacturaDetalle $detalles) {
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Factura
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
     * @return Factura
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

    // Funciones adicionales

    /**
     * Get nroFactura
     * @return integer
     */
    public function getNroFactura() {
        return $this->prefijoNro . '-' . $this->facturaNro;
    }

    /**
     * Get selectFactura
     * @return integer
     */
    public function getSelectFactura() {
        //return $this->tipoFactura.$this->prefijoNro.'-'.$this->facturaNro.' ($'.$this->total.')';
        return $this->nroComprobante . ' ($' . $this->total . ')';
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Factura
     */
    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion() {
        return $this->descripcion;
    }

    /**
     * Set afipPuntoVenta
     *
     * @param string $afipPuntoVenta
     * @return Factura
     */
    public function setAfipPuntoVenta($afipPuntoVenta) {
        $this->afipPuntoVenta = $afipPuntoVenta;

        return $this;
    }

    /**
     * Get afipPuntoVenta
     *
     * @return string
     */
    public function getAfipPuntoVenta() {
        return $this->afipPuntoVenta;
    }

    /**
     * Set afipNroComprobante
     *
     * @param string $afipNroComprobante
     * @return Factura
     */
    public function setAfipNroComprobante($afipNroComprobante) {
        $this->afipNroComprobante = $afipNroComprobante;

        return $this;
    }

    /**
     * Get afipNroComprobante
     *
     * @return string
     */
    public function getAfipNroComprobante() {
        return $this->afipNroComprobante;
    }

    /**
     * Set afipComprobante
     *
     * @param \ConfigBundle\Entity\AfipComprobante $afipComprobante
     * @return Factura
     */
    public function setAfipComprobante(\ConfigBundle\Entity\AfipComprobante $afipComprobante = null) {
        $this->afipComprobante = $afipComprobante;

        return $this;
    }

    /**
     * Get afipComprobante
     *
     * @return \ConfigBundle\Entity\AfipComprobante
     */
    public function getAfipComprobante() {
        return $this->afipComprobante;
    }

    public function getNuevoNroComprobante() {
        if ($this->getAfipPuntoVenta() && $this->getAfipNroComprobante()) {
            return $this->getAfipPuntoVenta() . '-' . $this->getAfipNroComprobante();
        }
        else {
            return $this->getNroComprobante();
        }
    }

    public function getImpuestoTotal() {
        return $this->getIva();
    }


    /**
     * Set modificaStock
     *
     * @param boolean $modificaStock
     * @return Factura
     */
    public function setModificaStock($modificaStock)
    {
        $this->modificaStock = $modificaStock;

        return $this;
    }

    /**
     * Get modificaStock
     *
     * @return boolean 
     */
    public function getModificaStock()
    {
        return $this->modificaStock;
    }
}
