<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * AppBundle\Entity\Deposito
 *
 * @ORM\Table(name="deposito")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\DepositoRepository")
 */
class Deposito
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
     *@ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     *@ORM\JoinColumn(name="responsable_id", referencedColumnName="id") 
     */
    protected $responsable;    
    /**
     * @ORM\Column(name="central", type="boolean")
     */
    protected $central = false;    
    /**
     * @ORM\Column(name="pordefecto", type="boolean")
     */
    protected $pordefecto = false;    
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
     * @ORM\ManyToMany(targetEntity="ConfigBundle\Entity\RolUnidadNegocio", mappedBy="depositos")
     */
    protected $rolesUnidadNegocio;    
        
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Pedido", mappedBy="depositoOrigen")
     */
    protected $pedidosSolicitados;    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Pedido", mappedBy="depositoDestino")
     */
    protected $pedidosDemandados;    
    
    
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
    * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
    * @ORM\JoinColumn(name="localidad_id", referencedColumnName="id")
    * @Assert\NotNull()
    */
    protected $localidad;

    public function __toString() {
        return $this->nombre;
    } 
    
    public function getEmpresaUnidadDeposito(){
        return $this->unidadNegocio->getEmpresa()->getNombre().' - '. $this->unidadNegocio->getNombre().' - '. $this->getNombre();
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
     * @return Deposito
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
     * Set direccion
     *
     * @param string $direccion
     * @return Deposito
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
     * @return Deposito
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
     * Set activo
     *
     * @param boolean $activo
     * @return Deposito
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
     * Set responsable
     *
     * @param \Config\Entity\Usuario $responsable
     * @return Deposito
     */
    public function setResponsable(\ConfigBundle\Entity\Usuario $responsable = null)
    {
        $this->responsable = $responsable;    
        return $this;
    }

    /**
     * Get responsable
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getResponsable()
    {
        return $this->responsable;
    }

    /**
     * Set localidad
     *
     * @param \ConfigBundle\Entity\Localidad $localidad
     * @return Deposito
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
     * Set created
     *
     * @param \DateTime $created
     * @return Deposito
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
     * @return Deposito
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
     * Set central
     *
     * @param boolean $central
     * @return Deposito
     */
    public function setCentral($central)
    {
        $this->central = $central;
    
        return $this;
    }

    /**
     * Get central
     *
     * @return boolean 
     */
    public function getCentral()
    {
        return $this->central;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Deposito
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
     * @return Deposito
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



    /**
     * Get pedidosSolicitados
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPedidosSolicitados()
    {
        return $this->pedidosSolicitados;
    }
    /**
     * Get pedidosDemandados
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPedidosDemandados()
    {
        return $this->pedidosDemandados;
    }

    /**
     * Set pordefecto
     *
     * @param boolean $pordefecto
     * @return Deposito
     */
    public function setPordefecto($pordefecto)
    {
        $this->pordefecto = $pordefecto;

        return $this;
    }

    /**
     * Get pordefecto
     *
     * @return boolean 
     */
    public function getPordefecto()
    {
        return $this->pordefecto;
    }

    /**
     * Add pedidosSolicitados
     *
     * @param \AppBundle\Entity\Pedido $pedidosSolicitados
     * @return Deposito
     */
    public function addPedidosSolicitado(\AppBundle\Entity\Pedido $pedidosSolicitados)
    {
        $this->pedidosSolicitados[] = $pedidosSolicitados;

        return $this;
    }

    /**
     * Remove pedidosSolicitados
     *
     * @param \AppBundle\Entity\Pedido $pedidosSolicitados
     */
    public function removePedidosSolicitado(\AppBundle\Entity\Pedido $pedidosSolicitados)
    {
        $this->pedidosSolicitados->removeElement($pedidosSolicitados);
    }

    /**
     * Add pedidosDemandados
     *
     * @param \AppBundle\Entity\Pedido $pedidosDemandados
     * @return Deposito
     */
    public function addPedidosDemandado(\AppBundle\Entity\Pedido $pedidosDemandados)
    {
        $this->pedidosDemandados[] = $pedidosDemandados;

        return $this;
    }

    /**
     * Remove pedidosDemandados
     *
     * @param \AppBundle\Entity\Pedido $pedidosDemandados
     */
    public function removePedidosDemandado(\AppBundle\Entity\Pedido $pedidosDemandados)
    {
        $this->pedidosDemandados->removeElement($pedidosDemandados);
    }
}
