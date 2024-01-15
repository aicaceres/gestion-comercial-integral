<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * ConfigBundle\Entity\ActividadComercial
 * @ORM\Table(name="actividad_comercial")
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"codigo"},
 *     errorPath="codigo",
 *     message="Registro duplicado."
 * )
 */
class ActividadComercial {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false, unique=true)
     */
    protected $nombre;
    /**
     * @var string $codigo
     * @ORM\Column(name="codigo", type="string", nullable=false, unique=true)
     */
    protected $codigo;
    /**
     * @var string $exento
     * @ORM\Column(name="exento", type="decimal", precision=20, scale=2,nullable=true)
     */
    protected $exento = 0;
    /**
     * @var string $inscripto
     * @ORM\Column(name="inscripto", type="decimal", precision=20, scale=2, nullable=true)
     */
    protected $inscripto = 0;
    /**
     * @var string $noInscripto
     * @ORM\Column(name="no_inscripto", type="decimal", precision=20, scale=2, nullable=true)
     */
    protected $noInscripto = 0;
    /**
     * @var string $minimo
     * @ORM\Column(name="minimo", type="decimal", precision=20, scale=2, nullable=true)
     */
    protected $minimo = 0;

    /**
     * @ORM\Column(name="by_default", type="boolean", nullable=true)
     */
    protected $byDefault = false;

    /**
     * @var string $codigoImpuesto
     * @ORM\Column(name="codigo_impuesto", type="string", nullable=true)
     */
    protected $codigoImpuesto;
    /**
     * @var string $codigoRegimen
     * @ORM\Column(name="codigo_regimen", type="string", nullable=true)
     */
    protected $codigoRegimen;
    /**
     * @var string $codigoRG
     * @ORM\Column(name="codigo_rg", type="string", nullable=true)
     */
    protected $codigoRG;

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
     * @return ActividadComercial
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
     * Set exento
     *
     * @param string $exento
     * @return ActividadComercial
     */
    public function setExento($exento)
    {
        $this->exento = $exento;

        return $this;
    }

    /**
     * Get exento
     *
     * @return string
     */
    public function getExento()
    {
        return $this->exento;
    }

    /**
     * Set inscripto
     *
     * @param string $inscripto
     * @return ActividadComercial
     */
    public function setInscripto($inscripto)
    {
        $this->inscripto = $inscripto;

        return $this;
    }

    /**
     * Get inscripto
     *
     * @return string
     */
    public function getInscripto()
    {
        return $this->inscripto;
    }

    /**
     * Set noInscripto
     *
     * @param string $noInscripto
     * @return ActividadComercial
     */
    public function setNoInscripto($noInscripto)
    {
        $this->noInscripto = $noInscripto;

        return $this;
    }

    /**
     * Get noInscripto
     *
     * @return string
     */
    public function getNoInscripto()
    {
        return $this->noInscripto;
    }

    /**
     * Set minimo
     *
     * @param string $minimo
     * @return ActividadComercial
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
     * Set byDefault
     *
     * @param boolean $byDefault
     * @return ActividadComercial
     */
    public function setByDefault($byDefault)
    {
        $this->byDefault = $byDefault;

        return $this;
    }

    /**
     * Get byDefault
     *
     * @return boolean
     */
    public function getByDefault()
    {
        return $this->byDefault;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return ActividadComercial
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

    /**
     * Set codigoImpuesto
     *
     * @param string $codigoImpuesto
     * @return ActividadComercial
     */
    public function setCodigoImpuesto($codigoImpuesto)
    {
        $this->codigoImpuesto = $codigoImpuesto;

        return $this;
    }

    /**
     * Get codigoImpuesto
     *
     * @return string
     */
    public function getCodigoImpuesto()
    {
        return $this->codigoImpuesto;
    }

    /**
     * Set codigoRegimen
     *
     * @param string $codigoRegimen
     * @return ActividadComercial
     */
    public function setCodigoRegimen($codigoRegimen)
    {
        $this->codigoRegimen = $codigoRegimen;

        return $this;
    }

    /**
     * Get codigoRegimen
     *
     * @return string
     */
    public function getCodigoRegimen()
    {
        return $this->codigoRegimen;
    }

    /**
     * Set codigoRG
     *
     * @param string $codigoRG
     * @return ActividadComercial
     */
    public function setCodigoRG($codigoRG)
    {
        $this->codigoRG = $codigoRG;

        return $this;
    }

    /**
     * Get codigoRG
     *
     * @return string
     */
    public function getCodigoRG()
    {
        return $this->codigoRG;
    }

}
