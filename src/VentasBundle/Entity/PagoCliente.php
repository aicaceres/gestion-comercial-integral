<?php
namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * VentasBundle\Entity\PagoCliente
 * @ORM\Table(name="ventas_pago_cliente")
 * @ORM\Entity()
 */
class PagoCliente
{
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
    protected $prefijoNro = '011';
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
     * @ORM\Column(name="cotizacion", type="decimal", scale=2, nullable=true)
     */
    protected $cotizacion = 1;
    /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=2 )
     */
    protected $total=0;

    /**
     * @ORM\Column(name="genera_nota_credito", type="boolean",nullable=true)
     */
    protected $generaNotaCredito = false;

    /**
    * @ORM\OneToOne(targetEntity="VentasBundle\Entity\NotaDebCred")
    * @ORM\JoinColumn(name="ventas_nota_debcred_id", referencedColumnName="id")
    * Registro de la nota de credito generada por pago adelantado
    */
    protected $notaDebCred;

     /**
     * @ORM\ManyToMany(targetEntity="VentasBundle\Entity\FacturaElectronica", inversedBy="pagos")
     * @ORM\JoinTable(name="comprobantes_x_pagocliente",
     *      joinColumns={@ORM\JoinColumn(name="ventas_factura_electronica_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ventas_pago_cliente_id", referencedColumnName="id")}
     * )
     */
    private $comprobantes;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\CobroDetalle", mappedBy="pagoCliente",cascade={"persist", "remove"})
     */
    protected $cobroDetalles;

    /**
     * @var string $observaciones
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

     /**
     *@ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="pagos")
     *@ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     */
    protected $cliente;

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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comprobantes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cobroDetalles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString(){
         return str_pad($this->getPrefijoNro(), 4, "0", STR_PAD_LEFT) . '-' .  str_pad($this->getPagoNro(), 8, "0", STR_PAD_LEFT);
    }

    public function getComprobanteNro(){
        return $this->__toString();
    }

    public function getComprobantesTxt(){
        $txt = '';
        foreach ($this->getComprobantes() as $comp) {
            $aux = ($txt) ? ' | ' : '';
            $txt = $txt . $aux . $comp->getComprobanteTxt();
        }
        return $txt;
    }

    public function getMontoNc(){
        $totpagos = 0;
        foreach ($this->getCobroDetalles() as $det) {
            $totpagos +=  $det->getImporte() ;
        }
        return $this->getTotal() - $totpagos;
    }

    public function getTextoPagosParaRecibo(){
        $txt = '';
        foreach( $this->cobroDetalles as $det){
            $aux = ($txt) ? ' - ' : '';
            $monto = $det->getMoneda()->getSimbolo() . ' ' .  $det->getImporte();
            switch ($det->getTipoPago()){
                case 'EFECTIVO':
                    $txt = $txt . $aux . 'EFECTIVO: '. $monto;
                    break;
                case 'CHEQUE':
                    $txt = $txt . $aux . 'CHEQUE: '. $monto;
                    break;
                case 'TARJETA':
                    $txt = $txt . $aux . $det->getDatosTarjeta()->getTarjeta()->getNombre() .': ' . $monto;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return PagoCliente
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set prefijoNro
     *
     * @param string $prefijoNro
     * @return PagoCliente
     */
    public function setPrefijoNro($prefijoNro)
    {
        $this->prefijoNro = $prefijoNro;

        return $this;
    }

    /**
     * Get prefijoNro
     *
     * @return string
     */
    public function getPrefijoNro()
    {
        return $this->prefijoNro;
    }

    /**
     * Set pagoNro
     *
     * @param string $pagoNro
     * @return PagoCliente
     */
    public function setPagoNro($pagoNro)
    {
        $this->pagoNro = $pagoNro;

        return $this;
    }

    /**
     * Get pagoNro
     *
     * @return string
     */
    public function getPagoNro()
    {
        return $this->pagoNro;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return PagoCliente
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return PagoCliente
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return PagoCliente
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Add comprobantes
     *
     * @param \VentasBundle\Entity\FacturaElectronica $comprobantes
     * @return PagoCliente
     */
    public function addComprobante(\VentasBundle\Entity\FacturaElectronica $comprobantes)
    {
        $this->comprobantes[] = $comprobantes;

        return $this;
    }

    /**
     * Remove comprobantes
     *
     * @param \VentasBundle\Entity\FacturaElectronica $comprobantes
     */
    public function removeComprobante(\VentasBundle\Entity\FacturaElectronica $comprobantes)
    {
        $this->comprobantes->removeElement($comprobantes);
    }

    /**
     * Get comprobantes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComprobantes()
    {
        return $this->comprobantes;
    }

    /**
     * Add cobroDetalles
     *
     * @param \VentasBundle\Entity\CobroDetalle $cobroDetalles
     * @return PagoCliente
     */
    public function addCobroDetalle(\VentasBundle\Entity\CobroDetalle $cobroDetalles)
    {
        $cobroDetalles->setPagoCliente($this);
        $this->cobroDetalles[] = $cobroDetalles;

        return $this;
    }

    /**
     * Remove cobroDetalles
     *
     * @param \VentasBundle\Entity\CobroDetalle $cobroDetalles
     */
    public function removeCobroDetalle(\VentasBundle\Entity\CobroDetalle $cobroDetalles)
    {
        $this->cobroDetalles->removeElement($cobroDetalles);
    }

    /**
     * Get cobroDetalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCobroDetalles()
    {
        return $this->cobroDetalles;
    }

    /**
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return PagoCliente
     */
    public function setCliente(\VentasBundle\Entity\Cliente $cliente = null)
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get cliente
     *
     * @return \VentasBundle\Entity\Cliente
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return PagoCliente
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set generaNotaCredito
     *
     * @param boolean $generaNotaCredito
     * @return PagoCliente
     */
    public function setGeneraNotaCredito($generaNotaCredito)
    {
        $this->generaNotaCredito = $generaNotaCredito;

        return $this;
    }

    /**
     * Get generaNotaCredito
     *
     * @return boolean
     */
    public function getGeneraNotaCredito()
    {
        return $this->generaNotaCredito;
    }

    /**
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return PagoCliente
     */
    public function setMoneda(\ConfigBundle\Entity\Moneda $moneda = null)
    {
        $this->moneda = $moneda;

        return $this;
    }

    /**
     * Get moneda
     *
     * @return \ConfigBundle\Entity\Moneda
     */
    public function getMoneda()
    {
        return $this->moneda;
    }

    /**
     * Set cotizacion
     *
     * @param string $cotizacion
     * @return PagoCliente
     */
    public function setCotizacion($cotizacion)
    {
        $this->cotizacion = $cotizacion;

        return $this;
    }

    /**
     * Get cotizacion
     *
     * @return string
     */
    public function getCotizacion()
    {
        return $this->cotizacion;
    }


    /**
     * Set notaDebCred
     *
     * @param \VentasBundle\Entity\NotaDebCred $notaDebCred
     * @return PagoCliente
     */
    public function setNotaDebCred(\VentasBundle\Entity\NotaDebCred $notaDebCred = null)
    {
        $this->notaDebCred = $notaDebCred;

        return $this;
    }

    /**
     * Get notaDebCred
     *
     * @return \VentasBundle\Entity\NotaDebCred
     */
    public function getNotaDebCred()
    {
        return $this->notaDebCred;
    }
}
