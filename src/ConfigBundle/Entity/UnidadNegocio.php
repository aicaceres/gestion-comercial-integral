<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ConfigBundle\Entity\UnidadNegocio
 *
 * @ORM\Table(name="unidad_negocio")
 * @ORM\Entity()
 */
class UnidadNegocio               
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
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;    
    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Empresa", inversedBy="unidades")
     * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     */
    protected $empresa;    
    
    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\RolUnidadNegocio", mappedBy="unidadNegocio")
     */
    protected $rolesUnidadNegocio;       

    public function __toString() {
        return $this->nombre;
    }    
    
    public function empresaUnidad(){
        return $this->getEmpresa()->getNombreCorto().' - '.$this->getNombre();
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
     * @return UnidadNegocio
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
     * Set activo
     *
     * @param boolean $activo
     * @return UnidadNegocio
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
     * @return UnidadNegocio
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return UnidadNegocio
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
     * Set empresa
     *
     * @param \ConfigBundle\Entity\Empresa $empresa
     * @return UnidadNegocio
     */
    public function setEmpresa(\ConfigBundle\Entity\Empresa $empresa = null)
    {
        $this->empresa = $empresa;

        return $this;
    }

    /**
     * Get empresa
     *
     * @return \ConfigBundle\Entity\Empresa 
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rolesUnidadNegocio = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add rolesUnidadNegocio
     *
     * @param \ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio
     * @return UnidadNegocio
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
