<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\Permiso
 * @ORM\Table(name="permiso")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\PermisoRepository")
 */
class Permiso
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $route
     * @ORM\Column(name="route", type="string", length=255)
     */
    protected $route;
    
    /**
     * @var string $text
     * @ORM\Column(name="text", type="string", length=255)
     */
    protected $text;   
    
    /**
     * @var string $orden
     * @ORM\Column(name="orden", type="integer")
     */
    protected $orden;    
    
    /**
     * @ORM\OneToMany(targetEntity="Permiso", mappedBy="padre")
     */
    private $hijos;

    /**
     * @ORM\ManyToOne(targetEntity="Permiso", inversedBy="hijos")
     * @ORM\JoinColumn(name="padre_id", referencedColumnName="id")
     */
    private $padre;    
    
    public function __toString() {
        return $this->text;
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
     * Set route
     *
     * @param string $route
     * @return Permiso
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string 
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Permiso
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hijos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add hijos
     *
     * @param \ConfigBundle\Entity\Permiso $hijos
     * @return Permiso
     */
    public function addHijo(\ConfigBundle\Entity\Permiso $hijos)
    {
        $this->hijos[] = $hijos;

        return $this;
    }

    /**
     * Remove hijos
     *
     * @param \ConfigBundle\Entity\Permiso $hijos
     */
    public function removeHijo(\ConfigBundle\Entity\Permiso $hijos)
    {
        $this->hijos->removeElement($hijos);
    }

    /**
     * Get hijos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHijos()
    {
        return $this->hijos;
    }

    /**
     * Set padre
     *
     * @param \ConfigBundle\Entity\Permiso $padre
     * @return Permiso
     */
    public function setPadre(\ConfigBundle\Entity\Permiso $padre = null)
    {
        $this->padre = $padre;

        return $this;
    }

    /**
     * Get padre
     *
     * @return \ConfigBundle\Entity\Permiso 
     */
    public function getPadre()
    {
        return $this->padre;
    }
    
    public function esPadre(){
        return is_null($this->padre);
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return Permiso
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }
}
