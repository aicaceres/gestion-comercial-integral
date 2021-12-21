<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * AppBundle\Entity\PrecioLista
 * @ORM\Table(name="precio_lista")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\PrecioListaRepository")
 */
class PrecioLista
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
     * @var date $vigenciaDesde
     * @ORM\Column(name="vigencia_desde", type="date")
     */    
    protected $vigenciaDesde;
    
    /**
     * @var date $vigenciaHasta
     * @ORM\Column(name="vigencia_hasta", type="date", nullable=true)
     */    
    protected $vigenciaHasta;
    
    /**
     * @ORM\Column(name="principal", type="boolean")
     */
    protected $principal = false;   
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Precio", mappedBy="precioLista",cascade={"persist", "remove"})
     */
    protected $precios;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PrecioActualizacion", mappedBy="precioLista")
     */
    protected $actualizaciones;
    
    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true;   
    
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
    public function __construct()
    {
        $this->precios = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actualizaciones = new \Doctrine\Common\Collections\ArrayCollection();
    }
    public function __toString() {
        //$result = $this->vigenciaDesde->format('d/m/Y');
        return $this->nombre. '- '.$this->descripcion;
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
     * @return PrecioLista
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
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
     * @return PrecioLista
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
     * Set principal
     *
     * @param boolean $principal
     * @return PrecioLista
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;
    
        return $this;
    }

    /**
     * Get principal
     *
     * @return boolean 
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return PrecioLista
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
     * Set created
     *
     * @param \DateTime $created
     * @return PrecioLista
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
     * @return PrecioLista
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
     * Add precios
     *
     * @param \AppBundle\Entity\Precio $precios
     * @return PrecioLista
     */
    public function addPrecio(\AppBundle\Entity\Precio $precios)
    {
        $precios->setPrecioLista($this);
        $this->precios[] = $precios;
        return $this;
    }

    /**
     * Remove precios
     *
     * @param \AppBundle\Entity\Precio $precios
     */
    public function removePrecio(\AppBundle\Entity\Precio $precios)
    {
        $this->precios->removeElement($precios);
    }

    /**
     * Get precios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPrecios()
    {
        return $this->precios;
    }

    /**
     * Add actualizaciones
     *
     * @param \AppBundle\Entity\PrecioActualizacion $actualizaciones
     * @return PrecioLista
     */
    public function addActualizaciones(\AppBundle\Entity\PrecioActualizacion $actualizaciones)
    {
        $this->actualizaciones[] = $actualizaciones;    
        return $this;
    }

    /**
     * Remove actualizaciones
     *
     * @param \AppBundle\Entity\PrecioActualizacion $actualizaciones
     */
    public function removeActualizacione(\AppBundle\Entity\PrecioActualizacion $actualizaciones)
    {
        $this->actualizaciones->removeElement($actualizaciones);
    }

    /**
     * Get actualizaciones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActualizaciones()
    {
        return $this->actualizaciones;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return PrecioLista
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
     * @return PrecioLista
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
     * Set vigenciaDesde
     *
     * @param \DateTime $vigenciaDesde
     * @return PrecioLista
     */
    public function setVigenciaDesde($vigenciaDesde)
    {
        $this->vigenciaDesde = $vigenciaDesde;    
        return $this;
    }

    /**
     * Get vigenciaDesde
     *
     * @return \DateTime 
     */
    public function getVigenciaDesde()
    {
        return $this->vigenciaDesde;
    }

    /**
     * Set vigenciaHasta
     *
     * @param \DateTime $vigenciaHasta
     * @return PrecioLista
     */
    public function setVigenciaHasta($vigenciaHasta)
    {
        $this->vigenciaHasta = $vigenciaHasta;    
        return $this;
    }

    /**
     * Get vigenciaHasta
     *
     * @return \DateTime 
     */
    public function getVigenciaHasta()
    {
        return $this->vigenciaHasta;
    }

    /**
     * Add actualizaciones
     *
     * @param \AppBundle\Entity\PrecioActualizacion $actualizaciones
     * @return PrecioLista
     */
    public function addActualizacione(\AppBundle\Entity\PrecioActualizacion $actualizaciones)
    {
        $this->actualizaciones[] = $actualizaciones;    
        return $this;
    }
}