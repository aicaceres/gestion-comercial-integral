<?php

namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ComprasBundle\Entity\CentroCostoDetalle
 *
 * @ORM\Table(name="centro_costo_detalle")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\FacturaRepository")
 */
class CentroCostoDetalle {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\FacturaDetalle", inversedBy="centroCostoDetalle")
     * @ORM\JoinColumn(name="factura_detalle_id", referencedColumnName="id")
     */
    protected $facturaDetalle;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\CentroCosto")
     * @ORM\JoinColumn(name="centro_costo_id", referencedColumnName="id")
     */
    protected $centroCosto;

    /**
     * @var integer $costo
     * @ORM\Column(name="costo", type="decimal", scale=3 )
     */
    protected $costo;

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

    public function __toString() {
        return $this->getCosto();
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
     * Set costo
     *
     * @param string $costo
     * @return CentroCostoDetalle
     */
    public function setCosto($costo) {
        $this->costo = $costo;

        return $this;
    }

    /**
     * Get costo
     *
     * @return string
     */
    public function getCosto() {
        return $this->costo;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return CentroCostoDetalle
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
     * @return CentroCostoDetalle
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
     * Set facturaDetalle
     *
     * @param \ComprasBundle\Entity\FacturaDetalle $facturaDetalle
     * @return CentroCostoDetalle
     */
    public function setFacturaDetalle(\ComprasBundle\Entity\FacturaDetalle $facturaDetalle = null) {
        $this->facturaDetalle = $facturaDetalle;

        return $this;
    }

    /**
     * Get facturaDetalle
     *
     * @return \ComprasBundle\Entity\FacturaDetalle
     */
    public function getFacturaDetalle() {
        return $this->facturaDetalle;
    }

    /**
     * Set CentroCosto
     *
     * @param \ConfigBundle\Entity\CentroCosto $centroCosto
     * @return CentroCostoDetalle
     */
    public function setCentroCosto(\ConfigBundle\Entity\CentroCosto $centroCosto = null) {
        $this->centroCosto = $centroCosto;

        return $this;
    }

    /**
     * Get CentroCosto
     *
     * @return \ConfigBundle\Entity\CentroCosto
     */
    public function getCentroCosto() {
        return $this->centroCosto;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return CentroCostoDetalle
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
     * @return CentroCostoDetalle
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

}