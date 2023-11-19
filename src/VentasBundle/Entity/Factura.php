<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\Factura
 * @ORM\Table(name="ventas_factura")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\FacturaRepository")
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
     * @var integer $nroFactura
     * @ORM\Column(name="nro_factura", type="string", length=30)
     */
    protected $nroFactura;

    /**
     * @var datetime $fechaFactura
     * @ORM\Column(name="fecha_factura", type="datetime", nullable=false)
     */
    private $fechaFactura;

    /**
     * Estados: PENDIENTE - PAGO PARCIAL - PAGADO - ANULADO
     */

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado;

    /**
     * @var integer $iva
     * @ORM\Column(name="iva", type="decimal", scale=2 )
     */
    protected $iva;

    /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=3 )
     */
    protected $total;

    /**
     * @var integer $saldo
     * @ORM\Column(name="saldo", type="decimal", scale=3 )
     */
    protected $saldo;

    /**
     * @var datetime $fechaImpresion
     * @ORM\Column(name="fecha_impresion", type="datetime", nullable=true)
     */
    private $fechaImpresion;

    /**
     * @var integer $impresiones
     * @ORM\Column(name="impresiones", type="integer", nullable=true )
     */
    protected $impresiones;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="facturasVenta")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     */
    protected $cliente;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="condicion_pago_id", referencedColumnName="id")
     * */
    protected $condicionPago;

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
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\FacturaDetalle", mappedBy="factura",cascade={"persist", "remove"})
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
        $this->impresiones = 0;
        $this->fechaFactura = new \DateTime();
        $this->tipoFactura = 'A';
        $this->iva = 0;
    }

    public function __toString() {
        return $this->tipoFactura . ' ' . $this->nroFactura;
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
     * Set nroFactura
     *
     * @param string $nroFactura
     * @return Factura
     */
    public function setNroFactura($nroFactura) {
        $this->nroFactura = $nroFactura;

        return $this;
    }

    /**
     * Get nroFactura
     *
     * @return string
     */
    public function getNroFactura() {
        return $this->nroFactura;
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return Factura
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
     * Set deposito
     *
     * @param \AppBundle\Entity\Deposito $deposito
     * @return Factura
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
     * Set condicionPago
     *
     * @param \ConfigBundle\Entity\Parametro $condicionPago
     * @return Factura
     */
    public function setCondicionPago(\ConfigBundle\Entity\Parametro $condicionPago = null) {
        $this->condicionPago = $condicionPago;

        return $this;
    }

    /**
     * Get condicionPago
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getCondicionPago() {
        return $this->condicionPago;
    }

    /**
     * Set precioLista
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
     * Add detalles
     *
     * @param \VentasBundle\Entity\FacturaDetalle $detalles
     * @return Factura
     */
    public function addDetalle(\VentasBundle\Entity\FacturaDetalle $detalles) {
        $detalles->setFactura($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \VentasBundle\Entity\FacturaDetalle $detalles
     */
    public function removeDetalle(\VentasBundle\Entity\FacturaDetalle $detalles) {
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

    public function getNuevoNroComprobante() {
        if ($this->getAfipPuntoVenta() && $this->getAfipNroComprobante()) {
            return $this->getAfipPuntoVenta() . '-' . $this->getAfipNroComprobante();
        }
        else {
            return $this->getNroComprobante();
        }
    }

    public function getNroComprobante() {
        return $this->tipoFactura . '-' . $this->nroFactura;
    }

    public function getCantidadItems() {
        $cant = 0;
        foreach ($this->detalles as $detalle) {
            $cant = $cant + $detalle->getCantidad();
        }
        return $cant;
    }

    /*  public function getIva(){
      $iva = 0;
      foreach ($this->detalles as $item) {
      $iva = $iva + $item->getIva();
      }
      return $iva;
      } */

    public function getDescuento() {
        $descuento = 0;
        foreach ($this->detalles as $item) {
            $descuento = $descuento + $item->getMontoDescuento();
        }
        return $descuento;
    }

    /*  public function getRecargo(){
      $recargo = 0;
      foreach ($this->detalles as $item) {
      $recargo += $item->getMontoRecargo();
      }
      return $recargo;
      } */

    public function getSubTotal() {
        $subtotal = 0;
        foreach ($this->detalles as $item) {
            $subtotal = $subtotal + $item->getSubTotal();
        }
        return $subtotal;
    }

    public function getTotalIva() {
        $subtotal = 0;
        foreach ($this->detalles as $item) {
            $subtotal = $subtotal + $item->getIva();
        }
        return $subtotal;
    }

    public function getMontoTotal() {
        return ($this->getSubTotal() - $this->getDescuento() ) + $this->getTotalIva();
    }

    public function getPagado() {
        return $this->getTotal() - $this->getSaldo();
    }

    public function getProporcionPagado() {
        $proporcion = ( $this->getSaldo() * 100 ) / $this->getTotal();
        $saldo = ( $this->getSubTotal() * $proporcion ) / 100;
        return $this->getSubTotal() - $saldo;
    }

    public function paraComision() {
        $proporcion = ( $this->getSaldo() * 100 ) / $this->getTotal();
        $saldo = ( $this->getSubTotal() * $proporcion ) / 100;

        return ( $this->getSubTotal() - $saldo ) - $this->getComisionado();
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
     * Set fechaImpresion
     *
     * @param \DateTime $fechaImpresion
     * @return Factura
     */
    public function setFechaImpresion($fechaImpresion) {
        $this->fechaImpresion = $fechaImpresion;

        return $this;
    }

    /**
     * Get fechaImpresion
     *
     * @return \DateTime
     */
    public function getFechaImpresion() {
        return $this->fechaImpresion;
    }

    /**
     * Set impresiones
     *
     * @param integer $impresiones
     * @return Factura
     */
    public function setImpresiones($impresiones) {
        $this->impresiones = $impresiones;

        return $this;
    }

    /**
     * Get impresiones
     *
     * @return integer
     */
    public function getImpresiones() {
        return $this->impresiones;
    }


    /**
     * Set comisionado
     *
     * @param string $comisionado
     * @return Factura
     */
    public function setComisionado($comisionado) {
        $this->comisionado = $comisionado;

        return $this;
    }

    /**
     * Get comisionado
     *
     * @return string
     */
    public function getComisionado() {
        return $this->comisionado;
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
     * Get iva
     *
     * @return string
     */
    public function getIva() {
        return $this->iva;
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

}
