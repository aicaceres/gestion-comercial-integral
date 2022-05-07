<?php

namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ComprasBundle\Entity\RetencionGanancia
 * @ORM\Table(name="proveedor_retencion_ganancia")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\ProveedorRepository")
 */
class RetencionGanancia {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $periodo
     * @ORM\Column(name="periodo", type="string", length=6)
     */
    protected $periodo;

    /**
     * @var integer $acumuladoTotal
     * @ORM\Column(name="acumulado_total", type="decimal", scale=3 )
     */
    protected $acumuladoTotal;
    /**
     * @var integer $acumuladoRetencion
     * @ORM\Column(name="acumulado_retencion", type="decimal", scale=3 )
     */
    protected $acumuladoRetencion;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Proveedor", inversedBy="acumuladosGanancias")
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     */
    protected $proveedor;


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
     * @return RetencionGanancia
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
     * Set acumuladoTotal
     *
     * @param string $acumuladoTotal
     * @return RetencionGanancia
     */
    public function setAcumuladoTotal($acumuladoTotal)
    {
        $this->acumuladoTotal = $acumuladoTotal;

        return $this;
    }

    /**
     * Get acumuladoTotal
     *
     * @return string
     */
    public function getAcumuladoTotal()
    {
        return $this->acumuladoTotal;
    }

    /**
     * Set acumuladoRetencion
     *
     * @param string $acumuladoRetencion
     * @return RetencionGanancia
     */
    public function setAcumuladoRetencion($acumuladoRetencion)
    {
        $this->acumuladoRetencion = $acumuladoRetencion;

        return $this;
    }

    /**
     * Get acumuladoRetencion
     *
     * @return string
     */
    public function getAcumuladoRetencion()
    {
        return $this->acumuladoRetencion;
    }

    /**
     * Set proveedor
     *
     * @param \ComprasBundle\Entity\Proveedor $proveedor
     * @return RetencionGanancia
     */
    public function setProveedor(\ComprasBundle\Entity\Proveedor $proveedor = null)
    {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \ComprasBundle\Entity\Proveedor
     */
    public function getProveedor()
    {
        return $this->proveedor;
    }
}
