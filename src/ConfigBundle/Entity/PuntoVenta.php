<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ConfigBundle\Entity\PuntoVenta
 *
 * @ORM\Table(name="punto_venta")
 * @ORM\Entity()
 */
class PuntoVenta
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
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true;    
    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;      
    
    /**
     * @ORM\ManyToMany(targetEntity="ConfigBundle\Entity\RolUnidadNegocio", mappedBy="puntosVenta")
     */
    protected $rolesUnidadNegocio;      

    public function __toString() {
        return $this->nombre;
    } 

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rolesUnidadNegocio = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return PuntoVenta
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
     * @return PuntoVenta
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
     * @return PuntoVenta
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
     * @return PuntoVenta
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
     * Add rolesUnidadNegocio
     *
     * @param \ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio
     * @return PuntoVenta
     */
    public function addRolesUnidadNegocio(\ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio)
    {
        $this->rolesUnidadNegocio[] = $rolesUnidadNegocio;

        return $this;
    }

    /**
     * Remove rolesUnidadNegocio
     *
     * @param \ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio
     */
    public function removeRolesUnidadNegocio(\ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio)
    {
        $this->rolesUnidadNegocio->removeElement($rolesUnidadNegocio);
    }

    /**
     * Get rolesUnidadNegocio
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRolesUnidadNegocio()
    {
        return $this->rolesUnidadNegocio;
    }
}
