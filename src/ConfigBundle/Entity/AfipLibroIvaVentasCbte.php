<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\AfipLibroIvaVentasCbte
 * @ORM\Table(name="afip_libro_iva_ventas_cte")
 * @ORM\Entity()
 */
class AfipLibroIvaVentasCbte {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="periodo", type="string", length=6, nullable=true)
     */
    protected $periodo;

    /**
     * @ORM\Column(name="fecha", type="string", length=8, nullable=true)
     */
    protected $fecha;

    /**
     * @ORM\Column(name="tipo_comprobantes", type="string", length=3, nullable=true)
     */
    protected $tipoComprobante;

    /**
     * @ORM\Column(name="punto_venta", type="string", length=5, nullable=true)
     */
    protected $puntoVenta;

    /**
     * @ORM\Column(name="numero_comprobante", type="string", length=20, nullable=true)
     */
    protected $numeroComprobante;

    /**
     * @ORM\Column(name="numero_comprobante_hasta", type="string", length=20, nullable=true)
     */
    protected $numeroComprobanteHasta;

    /**
     * @ORM\Column(name="cod_documento_comprador", type="string", length=2, nullable=true)
     */
    protected $codDocumentoComprador;

    /**
     * @ORM\Column(name="nro_documento_comprador", type="string", length=20, nullable=true)
     */
    protected $nroDocumentoComprador;

    /**
     * @ORM\Column(name="nombre_comprador", type="string", length=30, nullable=true)
     */
    protected $nombreComprador;

    /**
     * @ORM\Column(name="total_operacion", type="string", length=15, nullable=true)
     */
    protected $totalOperacion;

    /**
     * @ORM\Column(name="no_gravado", type="string", length=15, nullable=true)
     */
    protected $noGravado;

    /**
     * @ORM\Column(name="no_categorizado", type="string", length=15, nullable=true)
     */
    protected $noCategorizado;

    /**
     * @ORM\Column(name="exentos", type="string", length=15, nullable=true)
     */
    protected $extentos;

    /**
     * @ORM\Column(name="imp_nacionales", type="string", length=15, nullable=true)
     */
    protected $impNacionales;

    /**
     * @ORM\Column(name="perc_iibb", type="string", length=15, nullable=true)
     */
    protected $percIibb;

    /**
     * @ORM\Column(name="perc_municipal", type="string", length=15, nullable=true)
     */
    protected $percMunicipal;

    /**
     * @ORM\Column(name="imp_internos", type="string", length=15, nullable=true)
     */
    protected $impInternos;

    /**
     * @ORM\Column(name="codigo_moneda", type="string", length=3, nullable=true)
     */
    protected $codigoMoneda;

    /**
     * @ORM\Column(name="tipo_cambio", type="string", length=10, nullable=true)
     */
    protected $tipoCambio;

    /**
     * @ORM\Column(name="cant_alicuotas", type="string", length=1, nullable=true)
     */
    protected $cantAlicuotas = 1;

    /**
     * @ORM\Column(name="cod_operacion", type="string", length=1, nullable=true)
     */
    protected $codOperacion;

    /**
     * @ORM\Column(name="otros_tributos", type="string", length=15, nullable=true)
     */
    protected $otrosTributos;

    /**
     * @ORM\Column(name="fecha_vto_pago", type="string", length=8, nullable=true)
     */
    protected $fechaVtoPago;

    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\AfipLibroIvaVentasAlicuotas", mappedBy="comprobante",cascade={"persist", "remove"})
     */
    protected $detalleAlicuotas;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detalleAlicuotas = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set periodo
     *
     * @param string $periodo
     * @return AfipLibroIvaVentasCbte
     */
    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get periodo
     *
     * @return string 
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * Set fecha
     *
     * @param string $fecha
     * @return AfipLibroIvaVentasCbte
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return string 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set tipoComprobante
     *
     * @param string $tipoComprobante
     * @return AfipLibroIvaVentasCbte
     */
    public function setTipoComprobante($tipoComprobante)
    {
        $this->tipoComprobante = $tipoComprobante;

        return $this;
    }

    /**
     * Get tipoComprobante
     *
     * @return string 
     */
    public function getTipoComprobante()
    {
        return $this->tipoComprobante;
    }

    /**
     * Set puntoVenta
     *
     * @param string $puntoVenta
     * @return AfipLibroIvaVentasCbte
     */
    public function setPuntoVenta($puntoVenta)
    {
        $this->puntoVenta = $puntoVenta;

        return $this;
    }

    /**
     * Get puntoVenta
     *
     * @return string 
     */
    public function getPuntoVenta()
    {
        return $this->puntoVenta;
    }

    /**
     * Set numeroComprobante
     *
     * @param string $numeroComprobante
     * @return AfipLibroIvaVentasCbte
     */
    public function setNumeroComprobante($numeroComprobante)
    {
        $this->numeroComprobante = $numeroComprobante;

        return $this;
    }

    /**
     * Get numeroComprobante
     *
     * @return string 
     */
    public function getNumeroComprobante()
    {
        return $this->numeroComprobante;
    }

    /**
     * Set numeroComprobanteHasta
     *
     * @param string $numeroComprobanteHasta
     * @return AfipLibroIvaVentasCbte
     */
    public function setNumeroComprobanteHasta($numeroComprobanteHasta)
    {
        $this->numeroComprobanteHasta = $numeroComprobanteHasta;

        return $this;
    }

    /**
     * Get numeroComprobanteHasta
     *
     * @return string 
     */
    public function getNumeroComprobanteHasta()
    {
        return $this->numeroComprobanteHasta;
    }

    /**
     * Set codDocumentoComprador
     *
     * @param string $codDocumentoComprador
     * @return AfipLibroIvaVentasCbte
     */
    public function setCodDocumentoComprador($codDocumentoComprador)
    {
        $this->codDocumentoComprador = $codDocumentoComprador;

        return $this;
    }

    /**
     * Get codDocumentoComprador
     *
     * @return string 
     */
    public function getCodDocumentoComprador()
    {
        return $this->codDocumentoComprador;
    }

    /**
     * Set nroDocumentoComprador
     *
     * @param string $nroDocumentoComprador
     * @return AfipLibroIvaVentasCbte
     */
    public function setNroDocumentoComprador($nroDocumentoComprador)
    {
        $this->nroDocumentoComprador = $nroDocumentoComprador;

        return $this;
    }

    /**
     * Get nroDocumentoComprador
     *
     * @return string 
     */
    public function getNroDocumentoComprador()
    {
        return $this->nroDocumentoComprador;
    }

    /**
     * Set nombreComprador
     *
     * @param string $nombreComprador
     * @return AfipLibroIvaVentasCbte
     */
    public function setNombreComprador($nombreComprador)
    {
        $this->nombreComprador = $nombreComprador;

        return $this;
    }

    /**
     * Get nombreComprador
     *
     * @return string 
     */
    public function getNombreComprador()
    {
        return $this->nombreComprador;
    }

    /**
     * Set totalOperacion
     *
     * @param string $totalOperacion
     * @return AfipLibroIvaVentasCbte
     */
    public function setTotalOperacion($totalOperacion)
    {
        $this->totalOperacion = $totalOperacion;

        return $this;
    }

    /**
     * Get totalOperacion
     *
     * @return string 
     */
    public function getTotalOperacion()
    {
        return $this->totalOperacion;
    }

    /**
     * Set noGravado
     *
     * @param string $noGravado
     * @return AfipLibroIvaVentasCbte
     */
    public function setNoGravado($noGravado)
    {
        $this->noGravado = $noGravado;

        return $this;
    }

    /**
     * Get noGravado
     *
     * @return string 
     */
    public function getNoGravado()
    {
        return $this->noGravado;
    }

    /**
     * Set noCategorizado
     *
     * @param string $noCategorizado
     * @return AfipLibroIvaVentasCbte
     */
    public function setNoCategorizado($noCategorizado)
    {
        $this->noCategorizado = $noCategorizado;

        return $this;
    }

    /**
     * Get noCategorizado
     *
     * @return string 
     */
    public function getNoCategorizado()
    {
        return $this->noCategorizado;
    }

    /**
     * Set extentos
     *
     * @param string $extentos
     * @return AfipLibroIvaVentasCbte
     */
    public function setExtentos($extentos)
    {
        $this->extentos = $extentos;

        return $this;
    }

    /**
     * Get extentos
     *
     * @return string 
     */
    public function getExtentos()
    {
        return $this->extentos;
    }

    /**
     * Set impNacionales
     *
     * @param string $impNacionales
     * @return AfipLibroIvaVentasCbte
     */
    public function setImpNacionales($impNacionales)
    {
        $this->impNacionales = $impNacionales;

        return $this;
    }

    /**
     * Get impNacionales
     *
     * @return string 
     */
    public function getImpNacionales()
    {
        return $this->impNacionales;
    }

    /**
     * Set percIibb
     *
     * @param string $percIibb
     * @return AfipLibroIvaVentasCbte
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
     * Set percMunicipal
     *
     * @param string $percMunicipal
     * @return AfipLibroIvaVentasCbte
     */
    public function setPercMunicipal($percMunicipal)
    {
        $this->percMunicipal = $percMunicipal;

        return $this;
    }

    /**
     * Get percMunicipal
     *
     * @return string 
     */
    public function getPercMunicipal()
    {
        return $this->percMunicipal;
    }

    /**
     * Set impInternos
     *
     * @param string $impInternos
     * @return AfipLibroIvaVentasCbte
     */
    public function setImpInternos($impInternos)
    {
        $this->impInternos = $impInternos;

        return $this;
    }

    /**
     * Get impInternos
     *
     * @return string 
     */
    public function getImpInternos()
    {
        return $this->impInternos;
    }

    /**
     * Set codigoMoneda
     *
     * @param string $codigoMoneda
     * @return AfipLibroIvaVentasCbte
     */
    public function setCodigoMoneda($codigoMoneda)
    {
        $this->codigoMoneda = $codigoMoneda;

        return $this;
    }

    /**
     * Get codigoMoneda
     *
     * @return string 
     */
    public function getCodigoMoneda()
    {
        return $this->codigoMoneda;
    }

    /**
     * Set tipoCambio
     *
     * @param string $tipoCambio
     * @return AfipLibroIvaVentasCbte
     */
    public function setTipoCambio($tipoCambio)
    {
        $this->tipoCambio = $tipoCambio;

        return $this;
    }

    /**
     * Get tipoCambio
     *
     * @return string 
     */
    public function getTipoCambio()
    {
        return $this->tipoCambio;
    }

    /**
     * Set cantAlicuotas
     *
     * @param string $cantAlicuotas
     * @return AfipLibroIvaVentasCbte
     */
    public function setCantAlicuotas($cantAlicuotas)
    {
        $this->cantAlicuotas = $cantAlicuotas;

        return $this;
    }

    /**
     * Get cantAlicuotas
     *
     * @return string 
     */
    public function getCantAlicuotas()
    {
        return $this->cantAlicuotas;
    }

    /**
     * Set codOperacion
     *
     * @param string $codOperacion
     * @return AfipLibroIvaVentasCbte
     */
    public function setCodOperacion($codOperacion)
    {
        $this->codOperacion = $codOperacion;

        return $this;
    }

    /**
     * Get codOperacion
     *
     * @return string 
     */
    public function getCodOperacion()
    {
        return $this->codOperacion;
    }

    /**
     * Set otrosTributos
     *
     * @param string $otrosTributos
     * @return AfipLibroIvaVentasCbte
     */
    public function setOtrosTributos($otrosTributos)
    {
        $this->otrosTributos = $otrosTributos;

        return $this;
    }

    /**
     * Get otrosTributos
     *
     * @return string 
     */
    public function getOtrosTributos()
    {
        return $this->otrosTributos;
    }

    /**
     * Set fechaVtoPago
     *
     * @param string $fechaVtoPago
     * @return AfipLibroIvaVentasCbte
     */
    public function setFechaVtoPago($fechaVtoPago)
    {
        $this->fechaVtoPago = $fechaVtoPago;

        return $this;
    }

    /**
     * Get fechaVtoPago
     *
     * @return string 
     */
    public function getFechaVtoPago()
    {
        return $this->fechaVtoPago;
    }

    /**
     * Add detalleAlicuotas
     *
     * @param \ConfigBundle\Entity\AfipLibroIvaVentasAlicuotas $detalleAlicuotas
     * @return AfipLibroIvaVentasCbte
     */
    public function addDetalleAlicuota(\ConfigBundle\Entity\AfipLibroIvaVentasAlicuotas $detalleAlicuotas)
    {
        $this->detalleAlicuotas[] = $detalleAlicuotas;

        return $this;
    }

    /**
     * Remove detalleAlicuotas
     *
     * @param \ConfigBundle\Entity\AfipLibroIvaVentasAlicuotas $detalleAlicuotas
     */
    public function removeDetalleAlicuota(\ConfigBundle\Entity\AfipLibroIvaVentasAlicuotas $detalleAlicuotas)
    {
        $this->detalleAlicuotas->removeElement($detalleAlicuotas);
    }

    /**
     * Get detalleAlicuotas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDetalleAlicuotas()
    {
        return $this->detalleAlicuotas;
    }
}
