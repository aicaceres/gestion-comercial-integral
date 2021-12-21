<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\AfipSecuenciaInformada
 * @ORM\Table(name="afip_secuencia_informada")
 * @ORM\Entity()
 */
class AfipSecuenciaInformada {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $periodo
     * @ORM\Column(name="periodo", type="string", length=6, nullable=false)
     */
    protected $periodo;

    /**
     * @var string $secuencia
     * @ORM\Column(name="secuencia", type="string", length=2, nullable=false)
     */
    protected $secuencia;

    /**
     * @var string $enviado
     * @ORM\Column(name="enviado", type="boolean", nullable=true)
     */
    protected $activo = true;

    public function __toString() {
        return $this->periodo;
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
     * Set periodo
     *
     * @param string $periodo
     * @return AfipSecuenciaInformada
     */
    public function setPeriodo($periodo) {
        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get periodo
     *
     * @return string
     */
    public function getPeriodo() {
        return $this->periodo;
    }

    /**
     * Set secuencia
     *
     * @param string $secuencia
     * @return AfipSecuenciaInformada
     */
    public function setSecuencia($secuencia) {
        $this->secuencia = $secuencia;

        return $this;
    }

    /**
     * Get secuencia
     *
     * @return string
     */
    public function getSecuencia() {
        return $this->secuencia;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return AfipSecuenciaInformada
     */
    public function setActivo($activo) {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo() {
        return $this->activo;
    }

}