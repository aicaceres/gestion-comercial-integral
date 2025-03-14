<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ConfigBundle\Entity\Caja
 *
 * @ORM\Table(name="caja")
 * @ORM\Entity()
 * @UniqueEntity( fields={"hostname"}, errorPath="hostname", message="El Nombre de equipo debe ser unico." )
 * @UniqueEntity( fields={"ptoVtaIfu"}, errorPath="ptoVtaIfu", message="El Punto de Venta debe ser unico." )
 */
class Caja
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $nombre;
    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="string", nullable=true)
     */
    protected $descripcion;

    /**
     * @ORM\Column(name="abierta", type="boolean")
     */
    protected $abierta = false;

    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true;

    /**
     * @ORM\Column(name="hostname", type="string", unique=true)
     */
    protected $hostname;
    /**
     * @ORM\Column(name="ptovta_ws", type="string")
     */
    protected $ptoVtaWs;
    /**
     * @ORM\Column(name="ptovta_ifu", type="string", unique=true)
     */
    protected $ptoVtaIfu;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\CajaApertura", mappedBy="caja",cascade={"remove"})
     */
    protected $aperturas;

    public function __toString() {
        return $this->nombre;
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
     * Set nombre
     *
     * @param string $nombre
     * @return Caja
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Caja
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
     * Set activo
     *
     * @param boolean $activo
     * @return Caja
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Caja
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
     * Set abierta
     *
     * @param boolean $abierta
     * @return Caja
     */
    public function setAbierta($abierta)
    {
        $this->abierta = $abierta;

        return $this;
    }

    /**
     * Get abierta
     *
     * @return boolean
     */
    public function getAbierta()
    {
        return $this->abierta;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aperturas = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add aperturas
     *
     * @param \VentasBundle\Entity\CajaApertura $aperturas
     * @return Caja
     */
    public function addApertura(\VentasBundle\Entity\CajaApertura $aperturas)
    {
        $this->aperturas[] = $aperturas;

        return $this;
    }

    /**
     * Remove aperturas
     *
     * @param \VentasBundle\Entity\CajaApertura $aperturas
     */
    public function removeApertura(\VentasBundle\Entity\CajaApertura $aperturas)
    {
        $this->aperturas->removeElement($aperturas);
    }

    /**
     * Get aperturas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAperturas()
    {
        return $this->aperturas;
    }

    /**
     * Set hostname
     *
     * @param string $hostname
     * @return Caja
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Set ptoVtaWs
     *
     * @param string $ptoVtaWs
     * @return Caja
     */
    public function setPtoVtaWs($ptoVtaWs)
    {
        $this->ptoVtaWs = $ptoVtaWs;

        return $this;
    }

    /**
     * Get ptoVtaWs
     *
     * @return string
     */
    public function getPtoVtaWs()
    {
        return $this->ptoVtaWs;
    }

    /**
     * Set ptoVtaIfu
     *
     * @param string $ptoVtaIfu
     * @return Caja
     */
    public function setPtoVtaIfu($ptoVtaIfu)
    {
        $this->ptoVtaIfu = $ptoVtaIfu;

        return $this;
    }

    /**
     * Get ptoVtaIfu
     *
     * @return string
     */
    public function getPtoVtaIfu()
    {
        return $this->ptoVtaIfu;
    }
}
