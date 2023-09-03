<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\ImpresoraFiscal
 * @ORM\Table(name="impresora_fiscal")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\FacturaRepository")
 */
class ImpresoraFiscal {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Caja", inversedBy="aperturas")
     * @ORM\JoinColumn(name="caja_id", referencedColumnName="id")
     */
    protected $caja;

    /**
     * @var date $fechaDesda
     * @ORM\Column(name="fecha_desde", type="datetime", nullable=true)
     */
    protected $fechaDesde;

    /**
     * @var date $fechaHasta
     * @ORM\Column(name="fecha_hasta", type="datetime", nullable=true)
     */
    protected $fechaHasta;

    /**
     * @var date $comando
     * @ORM\Column(name="comando", type="string", nullable=false)
     */
    protected $comando;

    /**
     * @var date $data
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    protected $data;

    /**
     * @ORM\Column(name="error", type="boolean",nullable=true)
     */
    protected $error = false;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User $updatedBy
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fechaDesde
     *
     * @param \DateTime $fechaDesde
     * @return ImpresoraFiscal
     */
    public function setFechaDesde($fechaDesde) {
        $this->fechaDesde = $fechaDesde;

        return $this;
    }

    /**
     * Get fechaDesde
     *
     * @return \DateTime
     */
    public function getFechaDesde() {
        return $this->fechaDesde;
    }

    /**
     * Set fechaHasta
     *
     * @param \DateTime $fechaHasta
     * @return ImpresoraFiscal
     */
    public function setFechaHasta($fechaHasta) {
        $this->fechaHasta = $fechaHasta;

        return $this;
    }

    /**
     * Get fechaHasta
     *
     * @return \DateTime
     */
    public function getFechaHasta() {
        return $this->fechaHasta;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return ImpresoraFiscal
     */
    public function setData($data) {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return ImpresoraFiscal
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return ImpresoraFiscal
     */
    public function setUpdated($updated) {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Set caja
     *
     * @param \ConfigBundle\Entity\Caja $caja
     * @return ImpresoraFiscal
     */
    public function setCaja(\ConfigBundle\Entity\Caja $caja = null) {
        $this->caja = $caja;

        return $this;
    }

    /**
     * Get caja
     *
     * @return \ConfigBundle\Entity\Caja
     */
    public function getCaja() {
        return $this->caja;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return ImpresoraFiscal
     */
    public function setUnidadNegocio(\ConfigBundle\Entity\UnidadNegocio $unidadNegocio = null) {
        $this->unidadNegocio = $unidadNegocio;

        return $this;
    }

    /**
     * Get unidadNegocio
     *
     * @return \ConfigBundle\Entity\UnidadNegocio
     */
    public function getUnidadNegocio() {
        return $this->unidadNegocio;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return ImpresoraFiscal
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return ImpresoraFiscal
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    /**
     * Set comando
     *
     * @param string $comando
     * @return ImpresoraFiscal
     */
    public function setComando($comando) {
        $this->comando = $comando;

        return $this;
    }

    /**
     * Get comando
     *
     * @return string
     */
    public function getComando() {
        return $this->comando;
    }


    /**
     * Set error
     *
     * @param boolean $error
     * @return ImpresoraFiscal
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return boolean 
     */
    public function getError()
    {
        return $this->error;
    }
}
