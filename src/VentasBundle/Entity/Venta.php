<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\Venta
 * @ORM\Table(name="ventas_venta")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\VentaRepository")
 */
class Venta {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var integer $nroOperacion
     * @ORM\Column(name="nro_operacion", type="integer")     
     */
    protected $nroOperacion = '';
    /**
     * @var datetime $fechaVenta
     * @ORM\Column(name="fecha_venta", type="datetime", nullable=false)
     */
    protected $fechaVenta;
    /**
     * Estados: PENDIENTE - FACTURADO - ANULADO
     */
    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado = 'PENDIENTE';

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\PuntoVenta", inversedBy="Ventas")
     * @ORM\JoinColumn(name="punto_venta_id", referencedColumnName="id")
     */
    protected $puntoVenta;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="Ventas")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     */
    protected $cliente;
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
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Transporte")
     * @ORM\JoinColumn(name="transporte_id", referencedColumnName="id")
     */
    protected $transporte;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\VentaDetalle", mappedBy="venta",cascade={"persist", "remove"})
     */
    protected $detalles;

    public function getTotal() {
        $total = 0;
        foreach ($this->detalles as $item) {
            $total = $total + $item->getTotal();
        }
        return $total;
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
     * Set fechaVenta
     *
     * @param \DateTime $fechaVenta
     * @return Venta
     */
    public function setFechaVenta($fechaVenta)
    {
        $this->fechaVenta = $fechaVenta;

        return $this;
    }

    /**
     * Get fechaVenta
     *
     * @return \DateTime 
     */
    public function getFechaVenta()
    {
        return $this->fechaVenta;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Venta
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return Venta
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
     * Set puntoVenta
     *
     * @param \ConfigBundle\Entity\PuntoVenta $puntoVenta
     * @return Venta
     */
    public function setPuntoVenta(\ConfigBundle\Entity\PuntoVenta $puntoVenta = null)
    {
        $this->puntoVenta = $puntoVenta;

        return $this;
    }

    /**
     * Get puntoVenta
     *
     * @return \ConfigBundle\Entity\PuntoVenta 
     */
    public function getPuntoVenta()
    {
        return $this->puntoVenta;
    }

    /**
     * Set formaPago
     *
     * @param \ConfigBundle\Entity\FormaPago $formaPago
     * @return Venta
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
     * @return Venta
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
     * Set transporte
     *
     * @param \ConfigBundle\Entity\Transporte $transporte
     * @return Venta
     */
    public function setTransporte(\ConfigBundle\Entity\Transporte $transporte = null)
    {
        $this->transporte = $transporte;

        return $this;
    }

    /**
     * Get transporte
     *
     * @return \ConfigBundle\Entity\Transporte 
     */
    public function getTransporte()
    {
        return $this->transporte;
    }

    /**
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return Venta
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
     * Constructor
     */
    public function __construct()
    {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add detalles
     *
     * @param \VentasBundle\Entity\VentaDetalle $detalles
     * @return Venta
     */
    public function addDetalle(\VentasBundle\Entity\VentaDetalle $detalles)
    {
        $detalles->setVenta($this);        
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \VentasBundle\Entity\VentaDetalle $detalles
     */
    public function removeDetalle(\VentasBundle\Entity\VentaDetalle $detalles)
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
     * Set nroOperacion
     *
     * @param integer $nroOperacion
     * @return Venta
     */
    public function setNroOperacion($nroOperacion)
    {
        $this->nroOperacion = $nroOperacion;

        return $this;
    }

    /**
     * Get nroOperacion
     *
     * @return integer 
     */
    public function getNroOperacion()
    {
        return $this->nroOperacion;
    }
}
