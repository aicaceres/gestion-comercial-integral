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
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\FacturaElectronica", mappedBy="notaDebCred", cascade={"persist"})
     */
    private $notaElectronica;

    /**
     * @var integer $descuentoRecargo
     * @ORM\Column(name="descuentoRecargo", type="decimal", scale=2,nullable=true )
     */
    protected $descuentoRecargo;

    /**
     * @var integer $iva
     * @ORM\Column(name="iva", type="decimal", scale=2,nullable=true )
     */
    protected $iva;

    /**
     * @var integer $percIibb
     * @ORM\Column(name="perc_iibb", type="decimal", scale=2,nullable=true )
     */
    protected $percIibb;

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
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\FormaPago")
     * @ORM\JoinColumn(name="forma_pago_id", referencedColumnName="id")
     **/
    protected $formaPago;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrecioLista")
     * @ORM\JoinColumn(name="precio_lista_id", referencedColumnName="id")
     */
    protected $precioLista;

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
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="notasDebCredVenta")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     */
    protected $cliente;
    /**
     * @var string $nombreCliente
     * @ORM\Column(name="nombre_cliente", type="string", nullable=true)
     */
    protected $nombreCliente;
    /**
     * @var string $tipoDocumentoCliente
     * @ORM\Column(name="tipo_documento_cliente", type="integer", nullable=true)
     */
    protected $tipoDocumentoCliente;
    /**
     * @var string $nroDocumentoCliente
     * @ORM\Column(name="nro_documento_cliente", type="string", length=13, nullable=true)
     */
    protected $nroDocumentoCliente;

    /**
     * @ORM\Column(name="concepto", type="text", nullable=true)
     */
    protected $concepto;

    /**
     * @ORM\ManyToMany(targetEntity="VentasBundle\Entity\FacturaElectronica")
     * @ORM\JoinTable(name="facturas_x_notadebcred_ventas",
     *      joinColumns={@ORM\JoinColumn(name="ventas_nota_debcred_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ventas_factura_electronica_id", referencedColumnName="id")}
     * )
     */
    protected $facturas;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\NotaDebCredDetalle", mappedBy="notaDebCred",cascade={"persist", "remove"})
     */
    protected $detalles;

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
    }

    public function getPagoTxt(){
        return ($this->getFormaPago()->getCuentaCorriente()) ? '' : $this->getFormaPago()->getNombre();
    }

    public function __toString() {
        return $this->getNotaElectronica()->getComprobanteTxt();
    }

    public function getSignoNota() {
        return ($this->signo == '-') ? 'Crédito' : 'Débito';
    }

    public function getClienteTxt(){
        $nombre = $this->getCliente()->getNombre();
        if( $this->nombreCliente ){
            $nombre = $nombre . ' - ' . $this->nombreCliente;
        }
        return $nombre;
    }

    /**
     *  TOTALIZADOS DE LA OPERACION
     */
    public function getSubTotal() {
        $total = 0;
        foreach ($this->detalles as $item) {
            $total = $total + $item->getTotalItem();
        }
        return $total;
    }
    public function getTotalDescuentoRecargo() {
        $total = 0;
        $categIva = $this->getCliente()->getCategoriaIva()->getNombre();
        if( $categIva == 'I' || $categIva == 'M'){
            // suma de descuentos x item
            foreach ($this->detalles as $item) {
                $total = $total + $item->getDtoRecItem();
            }
            $total = $total / $this->getCotizacion();
        }else{
            // descuento sobre el subtotal
            $total = $this->getSubTotal() * ( $this->getDescuentoRecargo()/100 );
        }
        return round( ($total ) ,3);
    }
    public function getTotalIva(){
        $total = 0;
        foreach ($this->detalles as $item) {
            $total = $total + $item->getIvaItem();
        }
        return round( ($total / $this->getCotizacion()) ,3);
    }
    public function getTotalIibb(){
        $monto = $this->getSubTotal() + $this->getTotalDescuentoRecargo();
        return $monto * 0.035;
    }
    public function getMontoTotal(){
        $categIva = $this->getCliente()->getCategoriaIva()->getNombre();
        if( $categIva == 'I' || $categIva == 'M'){
            // total con iva e iibb
            $total = $this->getSubTotal() + $this->getTotalDescuentoRecargo() + $this->getTotalIva();
            if( $categIva == 'I' ){
                $total = $total + $this->getTotalIibb();
            }
        }else{
            // subtotal +/- descuentoRecargo
            $descRec = $this->getSubTotal() * ( $this->getDescuentoRecargo()/100 );
            $total = $this->getSubTotal() + $descRec  ;
        }
        return round($total,2) ;
    }

    /**
     *  FIN TOTALIZADOS
     */



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
     * @return NotaDebCred
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
     * Set estado
     *
     * @param string $estado
     * @return NotaDebCred
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set signo
     *
     * @param string $signo
     * @return NotaDebCred
     */
    public function setSigno($signo)
    {
        $this->signo = $signo;

        return $this;
    }

    /**
     * Get signo
     *
     * @return string
     */
    public function getSigno()
    {
        return $this->signo;
    }

    /**
     * Set iva
     *
     * @param string $iva
     * @return NotaDebCred
     */
    public function setIva($iva)
    {
        $this->iva = $iva;

        return $this;
    }

    /**
     * Get iva
     *
     * @return string
     */
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return NotaDebCred
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
     * Set saldo
     *
     * @param string $saldo
     * @return NotaDebCred
     */
    public function setSaldo($saldo)
    {
        $this->saldo = $saldo;

        return $this;
    }

    /**
     * Get saldo
     *
     * @return string
     */
    public function getSaldo()
    {
        return $this->saldo;
    }

    /**
     * Set cotizacion
     *
     * @param string $cotizacion
     * @return NotaDebCred
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
     * Set nombreCliente
     *
     * @param string $nombreCliente
     * @return NotaDebCred
     */
    public function setNombreCliente($nombreCliente)
    {
        $this->nombreCliente = $nombreCliente;

        return $this;
    }

    /**
     * Get nombreCliente
     *
     * @return string
     */
    public function getNombreCliente()
    {
        return $this->nombreCliente;
    }

    /**
     * Set tipoDocumentoCliente
     *
     * @param integer $tipoDocumentoCliente
     * @return NotaDebCred
     */
    public function setTipoDocumentoCliente($tipoDocumentoCliente)
    {
        $this->tipoDocumentoCliente = $tipoDocumentoCliente;

        return $this;
    }

    /**
     * Get tipoDocumentoCliente
     *
     * @return integer
     */
    public function getTipoDocumentoCliente()
    {
        return $this->tipoDocumentoCliente;
    }

    /**
     * Set nroDocumentoCliente
     *
     * @param string $nroDocumentoCliente
     * @return NotaDebCred
     */
    public function setNroDocumentoCliente($nroDocumentoCliente)
    {
        $this->nroDocumentoCliente = $nroDocumentoCliente;

        return $this;
    }

    /**
     * Get nroDocumentoCliente
     *
     * @return string
     */
    public function getNroDocumentoCliente()
    {
        return $this->nroDocumentoCliente;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return NotaDebCred
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
     * Set updated
     *
     * @param \DateTime $updated
     * @return NotaDebCred
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set notaElectronica
     *
     * @param \VentasBundle\Entity\FacturaElectronica $notaElectronica
     * @return NotaDebCred
     */
    public function setNotaElectronica(\VentasBundle\Entity\FacturaElectronica $notaElectronica = null)
    {
        $this->notaElectronica = $notaElectronica;

        return $this;
    }

    /**
     * Get notaElectronica
     *
     * @return \VentasBundle\Entity\FacturaElectronica
     */
    public function getNotaElectronica()
    {
        return $this->notaElectronica;
    }

    /**
     * Set formaPago
     *
     * @param \ConfigBundle\Entity\FormaPago $formaPago
     * @return NotaDebCred
     */
    public function setFormaPago(\ConfigBundle\Entity\FormaPago $formaPago = null)
    {
        $this->formaPago = $formaPago;

        return $this;
    }

    /**
     * Get formaPago
     *
     * @return \ConfigBundle\Entity\FormaPago
     */
    public function getFormaPago()
    {
        return $this->formaPago;
    }

    /**
     * Set precioLista
     *
     * @param \AppBundle\Entity\PrecioLista $precioLista
     * @return NotaDebCred
     */
    public function setPrecioLista(\AppBundle\Entity\PrecioLista $precioLista = null)
    {
        $this->precioLista = $precioLista;

        return $this;
    }

    /**
     * Get precioLista
     *
     * @return \AppBundle\Entity\PrecioLista
     */
    public function getPrecioLista()
    {
        return $this->precioLista;
    }

    /**
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return NotaDebCred
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return NotaDebCred
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
     * Add facturas
     *
     * @param \VentasBundle\Entity\FacturaElectronica $facturas
     * @return NotaDebCred
     */
    public function addFactura(\VentasBundle\Entity\FacturaElectronica $facturas)
    {
        $this->facturas[] = $facturas;

        return $this;
    }

    /**
     * Remove facturas
     *
     * @param \VentasBundle\Entity\FacturaElectronica $facturas
     */
    public function removeFactura(\VentasBundle\Entity\FacturaElectronica $facturas)
    {
        $this->facturas->removeElement($facturas);
    }

    /**
     * Get facturas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFacturas()
    {
        return $this->facturas;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return NotaDebCred
     */
    public function setUnidadNegocio(\ConfigBundle\Entity\UnidadNegocio $unidadNegocio = null)
    {
        $this->unidadNegocio = $unidadNegocio;

        return $this;
    }

    /**
     * Get unidadNegocio
     *
     * @return \ConfigBundle\Entity\UnidadNegocio
     */
    public function getUnidadNegocio()
    {
        return $this->unidadNegocio;
    }

    /**
     * Add detalles
     *
     * @param \VentasBundle\Entity\NotaDebCredDetalle $detalles
     * @return NotaDebCred
     */
    public function addDetalle(\VentasBundle\Entity\NotaDebCredDetalle $detalles)
    {
        $detalles->setNotaDebCred($this);
        $this->detalles[] = $detalles;

        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \VentasBundle\Entity\NotaDebCredDetalle $detalles
     */
    public function removeDetalle(\VentasBundle\Entity\NotaDebCredDetalle $detalles)
    {
        $this->detalles->removeElement($detalles);
    }

    /**
     * Get detalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalles()
    {
        return $this->detalles;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return NotaDebCred
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
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return NotaDebCred
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set percIibb
     *
     * @param string $percIibb
     * @return NotaDebCred
     */
    public function setPercIibb($percIibb)
    {
        $this->percIibb = $percIibb;

        return $this;
    }

    /**
     * Get percIibb
     *
     * @return string
     */
    public function getPercIibb()
    {
        return $this->percIibb;
    }

    /**
     * Set descuentoRecargo
     *
     * @param string $descuentoRecargo
     * @return NotaDebCred
     */
    public function setDescuentoRecargo($descuentoRecargo)
    {
        $this->descuentoRecargo = $descuentoRecargo;

        return $this;
    }

    /**
     * Get descuentoRecargo
     *
     * @return string
     */
    public function getDescuentoRecargo()
    {
        return $this->descuentoRecargo;
    }

    /**
     * Set concepto
     *
     * @param string $concepto
     * @return NotaDebCred
     */
    public function setConcepto($concepto)
    {
        $this->concepto = $concepto;

        return $this;
    }

    /**
     * Get concepto
     *
     * @return string 
     */
    public function getConcepto()
    {
        return $this->concepto;
    }
}
