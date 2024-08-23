<?php

namespace ComprasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ComprasBundle\Entity\FacturaAlicuota
 * @ORM\Table(name="compras_factura_alicuota")
 * @ORM\Entity()
 */
class FacturaAlicuota {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="neto_gravado", type="decimal", precision=20, scale=2 )
     */
    protected $netoGravado;
    /**
     * @ORM\Column(name="liquidado", type="decimal", precision=20, scale=2 )
     */
    protected $liquidado;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\AfipAlicuota")
     * @ORM\JoinColumn(name="afip_alicuota_id", referencedColumnName="id")
     * */
    protected $afipAlicuota;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Factura", inversedBy="alicuotas")
     * @ORM\JoinColumn(name="compras_factura_id", referencedColumnName="id")
     */
    protected $factura;


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
     * Set netoGravado
     *
     * @param string $netoGravado
     * @return FacturaAlicuota
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
     * Set liquidado
     *
     * @param string $liquidado
     * @return FacturaAlicuota
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
     * Set afipAlicuota
     *
     * @param \ConfigBundle\Entity\AfipAlicuota $afipAlicuota
     * @return FacturaAlicuota
     */
    public function setAfipAlicuota(\ConfigBundle\Entity\AfipAlicuota $afipAlicuota = null)
    {
        $this->afipAlicuota = $afipAlicuota;

        return $this;
    }

    /**
     * Get afipAlicuota
     *
     * @return \ConfigBundle\Entity\AfipAlicuota
     */
    public function getAfipAlicuota()
    {
        return $this->afipAlicuota;
    }

    /**
     * Set factura
     *
     * @param \ComprasBundle\Entity\Factura $factura
     * @return FacturaAlicuota
     */
    public function setFactura(\ComprasBundle\Entity\Factura $factura = null)
    { 
        $this->factura = $factura;

        return $this;
    }

    /**
     * Get factura
     *
     * @return \ComprasBundle\Entity\Factura
     */
    public function getFactura()
    {
        return $this->factura;
    }
}
