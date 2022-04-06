<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * VentasBundle\Entity\CobroDetalle
 * @ORM\Table(name="ventas_cobro_detalle")
 * @ORM\Entity()
 */
class CobroDetalle {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var integer $tipoPago
     * @ORM\Column(name="tipo_pago", type="string", nullable=true)
     */
    protected $tipoPago = 'CTACTE';
    /**
     * @var integer $importe
     * @ORM\Column(name="importe", type="decimal", scale=3 )
     */
    protected $importe=0;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Moneda")
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     */
    protected $moneda;

     /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\CobroDetalleTarjeta", cascade={"persist"})
     */
    private $datosTarjeta;

     /**
     * @ORM\OneToOne(targetEntity="ConfigBundle\Entity\Cheque", cascade={"persist"})
     */
    private $chequeRecibido;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cobro", inversedBy="detalles")
     * @ORM\JoinColumn(name="ventas_cobro_id", referencedColumnName="id")
     */
    protected $cobro;
    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\NotaDebCred", inversedBy="cobroDetalles")
     * @ORM\JoinColumn(name="ventas_notadebcred_id", referencedColumnName="id")
     */
    protected $notaDebCred;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\CajaApertura", inversedBy="movimientos")
     * @ORM\JoinColumn(name="ventas_caja_apertura_id", referencedColumnName="id")
     */
    protected $cajaApertura;

    public function getTipoComprobante(){
        if( $this->getCobro() ){
            // buscar factura
            $tipo = $this->getCobro()->getFacturaElectronica()->getTipo();
        }
        if( $this->getNotaDebCred()){
            // buscar nota
            $tipo = $this->getNotaDebCred()->getNotaElectronica()->getTipo();
        }
        return $tipo;
    }

    public function getComprobanteTxt(){
        if( $this->getCobro() ){
            // buscar factura
            $comprobante = $this->getCobro()->getFacturaElectronica()->getComprobanteTxt();
        }
        if( $this->getNotaDebCred()){
            // buscar nota
            $comprobante = $this->getNotaDebCred()->getNotaElectronica()->getComprobanteTxt();
        }
        return $comprobante;
    }
    public function getFecha(){
        if( $this->getCobro() ){
            $fecha = $this->getCobro()->getFechaCobro();
        }
        if( $this->getNotaDebCred()){
            // buscar nota
            $fecha = $this->getNotaDebCred()->getFecha();
        }
        return $fecha;
    }
    public function getCliente(){
        if( $this->getCobro() ){
            $cliente = $this->getCobro()->getCliente()->getNombre();
        }
        if( $this->getNotaDebCred()){
            // buscar nota
            $cliente = $this->getNotaDebCred()->getCliente()->getNombre();
        }
        return $cliente;
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
     * Set tipoPago
     *
     * @param string $tipoPago
     * @return CobroDetalle
     */
    public function setTipoPago($tipoPago)
    {
        $this->tipoPago = $tipoPago;

        return $this;
    }

    /**
     * Get tipoPago
     *
     * @return string
     */
    public function getTipoPago()
    {
        return $this->tipoPago;
    }

    /**
     * Set importe
     *
     * @param string $importe
     * @return CobroDetalle
     */
    public function setImporte($importe)
    {
        $this->importe = $importe;

        return $this;
    }

    /**
     * Get importe
     *
     * @return string
     */
    public function getImporte()
    {
        return $this->importe;
    }

    /**
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return CobroDetalle
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
     * Set datosTarjeta
     *
     * @param \VentasBundle\Entity\CobroDetalleTarjeta $datosTarjeta
     * @return CobroDetalle
     */
    public function setDatosTarjeta(\VentasBundle\Entity\CobroDetalleTarjeta $datosTarjeta = null)
    {
        $this->datosTarjeta = $datosTarjeta;

        return $this;
    }

    /**
     * Get datosTarjeta
     *
     * @return \VentasBundle\Entity\CobroDetalleTarjeta
     */
    public function getDatosTarjeta()
    {
        return $this->datosTarjeta;
    }

    /**
     * Set chequeRecibido
     *
     * @param \ConfigBundle\Entity\Cheque $chequeRecibido
     * @return CobroDetalle
     */
    public function setChequeRecibido(\ConfigBundle\Entity\Cheque $chequeRecibido = null)
    {
        $this->chequeRecibido = $chequeRecibido;

        return $this;
    }

    /**
     * Get chequeRecibido
     *
     * @return \ConfigBundle\Entity\Cheque
     */
    public function getChequeRecibido()
    {
        return $this->chequeRecibido;
    }

    /**
     * Set cobro
     *
     * @param \VentasBundle\Entity\Cobro $cobro
     * @return CobroDetalle
     */
    public function setCobro(\VentasBundle\Entity\Cobro $cobro = null)
    {
        $this->cobro = $cobro;

        return $this;
    }

    /**
     * Get cobro
     *
     * @return \VentasBundle\Entity\Cobro
     */
    public function getCobro()
    {
        return $this->cobro;
    }

    /**
     * Set notaDebCred
     *
     * @param \VentasBundle\Entity\NotaDebCred $notaDebCred
     * @return CobroDetalle
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

    /**
     * Set cajaApertura
     *
     * @param \VentasBundle\Entity\CajaApertura $cajaApertura
     * @return CobroDetalle
     */
    public function setCajaApertura(\VentasBundle\Entity\CajaApertura $cajaApertura = null)
    {
        $this->cajaApertura = $cajaApertura;

        return $this;
    }

    /**
     * Get cajaApertura
     *
     * @return \VentasBundle\Entity\CajaApertura
     */
    public function getCajaApertura()
    {
        return $this->cajaApertura;
    }
}
