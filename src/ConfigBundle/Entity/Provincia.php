<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\Provincia
 * @ORM\Table(name="provincia")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\ProvinciaRepository")
 */
class Provincia {
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
     * @ORM\Column(name="by_default", type="boolean", nullable=true)
     */
    protected $byDefault = false;

    /**
     * @var string $codSicore
     * @ORM\Column(name="cod_sicore", type="string", length=2, nullable=true)
     */
    protected $codSicore;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Pais", inversedBy="provincias")
     * @ORM\JoinColumn(name="pais_id", referencedColumnName="id")
     */
    protected $pais;

    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\Localidad", mappedBy="provincia")
     */
    protected $localidades;

    /**
     * Constructor
     */
    public function __construct() {
        $this->localidades = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Provincia
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set shortname
     *
     * @param string $shortname
     * @return Localidad
     */
    public function setShortName($shortname) {
        $this->shortname = $shortname;

        return $this;
    }

    /**
     * Get shortname
     *
     * @return string
     */
    public function getShortName() {
        return $this->shortname;
    }

    /**
     * Set pais
     *
     * @param \ConfigBundle\Entity\Pais $pais
     * @return Provincia
     */
    public function setPais(\ConfigBundle\Entity\Pais $pais = null) {
        $this->pais = $pais;

        return $this;
    }

    /**
     * Get pais
     *
     * @return \ConfigBundle\Entity\Pais
     */
    public function getPais() {
        return $this->pais;
    }

    /**
     * Add localidades
     *
     * @param \ConfigBundle\Entity\Localidad $localidades
     * @return Provincia
     */
    public function addLocalidade(\ConfigBundle\Entity\Localidad $localidades) {
        $this->localidades[] = $localidades;

        return $this;
    }

    /**
     * Remove localidades
     *
     * @param \ConfigBundle\Entity\Localidad $localidades
     */
    public function removeLocalidade(\ConfigBundle\Entity\Localidad $localidades) {
        $this->localidades->removeElement($localidades);
    }

    /**
     * Get localidades
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocalidades() {
        return $this->localidades;
    }

    /**
     * Set byDefault
     *
     * @param boolean $byDefault
     * @return Provincia
     */
    public function setByDefault($byDefault) {
        $this->byDefault = $byDefault;

        return $this;
    }

    /**
     * Get byDefault
     *
     * @return boolean
     */
    public function getByDefault() {
        return $this->byDefault;
    }


    /**
     * Set codSicore
     *
     * @param string $codSicore
     * @return Provincia
     */
    public function setCodSicore($codSicore)
    {
        $this->codSicore = $codSicore;

        return $this;
    }

    /**
     * Get codSicore
     *
     * @return string 
     */
    public function getCodSicore()
    {
        return $this->codSicore;
    }
}
