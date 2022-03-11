<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\Localidad
 * @ORM\Table(name="localidad")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\LocalidadRepository")
 */
class Localidad
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $shortname
     * @ORM\Column(name="shortname", type="string", length=255, nullable=true)
     */
    protected $shortname;

    /**
     * @var string $codpostal
     * @ORM\Column(name="codpostal", type="string", nullable=true)
     */
    protected $codpostal;

    /**
     * @ORM\Column(name="by_default", type="boolean", nullable=true)
     */
    protected $byDefault = false;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Provincia", inversedBy="localidades")
     * @ORM\JoinColumn(name="provincia_id", referencedColumnName="id")
     */
    protected $provincia;

    public function __toString() {
        return ($this->name) ? $this->name : '';
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
     * Set name
     *
     * @param string $name
     * @return Localidad
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set shortname
     *
     * @param string $shortname
     * @return Localidad
     */
    public function setShortName($shortname)
    {
        $this->shortname = $shortname;

        return $this;
    }

    /**
     * Get shortname
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortname;
    }

    /**
     * Set provincia
     *
     * @param \ConfigBundle\Entity\Provincia $provincia
     * @return Localidad
     */
    public function setProvincia(\ConfigBundle\Entity\Provincia $provincia = null)
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * Get provincia
     *
     * @return \ConfigBundle\Entity\Provincia
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * Set codpostal
     *
     * @param string $codpostal
     * @return Localidad
     */
    public function setCodpostal($codpostal)
    {
        $this->codpostal = $codpostal;

        return $this;
    }

    /**
     * Get codpostal
     *
     * @return string
     */
    public function getCodpostal()
    {
        return $this->codpostal;
    }

    /**
     * Set byDefault
     *
     * @param boolean $byDefault
     * @return Localidad
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
     * Return localidad - provincia
     */
    public function getNombreProvincia(){
        return $this->getName().' ('.$this->getProvincia()->getName().')';
    }
    /**
     * Return Pais - Provincia - Localidad
     */
    public function getCompleto(){
        return $this->getProvincia()->getPais()->getName() .' | '. $this->getProvincia()->getName(). ' | ' .$this->getName();
    }

}
