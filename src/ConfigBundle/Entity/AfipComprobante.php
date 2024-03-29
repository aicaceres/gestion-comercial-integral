<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\AfipComprobante
 * @ORM\Table(name="afip_comprobante")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\ParametroRepository")
 */
class AfipComprobante {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $codigo
     * @ORM\Column(name="codigo", type="string", nullable=false)
     */
    protected $codigo;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     */
    protected $nombre;

    /**
     * @var string $valor
     * @ORM\Column(name="valor", type="string", nullable=true)
     */
    protected $valor;

    /**
     * @var string $activo
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    protected $activo = true;

    /**
     * @var string $visibleCompras
     * @ORM\Column(name="visible_compras", type="boolean", nullable=true)
     */
    protected $visibleCompras = false;

    public function __toString() {
        return $this->codigo . ' - ' . $this->nombre;
    }

    public function getSigno() {
        $tipo = substr($this->getValor(), 0, 3);
        return ( ($tipo == 'DEB') ? '+' : '-' );
    }

    public function getClase() {
        $clase = substr($this->getValor(), 0, 3);
        return $clase;
    }

    public function getLetra() {
        $letra = explode('-', $this->getValor());
        return $letra[1];
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
     * Set nombre
     *
     * @param string $nombre
     * @return AfipComprobante
     */
    public function setNombre($nombre) {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre() {
        return $this->nombre;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return AfipComprobante
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

    /**
     * Set visibleCompras
     *
     * @param boolean $visibleCompras
     * @return AfipComprobante
     */
    public function setVisibleCompras($visibleCompras) {
        $this->visibleCompras = $visibleCompras;

        return $this;
    }

    /**
     * Get visibleCompras
     *
     * @return boolean
     */
    public function getVisibleCompras() {
        return $this->visibleCompras;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return AfipComprobante
     */
    public function setCodigo($codigo) {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo() {
        return $this->codigo;
    }

    /**
     * Set valor
     *
     * @param string $valor
     * @return AfipComprobante
     */
    public function setValor($valor) {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string
     */
    public function getValor() {
        return $this->valor;
    }

}