<?php

namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ComprasBundle\Entity\PagoProveedor
 * @ORM\Table(name="compras_pago_proveedor")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\ProveedorRepository")
 */
class PagoProveedor {
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
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Moneda")
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     */
    protected $moneda;

    /**
     * @var string $cotizacion
     * @ORM\Column(name="cotizacion", type="decimal", precision=20, scale=2, nullable=true)
     */
    protected $cotizacion = 1;

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
     * @ORM\Column(name="importe", type="decimal", precision=20, scale=2 )
     */
    protected $importe;

    /**
     * @var integer $montoPago
     * @ORM\Column(name="monto_pago", type="decimal", precision=20, scale=2 )
     */
    protected $montoPago;

    /**
     * @var integer $montoIva
     * @ORM\Column(name="monto_iva", type="decimal", precision=20, scale=2 )
     */
    protected $montoIva;

    /**
     * @var integer $baseImponibleRentas
     * @ORM\Column(name="base_imponible_rentas", type="decimal", precision=20, scale=2 )
     */
    protected $baseImponibleRentas = 0;

    /**
     * @var integer $retencionRentas
     * @ORM\Column(name="retencion_rentas", type="decimal", precision=20, scale=2 )
     */
    protected $retencionRentas = 0;

    /**
     * @var integer $adicionalRentas
     * @ORM\Column(name="adicional_rentas", type="decimal", precision=20, scale=2 )
     */
    protected $adicionalRentas = 0;

    /**
     * @var integer $codigoAtp
     * @ORM\Column(name="codigo_atp", type="integer", nullable=true)
     */
    protected $codigoAtp;

    /**
     * @var integer $retencionGanancias
     * @ORM\Column(name="retencion_ganancias", type="decimal", precision=20, scale=2 )
     */
    protected $retencionGanancias = 0;

    /**
     * @var integer $montoRetRentas
     * @ORM\Column(name="monto_ret_rentas", type="decimal", precision=20, scale=2, nullable=true )
     */
    protected $montoRetRentas;

    /**
     * @var integer $montoRetGanancias
     * @ORM\Column(name="monto_ret_ganancias", type="decimal", precision=20, scale=2, nullable=true )
     */
    protected $montoRetGanancias;

    /**
     * * Diferencia entre el pago total y los importes abonados.
     * * Refleja los saldos pendientes de las facturas imputadas
     * @var integer $saldo
     * @ORM\Column(name="saldo", type="decimal", precision=20, scale=2 )
     */
    protected $saldo = 0;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\RubroCompras")
     * @ORM\JoinColumn(name="rubro_compras_id", referencedColumnName="id")
     * */
    protected $rubroCompras;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Proveedor", inversedBy="pagos")
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     */
    protected $proveedor;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\CobroDetalle", mappedBy="pagoProveedor",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $cobroDetalles;

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
    public function __construct() {
        $this->cobroDetalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->saldo = 0;
        $this->fecha = new \DateTime();
        $this->baseImponibleRentas = 0;
    }

    public function __toString() {
        return str_pad($this->getPrefijoNro(), 4, "0", STR_PAD_LEFT) . '-' . str_pad($this->getPagoNro(), 8, "0", STR_PAD_LEFT);
    }

    public function getComprobanteNro() {
        return $this->__toString();
    }

    public function getConceptoTxt() {
        return $this->conceptoTxt;
    }

    public function setConceptoTxt($txt) {
        $this->conceptoTxt = $txt;
        return $this;
    }

    public function getAlicuotaRentasTxt() {
        $ret = $this->getRetencionRentas() . '%';
        $alicuota = ( $this->getAdicionalRentas() > 0 ) ? $ret . ' + ' . $this->getAdicionalRentas() . '%' : $ret;
        return $alicuota;
    }

    public function getMontoRetencionRentas() {
        $retencion = $this->getBaseImponibleRentas() * ( $this->getRetencionRentas() / 100 );
        $adicional = $retencion * ( $this->getAdicionalRentas() / 100 );
        return $retencion + $adicional;
    }

