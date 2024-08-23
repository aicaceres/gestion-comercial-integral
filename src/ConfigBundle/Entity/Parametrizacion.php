<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\Parametrizacion
 * @ORM\Table(name="parametrizacion")
 * @ORM\Entity()
 */
class Parametrizacion {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente")
     * @ORM\JoinColumn(name="ventas_cliente_bydefault_id", referencedColumnName="id")
     */
    protected $ventasClienteBydefault;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="ventas_deposito_bydefault_id", referencedColumnName="id")
     */
    protected $ventasDepositoBydefault;

    /**
     * @var integer $cantidadItemsParaFactura
     * @ORM\Column(name="cantidad_items_para_factura", type="integer")
     */
    protected $cantidadItemsParaFactura = 10;

    /**
     * @var integer $ultimoNroOperacionVenta
     * @ORM\Column(name="ultimo_nro_operacion_venta", type="integer")
     */
    protected $ultimoNroOperacionVenta = 0;

    /**
     * @var integer $ultimoNroOperacionCobro
     * @ORM\Column(name="ultimo_nro_operacion_cobro", type="integer")
     */
    protected $ultimoNroOperacionCobro = 0;

    /**
     * @var integer $ultimoNroPresupuesto
     * @ORM\Column(name="ultimo_nro_presupuesto", type="integer")
     */
    protected $ultimoNroPresupuesto = 0;

    /**
     * @var integer $ultimoNroPagoCliente
     * @ORM\Column(name="ultimo_nro_pago_cliente", type="integer")
     */
    protected $ultimoNroPagoCliente = 0;

    /**
     * @var integer $validezPresupuesto
     * validez en dias
     * @ORM\Column(name="validez_presupuesto", type="integer")
     */
    protected $validezPresupuesto = 7;

    /**
     * Parametros para Factura MiPymes
     */

    /**
     * @var string $formPagoFE
     * @ORM\Column(name="form_pago_fe", type="string", length=3)}
     * Valores SCA - ADC
     */
    protected $formPagoFE = 'SCA';

    /**
     * @var string $cbuEmisor
     * @ORM\Column(name="cbu_emisor", type="string", length=22)}
     */
    protected $cbuEmisor;

    /**
     * @var string $aliasEmisor
     * @ORM\Column(name="alias_emisor", type="string", length=256, nullable=true)}
     */
    protected $aliasEmisor;

    /**
     * @var string $referenciaComercial
     * @ORM\Column(name="referencia_comercial", type="string", length=256, nullable=true)}
     */
    protected $referenciaComercial;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set ventasClienteBydefault
     *
     * @param \VentasBundle\Entity\Cliente $ventasClienteBydefault
     * @return Parametrizacion
     */
    public function setVentasClienteBydefault(\VentasBundle\Entity\Cliente $ventasClienteBydefault = null) {
        $this->ventasClienteBydefault = $ventasClienteBydefault;

        return $this;
    }

    /**
     * Get ventasClienteBydefault
     *
     * @return \VentasBundle\Entity\Cliente
     */
    public function getVentasClienteBydefault() {
        return $this->ventasClienteBydefault;
    }

    /**
     * Set ventasDepositoBydefault
     *
     * @param \AppBundle\Entity\Deposito $ventasDepositoBydefault
     * @return Parametrizacion
     */
    public function setVentasDepositoBydefault(\AppBundle\Entity\Deposito $ventasDepositoBydefault = null) {
        $this->ventasDepositoBydefault = $ventasDepositoBydefault;

        return $this;
    }

    /**
     * Get ventasDepositoBydefault
     *
     * @return \AppBundle\Entity\Deposito
     */
    public function getVentasDepositoBydefault() {
        return $this->ventasDepositoBydefault;
    }

    /**
     * Set cantidadItemsParaFactura
     *
     * @param integer $cantidadItemsParaFactura
     * @return Parametrizacion
     */
    public function setCantidadItemsParaFactura($cantidadItemsParaFactura) {
        $this->cantidadItemsParaFactura = $cantidadItemsParaFactura;

        return $this;
    }

    /**
     * Get cantidadItemsParaFactura
     *
     * @return integer
     */
    public function getCantidadItemsParaFactura() {
        return $this->cantidadItemsParaFactura;
    }

    /**
     * Set ultimoNroOperacionVenta
     *
     * @param integer $ultimoNroOperacionVenta
     * @return Parametrizacion
     */
    public function setUltimoNroOperacionVenta($ultimoNroOperacionVenta) {
        $this->ultimoNroOperacionVenta = $ultimoNroOperacionVenta;

        return $this;
    }

    /**
     * Get ultimoNroOperacionVenta
     *
     * @return integer
     */
    public function getUltimoNroOperacionVenta() {
        return $this->ultimoNroOperacionVenta;
    }

    /**
     * Set ultimoNroOperacionCobro
     *
     * @param integer $ultimoNroOperacionCobro
     * @return Parametrizacion
     */
    public function setUltimoNroOperacionCobro($ultimoNroOperacionCobro) {
        $this->ultimoNroOperacionCobro = $ultimoNroOperacionCobro;

        return $this;
    }

    /**
     * Get ultimoNroOperacionCobro
     *
     * @return integer
     */
    public function getUltimoNroOperacionCobro() {
        return $this->ultimoNroOperacionCobro;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Parametrizacion
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
     * Set ultimoNroPresupuesto
     *
     * @param integer $ultimoNroPresupuesto
     * @return Parametrizacion
     */
    public function setUltimoNroPresupuesto($ultimoNroPresupuesto) {
        $this->ultimoNroPresupuesto = $ultimoNroPresupuesto;

        return $this;
    }

    /**
     * Get ultimoNroPresupuesto
     *
     * @return integer
     */
    public function getUltimoNroPresupuesto() {
        return $this->ultimoNroPresupuesto;
    }

    /**
     * Set validezPresupuesto
     *
     * @param integer $validezPresupuesto
     * @return Parametrizacion
     */
    public function setValidezPresupuesto($validezPresupuesto) {
        $this->validezPresupuesto = $validezPresupuesto;

        return $this;
    }

    /**
     * Get validezPresupuesto
     *
     * @return integer
     */
    public function getValidezPresupuesto() {
        return $this->validezPresupuesto;
    }

    /**
     * Set ultimoNroPagoCliente
     *
     * @param integer $ultimoNroPagoCliente
     * @return Parametrizacion
     */
    public function setUltimoNroPagoCliente($ultimoNroPagoCliente) {
        $this->ultimoNroPagoCliente = $ultimoNroPagoCliente;

        return $this;
    }

    /**
     * Get ultimoNroPagoCliente
     *
     * @return integer
     */
    public function getUltimoNroPagoCliente() {
        return $this->ultimoNroPagoCliente;
    }

    /**
     * Set formPagoFE
     *
     * @param string $formPagoFE
     * @return Parametrizacion
     */
    public function setFormPagoFE($formPagoFE) {
        $this->formPagoFE = $formPagoFE;

        return $this;
    }

    /**
     * Get formPagoFE
     *
     * @return string
     */
    public function getFormPagoFE() {
        return $this->formPagoFE;
    }

    /**
     * Set cbuEmisor
     *
     * @param string $cbuEmisor
     * @return Parametrizacion
     */
    public function setCbuEmisor($cbuEmisor) {
        $this->cbuEmisor = $cbuEmisor;

        return $this;
    }

    /**
     * Get cbuEmisor
     *
     * @return string
     */
    public function getCbuEmisor() {
        return $this->cbuEmisor;
    }

    /**
     * Set aliasEmisor
     *
     * @param string $aliasEmisor
     * @return Parametrizacion
     */
    public function setAliasEmisor($aliasEmisor) {
        $this->aliasEmisor = $aliasEmisor;

        return $this;
    }

    /**
     * Get aliasEmisor
     *
     * @return string
     */
    public function getAliasEmisor() {
        return $this->aliasEmisor;
    }


    /**
     * Set referenciaComercial
     *
     * @param string $referenciaComercial
     * @return Parametrizacion
     */
    public function setReferenciaComercial($referenciaComercial)
    {
        $this->referenciaComercial = $referenciaComercial;

        return $this;
    }

    /**
     * Get referenciaComercial
     *
     * @return string
     */
    public function getReferenciaComercial()
    {
        return $this->referenciaComercial;
    }
}
