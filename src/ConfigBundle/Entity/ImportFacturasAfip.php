<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\ImportFacturasAfip
 * @ORM\Table(name="import_facturas_afip")
 * @ORM\Entity()
 */
class ImportFacturasAfip {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="fecha", type="string")
     */
    protected $fecha;

    /**
     * @ORM\Column(name="tipo_comprobante", type="string")
     */
    protected $tipoComprobante;

    /**
     * @ORM\Column(name="punto_venta", type="string")
     */
    protected $puntoVenta;

    /**
     * @ORM\Column(name="nro_comprobante", type="string")
     */
    protected $nroComprobante;

    /**
     * @ORM\Column(name="cae", type="string", nullable=true)
     */
    protected $cae;

    /**
     * @ORM\Column(name="doc_tipo", type="string", nullable=true)
     */
    protected $docTipo;

    /**
     * @ORM\Column(name="doc_nro", type="string", nullable=true)
     */
    protected $docNro;

    /**
     * @ORM\Column(name="nombre_cliente", type="string", nullable=true)
     */
    protected $nombreCliente;

    /**
     * @ORM\Column(name="tipo_cambio", type="string")
     */
    protected $tipoCambio = 1;

    /**
     * @ORM\Column(name="moneda", type="string")
     */
    protected $moneda = "$";

    /**
     * @ORM\Column(name="imp_neto", type="string")
     */
    protected $impNeto;

    /**
     * @ORM\Column(name="imp_tot_conc", type="string")
     */
    protected $impTotConc;

    /**
     * @ORM\Column(name="imp_op_ex", type="string")
     */
    protected $impOpEx;

    /**
     * @ORM\Column(name="imp_iva", type="string")
     */
    protected $impIva;

    /**
     * @ORM\Column(name="imp_trib", type="string", nullable=true)
     */
    protected $impTrib;

    /**
     * @ORM\Column(name="total", type="string")
     */
    protected $total;

    /**
     * @ORM\Column(name="en_ctacte", type="string", nullable=true)
     */
    protected $enCtacte = 0;

    /**
     * @ORM\Column(name="procesado", type="boolean")
     */
    protected $procesado = false;

    /**
     * @ORM\Column(name="base21", type="string", nullable=true)
     */
    protected $base21;

    /**
     * @ORM\Column(name="iva21", type="string", nullable=true)
     */
    protected $iva21;

    /**
     * @ORM\Column(name="tasa21", type="string", nullable=true)
     */
    protected $tasa21;

    /**
     * @ORM\Column(name="base105", type="string", nullable=true)
     */
    protected $base105;

    /**
     * @ORM\Column(name="iva105", type="string", nullable=true)
     */
    protected $iva105;

    /**
     * @ORM\Column(name="tasa105", type="string", nullable=true)
     */
    protected $tasa105;

    /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\FacturaElectronica")
     */
    protected $facturaElectronica;

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
     * @param string $fecha
     * @return ImportFacturasAfip
     */
    public function setFecha($fecha) {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return string
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * Set tipoComprobante
     *
     * @param string $tipoComprobante
     * @return ImportFacturasAfip
     */
    public function setTipoComprobante($tipoComprobante) {
        $this->tipoComprobante = $tipoComprobante;

        return $this;
    }

    /**
     * Get tipoComprobante
     *
     * @return string
     */
    public function getTipoComprobante() {
        return $this->tipoComprobante;
    }

    /**
     * Set puntoVenta
     *
     * @param string $puntoVenta
     * @return ImportFacturasAfip
     */
    public function setPuntoVenta($puntoVenta) {
        $this->puntoVenta = $puntoVenta;

        return $this;
    }

    /**
     * Get puntoVenta
     *
     * @return string
     */
    public function getPuntoVenta() {
        return $this->puntoVenta;
    }

    /**
     * Set nroComprobante
     *
     * @param string $nroComprobante
     * @return ImportFacturasAfip
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
     * Set cae
     *
     * @param string $cae
     * @return ImportFacturasAfip
     */
    public function setCae($cae) {
        $this->cae = $cae;

        return $this;
    }

    /**
     * Get cae
     *
     * @return string
     */
    public function getCae() {
        return $this->cae;
    }

    /**
     * Set docTipo
     *
     * @param string $docTipo
     * @return ImportFacturasAfip
     */
    public function setDocTipo($docTipo) {
        $this->docTipo = $docTipo;

        return $this;
    }

    /**
     * Get docTipo
     *
     * @return string
     */
    public function getDocTipo() {
        return $this->docTipo;
    }

    /**
     * Set docNro
     *
     * @param string $docNro
     * @return ImportFacturasAfip
     */
    public function setDocNro($docNro) {
        $this->docNro = $docNro;

        return $this;
    }

    /**
     * Get docNro
     *
     * @return string
     */
    public function getDocNro() {
        return $this->docNro;
    }

    /**
     * Set nombreCliente
     *
     * @param string $nombreCliente
     * @return ImportFacturasAfip
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
     * Set impNeto
     *
     * @param string $impNeto
     * @return ImportFacturasAfip
     */
    public function setImpNeto($impNeto) {
        $this->impNeto = $impNeto;

        return $this;
    }

    /**
     * Get impNeto
     *
     * @return string
     */
    public function getImpNeto() {
        return $this->impNeto;
    }

    /**
     * Set impTotConc
     *
     * @param string $impTotConc
     * @return ImportFacturasAfip
     */
    public function setImpTotConc($impTotConc) {
        $this->impTotConc = $impTotConc;

        return $this;
    }

    /**
     * Get impTotConc
     *
     * @return string
     */
    public function getImpTotConc() {
        return $this->impTotConc;
    }

    /**
     * Set impOpEx
     *
     * @param string $impOpEx
     * @return ImportFacturasAfip
     */
    public function setImpOpEx($impOpEx) {
        $this->impOpEx = $impOpEx;

        return $this;
    }

    /**
     * Get impOpEx
     *
     * @return string
     */
    public function getImpOpEx() {
        return $this->impOpEx;
    }

    /**
     * Set impIva
     *
     * @param string $impIva
     * @return ImportFacturasAfip
     */
    public function setImpIva($impIva) {
        $this->impIva = $impIva;

        return $this;
    }

    /**
     * Get impIva
     *
     * @return string
     */
    public function getImpIva() {
        return $this->impIva;
    }

    /**
     * Set impTrib
     *
     * @param string $impTrib
     * @return ImportFacturasAfip
     */
    public function setImpTrib($impTrib) {
        $this->impTrib = $impTrib;

        return $this;
    }

    /**
     * Get impTrib
     *
     * @return string
     */
    public function getImpTrib() {
        return $this->impTrib;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return ImportFacturasAfip
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
     * Set procesado
     *
     * @param boolean $procesado
     * @return ImportFacturasAfip
     */
    public function setProcesado($procesado) {
        $this->procesado = $procesado;

        return $this;
    }

    /**
     * Get procesado
     *
     * @return boolean
     */
    public function getProcesado() {
        return $this->procesado;
    }

    /**
     * Set enCtacte
     *
     * @param boolean $enCtacte
     * @return ImportFacturasAfip
     */
    public function setEnCtacte($enCtacte) {
        $this->enCtacte = $enCtacte;

        return $this;
    }

    /**
     * Get enCtacte
     *
     * @return boolean
     */
    public function getEnCtacte() {
        return $this->enCtacte;
    }

    /**
     * Set facturaElectronica
     *
     * @param \VentasBundle\Entity\FacturaElectronica $facturaElectronica
     * @return ImportFacturasAfip
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
     * Set tipoCambio
     *
     * @param string $tipoCambio
     * @return ImportFacturasAfip
     */
    public function setTipoCambio($tipoCambio) {
        $this->tipoCambio = $tipoCambio;

        return $this;
    }

    /**
     * Get tipoCambio
     *
     * @return string
     */
    public function getTipoCambio() {
        return $this->tipoCambio;
    }

    /**
     * Set moneda
     *
     * @param string $moneda
     * @return ImportFacturasAfip
     */
    public function setMoneda($moneda) {
        $this->moneda = $moneda;

        return $this;
    }

    /**
     * Get moneda
     *
     * @return string
     */
    public function getMoneda() {
        return $this->moneda;
    }

    /**
     * Set base21
     *
     * @param string $base21
     * @return ImportFacturasAfip
     */
    public function setBase21($base21) {
        $this->base21 = $base21;

        return $this;
    }

    /**
     * Get base21
     *
     * @return string
     */
    public function getBase21() {
        return $this->base21;
    }

    /**
     * Set iva21
     *
     * @param string $iva21
     * @return ImportFacturasAfip
     */
    public function setIva21($iva21) {
        $this->iva21 = $iva21;

        return $this;
    }

    /**
     * Get iva21
     *
     * @return string
     */
    public function getIva21() {
        return $this->iva21;
    }

    /**
     * Set tasa21
     *
     * @param string $tasa21
     * @return ImportFacturasAfip
     */
    public function setTasa21($tasa21) {
        $this->tasa21 = $tasa21;

        return $this;
    }

    /**
     * Get tasa21
     *
     * @return string
     */
    public function getTasa21() {
        return $this->tasa21;
    }

    /**
     * Set base105
     *
     * @param string $base105
     * @return ImportFacturasAfip
     */
    public function setBase105($base105) {
        $this->base105 = $base105;

        return $this;
    }

    /**
     * Get base105
     *
     * @return string
     */
    public function getBase105() {
        return $this->base105;
    }

    /**
     * Set iva105
     *
     * @param string $iva105
     * @return ImportFacturasAfip
     */
    public function setIva105($iva105) {
        $this->iva105 = $iva105;

        return $this;
    }

    /**
     * Get iva105
     *
     * @return string
     */
    public function getIva105() {
        return $this->iva105;
    }

    /**
     * Set tasa105
     *
     * @param string $tasa105
     * @return ImportFacturasAfip
     */
    public function setTasa105($tasa105) {
        $this->tasa105 = $tasa105;

        return $this;
    }

    /**
     * Get tasa105
     *
     * @return string
     */
    public function getTasa105() {
        return $this->tasa105;
    }

}