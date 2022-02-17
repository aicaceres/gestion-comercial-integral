<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ConfigBundle\Entity\Caja
 *
 * @ORM\Table(name="caja")
 * @ORM\Entity()
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
}
