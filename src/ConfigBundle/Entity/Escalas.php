<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * ConfigBundle\Entity\Escalas
 * @ORM\Table(name="escalas")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\EscalasRepository")
 * @UniqueEntity(
 *     fields={"tipo","nombre","retencion"},
 *     errorPath="nombre",
 *     message="Ya existe un registro con esta combinación tipo/nombre/retencion."
 * )
 */
class Escalas {
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
     * @var string $tipo
     * @ORM\Column(name="tipo", type="string", nullable=false)
     */
    protected $tipo;  
    /**
     * @var string $retencion
     * @ORM\Column(name="retencion", type="decimal", scale=2, nullable=true)
     */
    protected $retencion = 0;      
    /**
     * @var string $adicional
     * @ORM\Column(name="adicional", type="decimal", scale=2, nullable=true)
     */
    protected $adicional = 0;      
    /**
     * @var string $minimo
     * @ORM\Column(name="minimo", type="decimal", scale=2, nullable=true)
     */
    protected $minimo = 0;      
    /**
     * @var string $codigoAtp
     * @ORM\Column(name="codigo_atp", type="integer", nullable=true)
     */
    protected $codigoAtp = 0;            

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
     * @return Escalas
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
     * Set tipo
     *
     * @param string $tipo
     * @return Escalas
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set retencion
     *
     * @param string $retencion
     * @return Escalas
     */
    public function setRetencion($retencion)
    {
        $this->retencion = $retencion;

        return $this;
    }

    /**
     * Get retencion
     *
     * @return string 
     */
    public function getRetencion()
    {
        return $this->retencion;
    }

    /**
     * Set adicional
     *
     * @param string $adicional
     * @return Escalas
     */
    public function setAdicional($adicional)
    {
        $this->adicional = $adicional;

        return $this;
    }

    /**
     * Get adicional
     *
     * @return string 
     */
    public function getAdicional()
    {
        return $this->adicional;
    }

    /**
     * Set minimo
     *
     * @param string $minimo
     * @return Escalas
     */
    public function setMinimo($minimo)
    {
        $this->minimo = $minimo;

        return $this;
    }

    /**
     * Get minimo
     *
     * @return string 
     */
    public function getMinimo()
    {
        return $this->minimo;
    }

    /**
     * Set codigoAtp
     *
     * @param integer $codigoAtp
     * @return Escalas
     */
    public function setCodigoAtp($codigoAtp)
    {
        $this->codigoAtp = $codigoAtp;

        return $this;
    }

    /**
     * Get codigoAtp
     *
     * @return integer 
     */
    public function getCodigoAtp()
    {
        return $this->codigoAtp;
    }
}
