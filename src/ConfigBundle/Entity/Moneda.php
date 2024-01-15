<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * ConfigBundle\Entity\Moneda
 * @ORM\Table(name="moneda")
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"nombre"},
 *     errorPath="nombre",
 *     message="Ya existe un registro con este nombre."
 * )
 */
class Moneda {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false,unique=true)
     */
    protected $nombre;
    /**
     * @var string $simbolo
     * @ORM\Column(name="simbolo", type="string", nullable=true)
     */
    protected $simbolo = '$';
    /**
     * @var string $cotizacion
     * @ORM\Column(name="cotizacion", type="decimal", precision=20, scale=2, nullable=true)
     */
    protected $cotizacion = 0;
    /**
     * @var string $tope
     * @ORM\Column(name="tope", type="decimal", precision=20, scale=2, nullable=true)
     */
    protected $tope = 0;
    /**
     * @var string $codigoAfip
     * @ORM\Column(name="codigo_afip", type="string", nullable=true, unique=true)
     */
    protected $codigoAfip;

    /**
     * @ORM\Column(name="by_default", type="boolean", nullable=true)
     */
    protected $byDefault = false;

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
     * @return Moneda
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
     * Set simbolo
     *
     * @param string $simbolo
     * @return Moneda
     */
    public function setSimbolo($simbolo)
    {
        $this->simbolo = $simbolo;

        return $this;
    }

    /**
     * Get simbolo
     *
     * @return string
     */
    public function getSimbolo()
    {
        return $this->simbolo;
    }

    /**
     * Set cotizacion
     *
     * @param string $cotizacion
     * @return Moneda
     */
    public function setCotizacion($cotizacion)
    {
        $this->cotizacion = $cotizacion;

        return $this;
    }

    /**
     * Get cotizacion
     *
     * @return string
     */
    public function getCotizacion()
    {
        return $this->cotizacion;
    }

    /**
     * Set tope
     *
     * @param string $tope
     * @return Moneda
     */
    public function setTope($tope)
    {
        $this->tope = $tope;

        return $this;
    }

    /**
     * Get tope
     *
     * @return string
     */
    public function getTope()
    {
        return $this->tope;
    }

    /**
     * Set byDefault
     *
     * @param boolean $byDefault
     * @return Moneda
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
     * Set codigoAfip
     *
     * @param string $codigoAfip
     * @return Moneda
     */
    public function setCodigoAfip($codigoAfip)
    {
        $this->codigoAfip = $codigoAfip;

        return $this;
    }

    /**
     * Get codigoAfip
     *
     * @return string
     */
    public function getCodigoAfip()
    {
        return $this->codigoAfip;
    }
}