    public function getAlicuotaGananciasTxt() {
        return $this->getRetencionGanancias() . '%';
    }

    // public function getMontoGanancias() {
    //     return $this->getBaseImponibleRentas() * ( $this->getRetencionGanancias() / 100 );
    // }

    public function getTextoPagosParaOrdenPago() {
        $txt = '';
        foreach ($this->cobroDetalles as $det) {
            $aux = ($txt) ? ' - ' : '';
            $monto = $det->getMoneda()->getSimbolo() . ' ' . $det->getImporte();
            switch ($det->getTipoPago()) {
                case 'EFECTIVO':
                    $txt = $txt . $aux . 'EFECTIVO: ' . $monto;
                    break;
                case 'CHEQUE':
                    $txt = $txt . $aux . 'CHEQUE:  N°' . $det->getChequeRecibido()->getNroCheque() . ' ' . $monto;
                    break;
                case 'TARJETA':
                    $txt = $txt . $aux . $det->getDatosTarjeta()->getTarjeta()->getNombre() . ': ' . $monto;
                    break;
            }
        }
        return $txt;
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
     * @return PagoProveedor
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
     * Set prefijoNro
     *
     * @param string $prefijoNro
     * @return PagoProveedor
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
     * Set pagoNro
     *
     * @param string $pagoNro
     * @return PagoProveedor
     */
    public function setPagoNro($pagoNro) {
        $this->pagoNro = $pagoNro;

        return $this;
    }

    /**
     * Get pagoNro
     *
     * @return string
     */
    public function getPagoNro() {
        return $this->pagoNro;
    }

    /**
     * Get nroPago
     * @return string
     */
    public function getNroPago() {
        return $this->prefijoNro . '-' . $this->pagoNro;
    }

    /**
     * Set nroComprobante
     *
     * @param string $nroComprobante
     * @return PagoProveedor
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
     * Set concepto
     *
     * @param string $concepto
     * @return PagoProveedor
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
     * Set detalle
     *
     * @param string $detalle
     * @return PagoProveedor
     */
    public function setDetalle($detalle) {
        $this->detalle = $detalle;

        return $this;
    }

    /**
     * Get detalle
     *
     * @return string
     */
    public function getDetalle() {
        return $this->detalle;
    }

    /**
     * Set importe
     *
     * @param string $importe
     * @return PagoProveedor
     */
    public function setImporte($importe) {
        $this->importe = $importe;

        return $this;
    }

    /**
     * Get importe
     *
     * @return string
     */
    public function getImporte() {
        return $this->importe;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return PagoProveedor
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
     * Set proveedor
     *
     * @param \ComprasBundle\Entity\Proveedor $proveedor
     * @return PagoProveedor
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
     * @return PagoProveedor
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

    public function getTotalCheques() {
        $cant = 0;
        foreach ($this->getCobroDetalles() as $cobro) {
            if ($cobro->getChequeRecibido()) {
                $cant = $cant + $cobro->getChequeRecibido()->getValor();
            }
        }
        return $cant;
    }

    public function getTotal() {
        $cant = 0;
        foreach ($this->getCobroDetalles() as $cobro) {
            $cant = $cant + $cobro->getImporte();
        }
        return $cant;
    }

    /**
     * Set saldo
     *
     * @param string $saldo
     * @return PagoProveedor
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
     * Add cobroDetalles
     *
     * @param \VentasBundle\Entity\CobroDetalle $cobroDetalles
     * @return PagoProveedor
     */
    public function addCobroDetalle(\VentasBundle\Entity\CobroDetalle $cobroDetalles) {
        $cobroDetalles->setPagoProveedor($this);
        $this->cobroDetalles[] = $cobroDetalles;

        return $this;
    }

    /**
     * Remove cobroDetalles
     *
     * @param \VentasBundle\Entity\CobroDetalle $cobroDetalles
     */
    public function removeCobroDetalle(\VentasBundle\Entity\CobroDetalle $cobroDetalles) {
        $this->cobroDetalles->removeElement($cobroDetalles);
    }

    /**
     * Get cobroDetalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCobroDetalles() {
        return $this->cobroDetalles;
    }

    /**
     * Set cotizacion
     *
     * @param string $cotizacion
     * @return PagoProveedor
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
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return PagoProveedor
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
     * Set retencionRentas
     *
     * @param string $retencionRentas
     * @return PagoProveedor
     */
    public function setRetencionRentas($retencionRentas) {
        $this->retencionRentas = $retencionRentas;

        return $this;
    }

    /**
     * Get retencionRentas
     *
     * @return string
     */
    public function getRetencionRentas() {
        return $this->retencionRentas;
    }

    /**
     * Set adicionalRentas
     *
     * @param string $adicionalRentas
     * @return PagoProveedor
     */
    public function setAdicionalRentas($adicionalRentas) {
        $this->adicionalRentas = $adicionalRentas;

        return $this;
    }

    /**
     * Get adicionalRentas
     *
     * @return string
     */
    public function getAdicionalRentas() {
        return $this->adicionalRentas;
    }

    /**
     * Set baseImponibleRentas
     *
     * @param string $baseImponibleRentas
     * @return PagoProveedor
     */
    public function setBaseImponibleRentas($baseImponibleRentas) {
        $this->baseImponibleRentas = $baseImponibleRentas;

        return $this;
    }

    /**
     * Get baseImponibleRentas
     *
     * @return string
     */
    public function getBaseImponibleRentas() {
        return $this->baseImponibleRentas;
    }

    /**
     * Set retencionGanancias
     *
     * @param string $retencionGanancias
     * @return PagoProveedor
     */
    public function setRetencionGanancias($retencionGanancias) {
        $this->retencionGanancias = $retencionGanancias;

        return $this;
    }

    /**
     * Get retencionGanancias
     *
     * @return string
     */
    public function getRetencionGanancias() {
        return $this->retencionGanancias;
    }

    /**
     * Set montoRetRentas
     *
     * @param string $montoRetRentas
     * @return PagoProveedor
     */
    public function setMontoRetRentas($montoRetRentas) {
        $this->montoRetRentas = $montoRetRentas;

        return $this;
    }

    /**
     * Get montoRetRentas
     *
     * @return string
     */
    public function getMontoRetRentas() {
        return $this->montoRetRentas;
    }

    /**
     * Set montoRetGanancias
     *
     * @param string $montoRetGanancias
     * @return PagoProveedor
     */
    public function setMontoRetGanancias($montoRetGanancias) {
        $this->montoRetGanancias = $montoRetGanancias;

        return $this;
    }

    /**
     * Get montoRetGanancias
     *
     * @return string
     */
    public function getMontoRetGanancias() {
        return $this->montoRetGanancias;
    }

    /**
     * Set montoPago
     *
     * @param string $montoPago
     * @return PagoProveedor
     */
    public function setMontoPago($montoPago) {
        $this->montoPago = $montoPago;

        return $this;
    }

    /**
     * Get montoPago
     *
     * @return string
     */
    public function getMontoPago() {
        return $this->montoPago;
    }

    /**
     * Set montoIva
     *
     * @param string $montoIva
     * @return PagoProveedor
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
     * Set rubroCompras
     *
     * @param \ConfigBundle\Entity\RubroCompras $rubroCompras
     * @return PagoProveedor
     */
    public function setRubroCompras(\ConfigBundle\Entity\RubroCompras $rubroCompras = null)
    {
        $this->rubroCompras = $rubroCompras;

        return $this;
    }

    /**
     * Get rubroCompras
     *
     * @return \ConfigBundle\Entity\RubroCompras
     */
    public function getRubroCompras()
    {
        return $this->rubroCompras;
    }

    /**
     * Set codigoAtp
     *
     * @param integer $codigoAtp
     * @return PagoProveedor
     */
    public function setCodigoAtp($codigoAtp)
    {
        $this->codigoAtp = $codigoAtp;

        return $this;
    }

    /**
     * Get codigoAtp
     *
     * @return integer 
     */
    public function getCodigoAtp()
    {
        return $this->codigoAtp;
    }
}
