<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\RolUnidadNegocio
 * @ORM\Table(name="rol_unidadnegocio")
 * @ORM\Entity()
 */
class RolUnidadNegocio
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true; 

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario", inversedBy="rolesUnidadNegocio")
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    protected $usuario;    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio", inversedBy="rolesUnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Rol", inversedBy="rolesUnidadNegocio")
     * @ORM\JoinColumn(name="rol_id", referencedColumnName="id")
     */
    protected $rol;      
    
     /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Deposito", inversedBy="rolesUnidadNegocio")
     * @ORM\JoinTable(name="depositos_x_unidadnegocio",
     *      joinColumns={@ORM\JoinColumn(name="rol_unidadnegocio_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="deposito_id", referencedColumnName="id")}
     * )
     */
    private $depositos;

     /**
     * @ORM\ManyToMany(targetEntity="ConfigBundle\Entity\PuntoVenta", inversedBy="rolesUnidadNegocio")
     * @ORM\JoinTable(name="puntosventa_x_unidadnegocio",
     *      joinColumns={@ORM\JoinColumn(name="rol_unidadnegocio_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="punto_venta_id", referencedColumnName="id")}
     * )
     */
    private $puntosVenta;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->depositos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->puntosVenta = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set activo
     *
     * @param boolean $activo
     * @return RolUnidadNegocio
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
     * Set usuario
     *
     * @param \ConfigBundle\Entity\Usuario $usuario
     * @return RolUnidadNegocio
     */
    public function setUsuario(\ConfigBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return RolUnidadNegocio
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
     * Set rol
     *
     * @param \ConfigBundle\Entity\Rol $rol
     * @return RolUnidadNegocio
     */
    public function setRol(\ConfigBundle\Entity\Rol $rol = null)
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return \ConfigBundle\Entity\Rol 
     */
    public function getRol()
    {
        return $this->rol;
    }
    
    public function getAccess($slug){
        if($this->rol->getAdmin()) return TRUE;
        foreach ($this->rol->getPermisos() as $permiso) {

            if(  !(strpos($permiso->getRoute(), $slug)  === false) ){
                return TRUE;                
            }
        }
        return FALSE; 
    }    

    /**
     * Add depositos
     *
     * @param \AppBundle\Entity\Deposito $depositos
     * @return RolUnidadNegocio
     */
    public function addDeposito(\AppBundle\Entity\Deposito $depositos)
    {
        $this->depositos[] = $depositos;
        return $this;
    }

    /**
     * Remove depositos
     *
     * @param \AppBundle\Entity\Deposito $depositos
     */
    public function removeDeposito(\AppBundle\Entity\Deposito $depositos)
    {
        $this->depositos->removeElement($depositos);
    }

    /**
     * Get depositos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepositos()
    {
        $deps = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->depositos as $value) {
            if($value->getActivo())
                $deps->add($value);
        }        
        return $deps;
    }

    /**
     * Add puntosVenta
     *
     * @param \ConfigBundle\Entity\PuntoVenta $puntosVenta
     * @return RolUnidadNegocio
     */
    public function addPuntosVentum(\ConfigBundle\Entity\PuntoVenta $puntosVenta)
    {
        $this->puntosVenta[] = $puntosVenta;

        return $this;
    }

    /**
     * Remove puntosVenta
     *
     * @param \ConfigBundle\Entity\PuntoVenta $puntosVenta
     */
    public function removePuntosVentum(\ConfigBundle\Entity\PuntoVenta $puntosVenta)
    {
        $this->puntosVenta->removeElement($puntosVenta);
    }

    /**
     * Get puntosVenta
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPuntosVenta()
    {
        $ptos = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->puntosVenta as $value) {
            if($value->getActivo())
                $ptos->add($value);
        }        
        return $ptos;
    }
}
