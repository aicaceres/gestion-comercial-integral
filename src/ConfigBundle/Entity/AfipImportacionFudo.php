<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\AfipImportacionFudo
 * @ORM\Table(name="afip_importacion_fudo")
 * @ORM\Entity()
 */
class AfipImportacionFudo {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @ORM\Column(name="cond_fiscal", type="string", nullable=true)
     */
    protected $condFiscal;

    /**
     * @ORM\Column(name="nombre_cliente", type="string", nullable=true)
     */
    protected $nombreCliente;

    /**
     * @ORM\Column(name="direccion_cliente", type="string", nullable=true)
     */
    protected $direccionCliente;

    /**
     * @ORM\Column(name="cuit_cliente", type="string", nullable=true)
     */
    protected $cuitCliente;

    /**
     * @ORM\Column(name="remito", type="string", nullable=true)
     */
    protected $remito;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return AfipImportacionFudo
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set fecha
     *
     * @param string $fecha
     * @return AfipImportacionFudo
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
     * @return AfipImportacionFudo
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
     * Set letra
     *
     * @param string $letra
     * @return AfipImportacionFudo
     */
    public function setLetra($letra)
    {
        $this->letra = $letra;

        return $this;
    }

    /**
     * Get letra
     *
     * @return string 
     */
    public function getLetra()
    {
        return $this->letra;
    }

    /**
     * Set puntoVenta
     *
     * @param string $puntoVenta
     * @return AfipImportacionFudo
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
     * @return AfipImportacionFudo
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
     * Set condFiscal
     *
     * @param string $condFiscal
     * @return AfipImportacionFudo
     */
    public function setCondFiscal($condFiscal)
    {
        $this->condFiscal = $condFiscal;

        return $this;
    }

    /**
     * Get condFiscal
     *
     * @return string 
     */
    public function getCondFiscal()
    {
        return $this->condFiscal;
    }

    /**
     * Set nombreCliente
     *
     * @param string $nombreCliente
     * @return AfipImportacionFudo
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
     * Set direccionCliente
     *
     * @param string $direccionCliente
     * @return AfipImportacionFudo
     */
    public function setDireccionCliente($direccionCliente)
    {
        $this->direccionCliente = $direccionCliente;

        return $this;
    }

    /**
     * Get direccionCliente
     *
     * @return string 
     */
    public function getDireccionCliente()
    {
        return $this->direccionCliente;
    }

    /**
     * Set cuitCliente
     *
     * @param string $cuitCliente
     * @return AfipImportacionFudo
     */
    public function setCuitCliente($cuitCliente)
    {
        $this->cuitCliente = $cuitCliente;

        return $this;
    }

    /**
     * Get cuitCliente
     *
     * @return string 
     */
    public function getCuitCliente()
    {
        return $this->cuitCliente;
    }

    /**
     * Set remito
     *
     * @param string $remito
     * @return AfipImportacionFudo
     */
    public function setRemito($remito)
    {
        $this->remito = $remito;

        return $this;
    }

    /**
     * Get remito
     *
     * @return string 
     */
    public function getRemito()
    {
        return $this->remito;
    }

    /**
     * Set netoGravado
     *
     * @param string $netoGravado
     * @return AfipImportacionFudo
     */
    public function setNetoGravado($netoGravado)
    {
        $this->netoGravado = $netoGravado;

        return $this;
    }

    /**
     * Get netoGravado
     *
     * @return string 
     */
    public function getNetoGravado()
    {
        return $this->netoGravado;
    }

    /**
     * Set alicuota
     *
     * @param string $alicuota
     * @return AfipImportacionFudo
     */
    public function setAlicuota($alicuota)
    {
        $this->alicuota = $alicuota;

        return $this;
    }

    /**
     * Get alicuota
     *
     * @return string 
     */
    public function getAlicuota()
    {
        return $this->alicuota;
    }

    /**
     * Set ivaLiquidado
     *
     * @param string $ivaLiquidado
     * @return AfipImportacionFudo
     */
    public function setIvaLiquidado($ivaLiquidado)
    {
        $this->ivaLiquidado = $ivaLiquidado;

        return $this;
    }

    /**
     * Get ivaLiquidado
     *
     * @return string 
     */
    public function getIvaLiquidado()
    {
        return $this->ivaLiquidado;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return AfipImportacionFudo
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
     * Set procesado
     *
     * @param boolean $procesado
     * @return AfipImportacionFudo
     */
    public function setProcesado($procesado)
    {
        $this->procesado = $procesado;

        return $this;
    }

    /**
     * Get procesado
     *
     * @return boolean 
     */
    public function getProcesado()
    {
        return $this->procesado;
    }
}
