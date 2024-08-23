<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AppBundle\Entity\PedidoDetalle
 * @ORM\Table(name="stock_pedido_detalle")
 * @ORM\Entity()
 */
class PedidoDetalle {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $orden
     * @ORM\Column(name="orden", type="integer",nullable=true)
     */
    protected $orden;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto")
     * @ORM\JoinColumn(name="producto_id", referencedColumnName="id")
     */
    protected $producto;

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", precision=20, scale=2 )
     */
    protected $cantidad;

    /**
     * @ORM\Column(name="bulto", type="boolean", nullable=true)
     */
    protected $bulto = false;

    /**
     * @var integer $cantidadxBulto
     * @ORM\Column(name="cantidad_x_bulto", type="integer", nullable=true )
     */
    protected $cantidadxBulto;

    /**
     * @var string $cantidadStock
     * @ORM\Column(name="cantidad_stock", type="decimal", precision=20, scale=2,  nullable=true)
     */
    protected $cantidadStock;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pedido", inversedBy="detalles")
     * @ORM\JoinColumn(name="stock_pedido_id", referencedColumnName="id")
     */
    protected $pedido;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\DespachoDetalle", mappedBy="pedidoDetalle")
     */
    private $despachoDetalle;

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
     * Set orden
     *
     * @param integer $orden
     * @return PedidoDetalle
     */
    public function setOrden($orden) {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden() {
        return $this->orden;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return PedidoDetalle
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
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return PedidoDetalle
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
     * Set pedido
     *
     * @param \AppBundle\Entity\Pedido $pedido
     * @return PedidoDetalle
     */
    public function setPedido(\AppBundle\Entity\Pedido $pedido = null) {
        $this->pedido = $pedido;

        return $this;
    }

    /**
     * Get pedido
     *
     * @return \AppBundle\Entity\Pedido
     */
    public function getPedido() {
        return $this->pedido;
    }

    /**
     * Set bulto
     *
     * @param boolean $bulto
     * @return PedidoDetalle
     */
    public function setBulto($bulto) {
        $this->bulto = $bulto;

        return $this;
    }

    /**
     * Get bulto
     *
     * @return boolean
     */
    public function getBulto() {
        return $this->bulto;
    }

    /**
     * Set cantidadxBulto
     *
     * @param string $cantidadxBulto
     * @return PedidoDetalle
     */
    public function setCantidadxBulto($cantidadxBulto) {
        $this->cantidadxBulto = $cantidadxBulto;

        return $this;
    }

    /**
     * Get cantidadxBulto
     *
     * @return string
     */
    public function getCantidadxBulto() {
        return $this->cantidadxBulto;
    }

    /**
     * Set despachoDetalle
     *
     * @param \AppBundle\Entity\DespachoDetalle $despachoDetalle
     * @return PedidoDetalle
     */
    public function setDespachoDetalle(\AppBundle\Entity\DespachoDetalle $despachoDetalle = null) {
        $this->despachoDetalle = $despachoDetalle;

        return $this;
    }

    /**
     * Get despachoDetalle
     *
     * @return \AppBundle\Entity\DespachoDetalle
     */
    public function getDespachoDetalle() {
        return $this->despachoDetalle;
    }

// Funciones
    public function __toString() {
        return strval($this->id);
    }

    public function getDespachado() {
        if ($this->getDespachoDetalle())
            return $this->getDespachoDetalle()->getCantidadTotal();
        else
            return 0;
    }

    public function getEntregado() {
        if ($this->getDespachoDetalle())
            return $this->getDespachoDetalle()->getEntregado();
        else
            return 0;
    }

    public function getEntregadoTxt() {
        if ($this->getDespachoDetalle())
            return $this->getDespachoDetalle()->getEntregadoTxt();
        else
            return 0;
    }

    public function getDespachadoTxt() {
        return $this->getDespachado() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
    }

    /** Calculos * */
    public function getItemsPendientes() {
        return ( ($this->cantidad - $this->getEntregado()) > 0 ) ? 1 : 0;
    }

    public function getCantidadTotal() {
        if ($this->bulto)
            return $this->cantidad * $this->cantidadxBulto;
        else
            return $this->cantidad;
    }

    public function getCantidadTxt() {
        if ($this->bulto) {
            $txt = $this->getCantidad() . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            $txt = $this->getCantidad() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        return $txt;
    }

// Para detalle del pedido
    public function getCantidadItemTxt() {
        if ($this->bulto) {
            $txt = number_format((float) $this->getCantidad(), 3, '.', '') . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            $txt = number_format((float) $this->getCantidad(), 3, '.', '') . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        return $txt;
    }

    public function getCantidadItemTotal() {
        if ($this->bulto)
            return number_format((float) ($this->getCantidad() * $this->cantidadxBulto), 3, '.', '');
        else
            return number_format((float) $this->getCantidad(), 3, '.', '');
    }

    public function getPendiente() {
        if (is_null($this->entregado))
            return $this->cantidad;
        else
            return $this->getCantidad() - $this->getEntregado();
    }

    public function getPendienteTxt() {
        // si no se entregó nada devolver la cantidad
        if (is_null($this->entregado))
            return $this->getCantidadTxt();
        // si ya se entregó algo calcular la diferencia
        $dif = $this->getCantidad() - $this->getEntregado();
        if ($this->bulto) {
            $txt = $dif . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            $txt = $dif . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        return $txt;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return PedidoDetalle
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
     * @return PedidoDetalle
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return PedidoDetalle
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
     * @return PedidoDetalle
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
     * Set cantidadStock
     *
     * @param string $cantidadStock
     * @return PedidoDetalle
     */
    public function setCantidadStock($cantidadStock) {
        $this->cantidadStock = $cantidadStock;

        return $this;
    }

    /**
     * Get cantidadStock
     *
     * @return string
     */
    public function getCantidadStock() {
        return $this->cantidadStock;
    }

}