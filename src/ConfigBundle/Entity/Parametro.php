<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\Parametro
 * @ORM\Table(name="parametro")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\ParametroRepository")
 */
class Parametro
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
 
    /**
     * @ORM\Column(name="agrupador_id", type="integer", nullable=true)
     */
    protected $agrupador_id;
    
    /**
     * @ORM\Column(name="padre_id", type="integer", nullable=true)
     */
    protected $padre_id;
    
    /**
     * @ORM\Column(name="nombre", type="string", length=200)
     */
    protected $nombre;
    
    /**
     * @ORM\Column(name="descripcion", type="string", length=200, nullable=true)
     */
    protected $descripcion;

    /**
     * @ORM\Column(name="numerico", type="decimal",scale=2, nullable=true)
     */
    protected $numerico;
    /**
     * @ORM\Column(name="numerico2", type="decimal",scale=2, nullable=true)
     */
    protected $numerico2;

    /**
     * @ORM\Column(name="codigo", type="string", nullable=true)
     */
    protected $codigo;
    /**
     * @ORM\Column(name="boleano", type="boolean")
     */
    protected $boleano;

    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo;

    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\Parametro", mappedBy="padre")
     */
    protected $hijos;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro", inversedBy="hijos")
     * @ORM\JoinColumn(name="padre_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $padre;
    
    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\Parametro", mappedBy="agrupador")
     */
    protected $agrupados;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro", inversedBy="agrupados")
     * @ORM\JoinColumn(name="agrupador_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $agrupador;
    
    public function __toString() {
        return $this->nombre;
    }
    public function getRubroSubrubroTxt() {
        return ($this->agrupador)? strtoupper($this->agrupador->getNombre()).' - '.$this->nombre  :'-';
    }
    public function __construct()
    {
        $this->activo = true;
        $this->boleano = false;
    }
    public function getTitle(){
        return '" '.$this->descripcion.' "';
    }
    public function getText() {
        return $this->nombre.' - '.$this->descripcion;
    }
    /**
     * Get id
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set agrupador_id
     * @param integer $agrupadorId
     */
    public function setAgrupadorId($agrupadorId)
    {
        $this->agrupador_id = $agrupadorId;
    }

    /**
     * Get agrupador_id
     * @return integer 
     */
    public function getAgrupadorId()
    {
        return $this->agrupador_id;
    }

    /**
     * Set padre_id
     * @param integer $padreId
     */
    public function setPadreId($padreId)
    {
        $this->padre_id = $padreId;
    }

    /**
     * Get padre_id
     * @return integer 
     */
    public function getPadreId()
    {
        return $this->padre_id;
    }

    /**
     * Set nombre
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
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
     * @param string $descripcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    /**
     * Get descripcion
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set numerico
     * @param decimal $numerico
     */
    public function setNumerico($numerico)
    {
        $this->numerico = $numerico;
    }

    /**
     * Get numerico
     * @return decimal 
     */
    public function getNumerico()
    {
        return $this->numerico;
    }

    /**
     * Set boleano
     * @param boolean $boleano
     */
    public function setBoleano($boleano)
    {
        $this->boleano = $boleano;
    }

    /**
     * Get boleano
     * @return boolean 
     */
    public function getBoleano()
    {
        return $this->boleano;
    }

    /**
     * Set activo
     * @param boolean $activo
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    }

    /**
     * Get activo
     * @return boolean 
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Add hijos
     * @param ConfigBundle\Entity\Parametro $hijos
     */
    public function addParametro(\ConfigBundle\Entity\Parametro $hijos)
    {
        $this->hijos[] = $hijos;
    }

    /**
     * Get hijos
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getHijos()
    {
        return $this->hijos;
    }

    /**
     * Set padre
     * @param ConfigBundle\Entity\Parametro $padre
     */
    public function setPadre(\ConfigBundle\Entity\Parametro $padre)
    {
        $this->padre = $padre;
    }

    /**
     * Get padre
     * @return ConfigBundle\Entity\Parametro 
     */
    public function getPadre()
    {
        return $this->padre;
    }

    /**
     * Get agrupados
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAgrupados()
    {
        return $this->agrupados;
    }

    /**
     * Set agrupador
     * @param ConfigBundle\Entity\Parametro $agrupador
     */
    public function setAgrupador(\ConfigBundle\Entity\Parametro $agrupador)
    {
        $this->agrupador = $agrupador;
    }

    /**
     * Get agrupador
     * @return ConfigBundle\Entity\Parametro 
     */
    public function getAgrupador()
    {
        return $this->agrupador;
    }
    

    /**
     * Set numerico2
     * @param string $numerico2
     * @return Parametro
     */
    public function setNumerico2($numerico2)
    {
        $this->numerico2 = $numerico2;
    
        return $this;
    }

    /**
     * Get numerico2
     * @return string 
     */
    public function getNumerico2()
    {
        return $this->numerico2;
    }

    /**
     * Add hijos
     * @param \ConfigBundle\Entity\Parametro $hijos
     * @return Parametro
     */
    public function addHijo(\ConfigBundle\Entity\Parametro $hijos)
    {
        $this->hijos[] = $hijos;
    
        return $this;
    }

    /**
     * Remove hijos
     * @param \ConfigBundle\Entity\Parametro $hijos
     */
    public function removeHijo(\ConfigBundle\Entity\Parametro $hijos)
    {
        $this->hijos->removeElement($hijos);
    }

    /**
     * Add agrupados
     * @param \ConfigBundle\Entity\Parametro $agrupados
     * @return Parametro
     */
    public function addAgrupado(\ConfigBundle\Entity\Parametro $agrupados)
    {
        $this->agrupados[] = $agrupados;
    
        return $this;
    }

    /**
     * Remove agrupados
     * @param \ConfigBundle\Entity\Parametro $agrupados
     */
    public function removeAgrupado(\ConfigBundle\Entity\Parametro $agrupados)
    {
        $this->agrupados->removeElement($agrupados);
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return Parametro
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }
}
