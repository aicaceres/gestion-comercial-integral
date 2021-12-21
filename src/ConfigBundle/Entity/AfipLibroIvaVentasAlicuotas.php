<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\AfipLibroIvaVentasAlicuotas
 * @ORM\Table(name="afip_libro_iva_ventas_alicuotas")
 * @ORM\Entity()
 */
class AfipLibroIvaVentasAlicuotas {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @ORM\Column(name="neto_gravado", type="string", length=15, nullable=true)
     */
    protected $netoGravado;

    /**
     * @ORM\Column(name="alicuota", type="string", length=4, nullable=true)
     */
    protected $alicuota;

    /**
     * @ORM\Column(name="liquidado", type="string", length=15, nullable=true)
     */
    protected $liquidado;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\AfipLibroIvaVentasCbte", inversedBy="detalleAlicuotas")
     * @ORM\JoinColumn(name="afip_libro_iva_ventas_cte_id", referencedColumnName="id")
     */
    protected $comprobante;


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
     * Set tipoComprobante
     *
     * @param string $tipoComprobante
     * @return AfipLibroIvaVentasAlicuotas
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
     * @return AfipLibroIvaVentasAlicuotas
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
     * @return AfipLibroIvaVentasAlicuotas
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
     * Set netoGravado
     *
     * @param string $netoGravado
     * @return AfipLibroIvaVentasAlicuotas
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
     * @return AfipLibroIvaVentasAlicuotas
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
     * Set liquidado
     *
     * @param string $liquidado
     * @return AfipLibroIvaVentasAlicuotas
     */
    public function setLiquidado($liquidado)
    {
        $this->liquidado = $liquidado;

        return $this;
    }

    /**
     * Get liquidado
     *
     * @return string 
     */
    public function getLiquidado()
    {
        return $this->liquidado;
    }

    /**
     * Set comprobante
     *
     * @param \ConfigBundle\Entity\AfipLibroIvaVentasCbte $comprobante
     * @return AfipLibroIvaVentasAlicuotas
     */
    public function setComprobante(\ConfigBundle\Entity\AfipLibroIvaVentasCbte $comprobante = null)
    {
        $this->comprobante = $comprobante;

        return $this;
    }

    /**
     * Get comprobante
     *
     * @return \ConfigBundle\Entity\AfipLibroIvaVentasCbte 
     */
    public function getComprobante()
    {
        return $this->comprobante;
    }
}
