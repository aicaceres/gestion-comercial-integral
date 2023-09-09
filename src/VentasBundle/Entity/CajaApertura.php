<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\CajaApertura
 * @ORM\Table(name="ventas_caja_apertura")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\CajaAperturaRepository")
 * @Gedmo\Loggable()
 */
class CajaApertura {
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
     * @var date $fechaApertura
     * @ORM\Column(name="fecha_apertura", type="datetime", nullable=false)
     * @Gedmo\Versioned()
     */
    protected $fechaApertura;

    /**
     * @var integer $montoApertura
     * @ORM\Column(name="monto_apertura", type="decimal", scale=2 )
     */
    protected $montoApertura = 0;

    /**
     * @var date $fechaCierre
     * @ORM\Column(name="fecha_cierre", type="datetime", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $fechaCierre;

    /**
     * @var integer $montoCierre
     * @ORM\Column(name="monto_cierre", type="decimal", scale=2, nullable=true )
     * @Gedmo\Versioned()
     */
    protected $montoCierre = 0;

    /**
     * @var integer $cambios
     * @ORM\Column(name="cambios", type="decimal", scale=2, nullable=true )
     */
    protected $cambios;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\CobroDetalle", mappedBy="cajaApertura")
     */
    protected $movimientos;

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

    /**   Diferencia encontrada entre la apertura y cierre    */
    public function getDiferencia() {
        return ($this->getMontoApertura() + $this->getTotalMovimientos()) - $this->getMontoCierre();
    }

    /** suma total de movimientos entre apertura y cierre */
    public function getTotalMovimientos() {
        $total = 0;
        // foreach($this->getIngresos() as $mov ){
        //         $total += $mov->getPago();
        // }
        // foreach($this->getGastos() as $mov ){
        //         $total -= $mov->getTotal();
        // }
        return $total;
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
     * Set fechaApertura
     *
     * @param \DateTime $fechaApertura
     * @return CajaApertura
     */
    public function setFechaApertura($fechaApertura) {
        $this->fechaApertura = $fechaApertura;

        return $this;
    }

    /**
     * Get fechaApertura
     *
     * @return \DateTime
     */
    public function getFechaApertura() {
        return $this->fechaApertura;
    }

    /**
     * Set montoApertura
     *
     * @param string $montoApertura
     * @return CajaApertura
     */
    public function setMontoApertura($montoApertura) {
        $this->montoApertura = $montoApertura;

        return $this;
    }

    /**
     * Get montoApertura
     *
     * @return string
     */
    public function getMontoApertura() {
        return $this->montoApertura;
    }

    /**
     * Set fechaCierre
     *
     * @param \DateTime $fechaCierre
     * @return CajaApertura
     */
    public function setFechaCierre($fechaCierre) {
        $this->fechaCierre = $fechaCierre;

        return $this;
    }

    /**
     * Get fechaCierre
     *
     * @return \DateTime
     */
    public function getFechaCierre() {
        return $this->fechaCierre;
    }

    /**
     * Set montoCierre
     *
     * @param string $montoCierre
     * @return CajaApertura
     */
    public function setMontoCierre($montoCierre) {
        $this->montoCierre = $montoCierre;

        return $this;
    }

    /**
     * Get montoCierre
     *
     * @return string
     */
    public function getMontoCierre() {
        return $this->montoCierre;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return CajaApertura
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
     * @return CajaApertura
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
     * @return CajaApertura
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return CajaApertura
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
     * @return CajaApertura
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
     * Constructor
     */
    public function __construct() {
        $this->movimientos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add movimientos
     *
     * @param \VentasBundle\Entity\CobroDetalle $movimientos
     * @return CajaApertura
     */
    public function addMovimiento(\VentasBundle\Entity\CobroDetalle $movimientos) {
        $this->movimientos[] = $movimientos;

        return $this;
    }

    /**
     * Remove movimientos
     *
     * @param \VentasBundle\Entity\CobroDetalle $movimientos
     */
    public function removeMovimiento(\VentasBundle\Entity\CobroDetalle $movimientos) {
        $this->movimientos->removeElement($movimientos);
    }

    /**
     * Get movimientos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMovimientos() {
        return $this->movimientos;
    }

    /**
     * Set cambios
     *
     * @param string $cambios
     * @return CajaApertura
     */
    public function setCambios($cambios) {
        $this->cambios = $cambios;

        return $this;
    }

    /**
     * Get cambios
     *
     * @return string
     */
    public function getCambios() {
        return $this->cambios;
    }

}