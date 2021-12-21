<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\TitularCheque
 * @ORM\Table(name="titular_cheque")
 * @ORM\Entity()
 */
class TitularCheque
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     */    
    protected $nombre;
    /**
     * @var string $cuit
     * @ORM\Column(name="cuit", type="string", length=13, nullable=true)
     */
    protected $cuit;
    
    /**
     * @var string $direccion
     * @ORM\Column(name="direccion", type="string", nullable=true)
     */
    protected $direccion;
    /**
     * @var string $telefono
     * @ORM\Column(name="telefono", type="string", nullable=true)
     */
    protected $telefono;
    /**
     * @var string $celular
     * @ORM\Column(name="celular", type="string", nullable=true)
     */
    protected $celular;
    /**
     * @var string $email
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;
   
    /**
    * @var string $observaciones
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;      
    
    /**
     * @var integer $devueltos
     * @ORM\Column(name="devueltos", type="boolean")
     */
    protected $devueltos;  
    /**
     * @var integer $activo
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo;  
    
    /**
     *@ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Banco")
     *@ORM\JoinColumn(name="banco_id", referencedColumnName="id") 
     */
    protected $banco;      
    /**
    * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
    * @ORM\JoinColumn(name="localidad_id", referencedColumnName="id")
    */
    protected $localidad;
    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\Cheque", mappedBy="titularCheque")
     */
    protected $cheques;    
 
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
        $this->cheques = new \Doctrine\Common\Collections\ArrayCollection();
    }
    public function __toString()
    {
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
     * @return TitularCheque
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
     * Set cuit
     *
     * @param string $cuit
     * @return TitularCheque
     */
    public function setCuit($cuit)
    {
        $this->cuit = $cuit;
    
        return $this;
    }

    /**
     * Get cuit
     *
     * @return string 
     */
    public function getCuit()
    {
        return $this->cuit;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return TitularCheque
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return TitularCheque
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    
        return $this;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return TitularCheque
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;
    
        return $this;
    }

    /**
     * Get celular
     *
     * @return string 
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return TitularCheque
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return TitularCheque
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    
        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return TitularCheque
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
     * @return TitularCheque
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
     * @return TitularCheque
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
     * Set banco
     *
     * @param \ConfigBundle\Entity\Banco $banco
     * @return TitularCheque
     */
    public function setBanco(\ConfigBundle\Entity\Banco $banco = null)
    {
        $this->banco = $banco;
    
        return $this;
    }

    /**
     * Get banco
     *
     * @return \ConfigBundle\Entity\Banco 
     */
    public function getBanco()
    {
        return $this->banco;
    }

    /**
     * Set localidad
     *
     * @param \ConfigBundle\Entity\Localidad $localidad
     * @return TitularCheque
     */
    public function setLocalidad(\ConfigBundle\Entity\Localidad $localidad = null)
    {
        $this->localidad = $localidad;
    
        return $this;
    }

    /**
     * Get localidad
     *
     * @return \ConfigBundle\Entity\Localidad 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Add cheques
     *
     * @param \ConfigBundle\Entity\Cheque $cheques
     * @return TitularCheque
     */
    public function addCheque(\ConfigBundle\Entity\Cheque $cheques)
    {
        $this->cheques[] = $cheques;
    
        return $this;
    }

    /**
     * Remove cheques
     *
     * @param \ConfigBundle\Entity\Cheque $cheques
     */
    public function removeCheque(\ConfigBundle\Entity\Cheque $cheques)
    {
        $this->cheques->removeElement($cheques);
    }

    /**
     * Get cheques
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCheques()
    {
        return $this->cheques;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return TitularCheque
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
     * @return TitularCheque
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
     * Set devueltos
     *
     * @param boolean $devueltos
     * @return TitularCheque
     */
    public function setDevueltos($devueltos)
    {
        $this->devueltos = $devueltos;
    
        return $this;
    }

    /**
     * Get devueltos
     *
     * @return boolean 
     */
    public function getDevueltos()
    {
        $dev = false;
        foreach ($this->getCheques() as $cheque){
            if( $cheque->getDevuelto() ) 
                $dev = true;
        }
        return ( count($this->getCheques())>0 ) ? $dev : $this->devueltos ;
    }
}