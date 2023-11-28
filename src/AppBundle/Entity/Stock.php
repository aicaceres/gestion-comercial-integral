<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * AppBundle\Entity\Stock
 *
 * @ORM\Table(name="stock",uniqueConstraints={@ORM\UniqueConstraint(name="prodxdep_idx", columns={"deposito_id", "producto_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 * @UniqueEntity(
 *     fields={"deposito", "producto"},
 *     errorPath="producto",
 *     message="Registro de stock duplicado."
 * )
 * @Gedmo\Loggable()
 */
class Stock {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto", inversedBy="stock")
     * @ORM\JoinColumn(name="producto_id", referencedColumnName="id")
     */
    protected $producto;

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=3 )
     * @Gedmo\Versioned()
     */
    protected $cantidad = 0;

    /**
     * @var string $stock_minimo
     * @ORM\Column(name="stock_minimo", type="decimal", scale=3,  nullable=true)
     * @Gedmo\Versioned()
     */
    protected $stockMinimo;

    /**
     * @var integer $costo
     * @ORM\Column(name="costo", type="decimal", precision=15, scale=3, nullable=true )
     */
    protected $costo;

    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return Stock
     */
    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer
     */
    public function getCantidad() {
        return $this->cantidad;
    }

    /**
     * Set costo
     *
     * @param string $costo
     * @return Stock
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
     * @return Stock
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
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return Stock
     */
    public function setProducto(\AppBundle\Entity\Producto $producto = null) {
        $this->producto = $producto;
        return $this;
    }

    /**
     * Get producto
     *
     * @return \AppBundle\Entity\Producto
     */
    public function getProducto() {
        return $this->producto;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Stock
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
     * Set deposito
     *
     * @param \AppBundle\Entity\Deposito $deposito
     * @return Stock
     */
    public function setDeposito(\AppBundle\Entity\Deposito $deposito = null) {
        $this->deposito = $deposito;
        return $this;
    }

    /**
     * Get deposito
     *
     * @return \AppBundle\Entity\Deposito
     */
    public function getDeposito() {
        return $this->deposito;
    }

    /**
     * Set stockMinimo
     *
     * @param string $stockMinimo
     * @return Stock
     */
    public function setStockMinimo($stockMinimo) {
        $this->stockMinimo = $stockMinimo;

        return $this;
    }

    /**
     * Get stockMinimo
     *
     * @return string
     */
    public function getStockMinimo() {
        return $this->stockMinimo;
    }

    /** Calculos    * */
    public function getValorizado() {
        return $this->getProducto()->getCosto() * $this->cantidad;
    }

}