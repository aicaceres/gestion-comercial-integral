<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * VentasBundle\Entity\NotaDebCred
 * @ORM\Table(name="ventas_nota_debcred")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\NotaDebCredRepository")
 * @UniqueEntity(
 *     fields={"cliente","signo","nroNotaDebCred"},
 *     errorPath="nroComprobante",
 *     message="El N° de Comprobante ya existe para este Cliente."
 * )
 */
class NotaDebCred {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $tipoNota
     * @ORM\Column(name="tipo_nota", type="string", length=1)
     */
    protected $tipoNota;

    /**
     * @var integer $nroNotaDebCred
     * @ORM\Column(name="nro_nota_debcred", type="string", length=30)
     */
    protected $nroNotaDebCred;

    /**
     * @var datetime $fecha
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado;

    /**
     * 'CRE' = '-'; 'DEB' = '+'
     * @var string $signo
     * @ORM\Column(name="signo", type="string",length=1)
     */
    protected $signo = '+';

    /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\FacturaElectronica", mappedBy="notaDebCred")
     */
    private $notaElectronica;

    /**
     * @var integer $iva
     * @ORM\Column(name="iva", type="decimal", scale=2,nullable=true )
     */
    protected $iva;

    /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=3 )
     */
    protected $total;

    /**
     * @var integer $saldo
     * @ORM\Column(name="saldo", type="decimal", scale=3,nullable=true )
     */
    protected $saldo;

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
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="notasDebCredVenta")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     */
    protected $cliente;

    /**
     * @ORM\ManyToMany(targetEntity="VentasBundle\Entity\Factura")
     * @ORM\JoinTable(name="facturas_x_notadebcred_ventas",
     *      joinColumns={@ORM\JoinColumn(name="ventas_nota_debcred_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ventas_factura_id", referencedColumnName="id")}
     * )
     */
    protected $facturas;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\Column(name="concepto", type="text", nullable=true)
     */
    protected $concepto;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\NotaDebCredDetalle", mappedBy="notaDebCred",cascade={"persist", "remove"})
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
        $this->signo = '+';
        $this->estado = 'ACREDITADO';
        $this->tipoNota = 'A';
    }

    public function __toString() {
        return $this->tipoNota . ' ' . $this->nroNotaDebCred;
    }

    public function getSignoNota() {
        return ($this->signo == '-') ? 'Crédito' : 'Débito';
    }

    public function getCae() {
        return $this->getNotaElectronica()->getCae();
    }

    public function getCaeVto() {
        return $this->getNotaElectronica()->getCaeVto();
    }

    public function getFacturadoDesde() {
        return $this->getNotaElectronica()->getFacturadoDesde();
    }

    public function getFacturadoHasta() {
        return $this->getNotaElectronica()->getFacturadoHasta();
    }

    public function getPagoVto() {
        return $this->getNotaElectronica()->getPagoVto();
    }

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

    public function getNroComprobante() {
        return $this->__toString();
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return NotaDebCred
     */
    public function setFecha($fecha) {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return NotaDebCred
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
     * @return NotaDebCred
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
     * @return NotaDebCred
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
     * Add detalles
     *
     * @param \VentasBundle\Entity\NotaDebCredDetalle $detalles
     * @return NotaDebCred
     */
    public function addDetalle(\VentasBundle\Entity\NotaDebCredDetalle $detalles) {
        $detalles->setNotaDebCred($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \VentasBundle\Entity\NotaDebCredDetalle $detalles
     */
    public function removeDetalle(\VentasBundle\Entity\NotaDebCredDetalle $detalles) {
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
     * @return NotaDebCred
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
     * @return NotaDebCred
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
     * Set concepto
     *
     * @param string $concepto
     * @return NotaDebCred
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
     * Set signo
     *
     * @param string $signo
     * @return NotaDebCred
     */
    public function setSigno($signo) {
        $this->signo = $signo;

        return $this;
    }

    /**
     * Get signo
     *
     * @return string
     */
    public function getSigno() {
        return $this->signo;
    }

    /**
     * Set iva
     *
     * @param string $iva
     * @return NotaDebCred
     */
    public function setIva($iva) {
        $this->iva = $iva;

        return $this;
    }

    public function getIva() {
        $iva = 0;
        foreach ($this->detalles as $item) {
            $iva = $iva + $item->getIva();
        }
        return $iva;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return NotaDebCred
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
     * Add facturas
     *
     * @param \VentasBundle\Entity\Factura $facturas
     * @return NotaDebCred
     */
    public function addFactura(\VentasBundle\Entity\Factura $facturas) {
        $this->facturas[] = $facturas;
        return $this;
    }

    /**
     * Remove facturas
     *
     * @param \VentasBundle\Entity\Factura $facturas
     */
    public function removeFactura(\VentasBundle\Entity\Factura $facturas) {
        $this->facturas->removeElement($facturas);
    }

    /**
     * Get facturas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFacturas() {
        return $this->facturas;
    }

    /**
     * Set saldo
     *
     * @param string $saldo
     * @return NotaDebCred
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return NotaDebCred
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
     * Set notaElectronica
     *
     * @param \VentasBundle\Entity\FacturaElectronica $notaElectronica
     * @return NotaDebCred
     */
    public function setNotaElectronica(\VentasBundle\Entity\FacturaElectronica $notaElectronica = null) {
        $this->notaElectronica = $notaElectronica;

        return $this;
    }

    /**
     * Get notaElectronica
     *
     * @return \VentasBundle\Entity\FacturaElectronica
     */
    public function getNotaElectronica() {
        return $this->notaElectronica;
    }

    /**
     * Set tipoNota
     *
     * @param string $tipoNota
     * @return NotaDebCred
     */
    public function setTipoNota($tipoNota) {
        $this->tipoNota = $tipoNota;

        return $this;
    }

    /**
     * Set nroNotaDebCred
     *
     * @param string $nroNotaDebCred
     * @return NotaDebCred
     */
    public function setNroNotaDebCred($nroNotaDebCred) {
        $this->nroNotaDebCred = $nroNotaDebCred;

        return $this;
    }

    /**
     * Get nroNotaDebCred
     *
     * @return string
     */
    public function getNroNotaDebCred() {
        return $this->nroNotaDebCred;
    }

    /**
     * Set condicionPago
     *
     * @param \ConfigBundle\Entity\Parametro $condicionPago
     * @return NotaDebCred
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
     *
     * @param \AppBundle\Entity\PrecioLista $precioLista
     * @return NotaDebCred
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
     * @return NotaDebCred
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
     * Get tipoNota
     *
     * @return string
     */
    public function getTipoNota() {
        return $this->tipoNota;
    }

    /**
     * Set afipPuntoVenta
     *
     * @param string $afipPuntoVenta
     * @return NotaDebCred
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
     * @return NotaDebCred
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
     * @return NotaDebCred
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
            return $this->getNroNotaDebCred();
        }
    }

}