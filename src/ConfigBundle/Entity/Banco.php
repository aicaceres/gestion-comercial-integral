<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ConfigBundle\Entity\Banco
 * @ORM\Table(name="banco")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\BancoRepository")
 * @UniqueEntity(
 *     fields={"nombre"},
 *     errorPath="nombre",
 *     message="Registro duplicado."
 * )
 */
class Banco {
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
     * @var string $activo
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    protected $activo = true;

    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\Cheque", mappedBy="banco")
     */
    protected $cheques;

    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\CuentaBancaria", mappedBy="banco", cascade={"persist","remove"}, orphanRemoval=true)
     */
    protected $cuentas;

    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\BancoMovimiento", mappedBy="banco")
     */
    protected $movimientos;

    /**
     * Constructor
     */
    public function __construct() {
        $this->cheques = new \Doctrine\Common\Collections\ArrayCollection();
        $this->movimientos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString() {
        return $this->nombre;
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
     * @return Banco
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
     * Add cheques
     *
     * @param \ConfigBundle\Entity\Cheque $cheques
     * @return Banco
     */
    public function addCheque(\ConfigBundle\Entity\Cheque $cheques) {
        $this->cheques[] = $cheques;

        return $this;
    }

    /**
     * Remove cheques
     *
     * @param \ConfigBundle\Entity\Cheque $cheques
     */
    public function removeCheque(\ConfigBundle\Entity\Cheque $cheques) {
        $this->cheques->removeElement($cheques);
    }

    /**
     * Get cheques
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCheques() {
        return $this->cheques;
    }

    /**
     * Add movimientos
     *
     * @param \ConfigBundle\Entity\BancoMovimiento $movimientos
     * @return Banco
     */
    public function addMovimiento(\ConfigBundle\Entity\BancoMovimiento $movimientos) {
        $this->movimientos[] = $movimientos;

        return $this;
    }

    /**
     * Remove movimientos
     *
     * @param \ConfigBundle\Entity\BancoMovimiento $movimientos
     */
    public function removeMovimiento(\ConfigBundle\Entity\BancoMovimiento $movimientos) {
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
     * Set activo
     *
     * @param boolean $activo
     * @return Banco
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
     * Add cuentas
     *
     * @param \ConfigBundle\Entity\CuentaBancaria $cuentas
     * @return Banco
     */
    public function addCuenta(\ConfigBundle\Entity\CuentaBancaria $cuentas) {
        $cuentas->setBanco($this);
        $this->cuentas[] = $cuentas;
        return $this;
    }

    /**
     * Remove cuentas
     *
     * @param \ConfigBundle\Entity\CuentaBancaria $cuentas
     */
    public function removeCuenta(\ConfigBundle\Entity\CuentaBancaria $cuentas) {
        $this->cuentas->removeElement($cuentas);
    }

    /**
     * Get cuentas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCuentas() {
        return $this->cuentas;
    }

}