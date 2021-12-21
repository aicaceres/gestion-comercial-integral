<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ConfigBundle\Entity\AfipImportacionBuffets
 * @ORM\Table(name="afip_importacion_buffets",uniqueConstraints={@ORM\UniqueConstraint(name="comprobante_idx", columns={"punto_venta", "numero_comprobante"})})
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\ParametroRepository")
 * @UniqueEntity(
 *     fields={"puntoVenta", "numeroComprobante"},
 *     errorPath="puntoVenta",
 *     message="Registro duplicado."
 * )
 */
class AfipImportacionBuffets {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="periodo", type="string", length=7, nullable=true)
     */
    protected $periodo;

    /**
     * @ORM\Column(name="descripcion", type="string", nullable=true)
     */
    protected $descripcion;

    /**
     * @ORM\Column(name="fecha", type="string", nullable=true)
     */
    protected $fecha;

    /**
     * @ORM\Column(name="tipo_comprobante", type="string", nullable=true)
     */
    protected $tipoComprobante;

    /**
     * @ORM\Column(name="letra", type="string", nullable=true)
     */
    protected $letra;

    /**
     * @ORM\Column(name="punto_venta", type="string", nullable=true)
     */
    protected $puntoVenta;

    /**
     * @ORM\Column(name="numero_comprobante", type="string", nullable=true)
     */
    protected $numeroComprobante;

    /**
     * @ORM\Column(name="nombre_cliente", type="string", nullable=true)
     */
    protected $nombreCliente;

    /**
     * @ORM\Column(name="cuit_cliente", type="string", nullable=true)
     */
    protected $cuitCliente;

    /**
     * @ORM\Column(name="cond_fiscal", type="string", nullable=true)
     */
    protected $condFiscal;

    /**
     * @ORM\Column(name="neto_gravado", type="string", nullable=true)
     */
    protected $netoGravado;

    /**
     * @ORM\Column(name="alicuota", type="string", nullable=true)
     */
    protected $alicuota;

    /**
     * @ORM\Column(name="iva_liquidado", type="string", nullable=true)
     */
    protected $ivaLiquidado;

    /**
     * @ORM\Column(name="conc_ng_ex", type="string", nullable=true)
     */
    protected $concNgEx;

    /**
     * @ORM\Column(name="perc_ret", type="string", nullable=true)
     */
    protected $percRet;

    /**
     * @ORM\Column(name="total", type="string", nullable=true)
     */
    protected $total;

    /**
     * @ORM\Column(name="procesado", type="boolean", nullable=true)
     */
    protected $procesado = false;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set periodo
     *
     * @param string $periodo
     * @return AfipImportacionBuffets
     */
    public function setPeriodo($periodo) {
        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get periodo
     *
     * @return string
     */
    public function getPeriodo() {
        return $this->periodo;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return AfipImportacionBuffets
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
     * Set fecha
     *
     * @param string $fecha
     * @return AfipImportacionBuffets
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
     * @return AfipImportacionBuffets
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
     * Set letra
     *
     * @param string $letra
     * @return AfipImportacionBuffets
     */
    public function setLetra($letra) {
        $this->letra = $letra;

        return $this;
    }

    /**
     * Get letra
     *
     * @return string
     */
    public function getLetra() {
        return $this->letra;
    }

    /**
     * Set puntoVenta
     *
     * @param string $puntoVenta
     * @return AfipImportacionBuffets
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
     * Set numeroComprobante
     *
     * @param string $numeroComprobante
     * @return AfipImportacionBuffets
     */
    public function setNumeroComprobante($numeroComprobante) {
        $this->numeroComprobante = $numeroComprobante;

        return $this;
    }

    /**
     * Get numeroComprobante
     *
     * @return string
     */
    public function getNumeroComprobante() {
        return $this->numeroComprobante;
    }

    /**
     * Set nombreCliente
     *
     * @param string $nombreCliente
     * @return AfipImportacionBuffets
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
     * Set cuitCliente
     *
     * @param string $cuitCliente
     * @return AfipImportacionBuffets
     */
    public function setCuitCliente($cuitCliente) {
        $this->cuitCliente = $cuitCliente;

        return $this;
    }

    /**
     * Get cuitCliente
     *
     * @return string
     */
    public function getCuitCliente() {
        return $this->cuitCliente;
    }

    /**
     * Set condFiscal
     *
     * @param string $condFiscal
     * @return AfipImportacionBuffets
     */
    public function setCondFiscal($condFiscal) {
        $this->condFiscal = $condFiscal;

        return $this;
    }

    /**
     * Get condFiscal
     *
     * @return string
     */
    public function getCondFiscal() {
        return $this->condFiscal;
    }

    /**
     * Set netoGravado
     *
     * @param string $netoGravado
     * @return AfipImportacionBuffets
     */
    public function setNetoGravado($netoGravado) {
        $this->netoGravado = $netoGravado;

        return $this;
    }

    /**
     * Get netoGravado
     *
     * @return string
     */
    public function getNetoGravado() {
        return $this->netoGravado;
    }

    /**
     * Set alicuota
     *
     * @param string $alicuota
     * @return AfipImportacionBuffets
     */
    public function setAlicuota($alicuota) {
        $this->alicuota = $alicuota;

        return $this;
    }

    /**
     * Get alicuota
     *
     * @return string
     */
    public function getAlicuota() {
        return $this->alicuota;
    }

    /**
     * Set ivaLiquidado
     *
     * @param string $ivaLiquidado
     * @return AfipImportacionBuffets
     */
    public function setIvaLiquidado($ivaLiquidado) {
        $this->ivaLiquidado = $ivaLiquidado;

        return $this;
    }

    /**
     * Get ivaLiquidado
     *
     * @return string
     */
    public function getIvaLiquidado() {
        return $this->ivaLiquidado;
    }

    /**
     * Set concNgEx
     *
     * @param string $concNgEx
     * @return AfipImportacionBuffets
     */
    public function setConcNgEx($concNgEx) {
        $this->concNgEx = $concNgEx;

        return $this;
    }

    /**
     * Get concNgEx
     *
     * @return string
     */
    public function getConcNgEx() {
        return $this->concNgEx;
    }

    /**
     * Set percRet
     *
     * @param string $percRet
     * @return AfipImportacionBuffets
     */
    public function setPercRet($percRet) {
        $this->percRet = $percRet;

        return $this;
    }

    /**
     * Get percRet
     *
     * @return string
     */
    public function getPercRet() {
        return $this->percRet;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return AfipImportacionBuffets
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
     * @return AfipImportacionBuffets
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

}