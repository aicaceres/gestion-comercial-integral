<?php

namespace ComprasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ComprasBundle\Entity\PedidoDetalle
 * @ORM\Table(name="compras_pedido_detalle")
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
     * @ORM\Column(name="cantidad", type="decimal", scale=3 )
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
     * @var string $cantidadOriginal
     * @ORM\Column(name="cantidad_original", type="string", nullable=true)
     */
    protected $cantidadOriginal;

    /**
     * @var string $cantidadTotalOriginal
     * @ORM\Column(name="cantidad_total_original", type="string", nullable=true)
     */
    protected $cantidadTotalOriginal;

    /**
     * @var integer $precio
     * @ORM\Column(name="precio", type="decimal", precision=15, scale=3, nullable=true )
     */
    protected $precio;

    /**
     * @var integer $entregado
     * @ORM\Column(name="entregado", type="decimal", scale=3, nullable=true)
     */
    protected $entregado;

    /**
     * @var string $cantidadStock
     * @ORM\Column(name="cantidad_stock", type="decimal", scale=3,  nullable=true)
     */
    protected $cantidadStock;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Pedido", inversedBy="detalles")
     * @ORM\JoinColumn(name="compras_pedido_id", referencedColumnName="id")
     */
    protected $pedido;

    /**
     * @ORM\ManyToMany(targetEntity="ComprasBundle\Entity\LoteProducto", inversedBy="compraPedidoDetalles")
     * @ORM\JoinTable(name="lotes_x_compradetalle",
     *      joinColumns={@ORM\JoinColumn(name="compras_pedido_detalle_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="compras_lote_producto_id", referencedColumnName="id")}
     * )
     */
    private $lotes;

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
     * @param \ComprasBundle\Entity\Pedido $pedido
     * @return PedidoDetalle
     */
    public function setPedido(\ComprasBundle\Entity\Pedido $pedido = null) {
        $this->pedido = $pedido;

        return $this;
    }

    /**
     * Get pedido
     *
     * @return \ComprasBundle\Entity\Pedido
     */
    public function getPedido() {
        return $this->pedido;
    }

    /**
     * Set precio
     *
     * @param string $precio
     * @return PedidoDetalle
     */
    public function setPrecio($precio) {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return string
     */
    public function getPrecio() {
        return $this->precio;
    }

    /**
     * Set entregado
     *
     * @param integer $entregado
     * @return PedidoDetalle
     */
    public function setEntregado($entregado) {
        $this->entregado = $entregado;

        return $this;
    }

    /**
     * Get entregado
     *
     * @return integer
     */
    public function getEntregado() {
        return $this->entregado;
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
     * Set cantidadOriginal
     *
     * @param string $cantidadOriginal
     * @return PedidoDetalle
     */
    public function setCantidadOriginal($cantidadOriginal) {
        $this->cantidadOriginal = $cantidadOriginal;

        return $this;
    }

    /**
     * Get cantidadOriginal
     *
     * @return string
     */
    public function getCantidadOriginal() {
        return $this->cantidadOriginal;
    }

    /** Calculos * */
    // Costo total del item
    public function getCostoTotal() {
        $cantidad = ($this->getPedido()->getEstado() == 'RECIBIDO') ? $this->entregado : $this->cantidad;
        return $this->precio * $cantidad;
    }

    // Costo total del item del pedido original
    public function getCostoTotalOriginal() {
        return $this->precio * $this->cantidad;
    }

    // Cantidad para mostrar como texto
    public function getCantidadTxt() {
        if ($this->cantidadOriginal)
            return $this->getCantidadOriginal();
        if ($this->bulto) {
            return number_format((float) $this->getCantidad(), 3, '.', '') . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            return number_format((float) $this->getCantidad(), 3, '.', '') . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
    }

    // Cantidad entregada para mostrar como texto
    public function getEntregadoTxt() {
        if (is_null($this->entregado) || $this->entregado == 0 &&
                ($this->getPedido()->getEstado() != 'RECIBIDO' || $this->getPedido()->getEstado() != 'FACTURADO')) {
            return '';
        }
        if ($this->bulto) {
            return $this->getEntregado() . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            return $this->getEntregado() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
    }

    // Cantidad total de items
    public function getCantidadTotal() {
        if ($this->cantidadTotalOriginal)
            return $this->cantidadTotalOriginal;
        if ($this->bulto) {
            return number_format((float) ($this->cantidad * $this->cantidadxBulto), 3, '.', '');
        }
        else {
            return number_format((float) $this->cantidad, 3, '.', '');
        }
    }

    public function getEntregadoTotal() {

        if (is_null($this->entregado) || $this->entregado == 0 &&
                ($this->getPedido()->getEstado() != 'RECIBIDO' || $this->getPedido()->getEstado() != 'FACTURADO'))
            return '';
        if ($this->bulto) {
            return $this->entregado * $this->cantidadxBulto;
        }
        else {
            return $this->entregado;
        }
    }

    /*   public function getPendientes(){
      return $this->cantidad - ( $this->entregado );
      }
      public function getTotalRecibido(){
      return $this->precio * $this->entregado;
      } */

    /*
      public function getPendiente(){
      if( is_null( $this->entregado ) )
      return $this->cantidad;
      else  return $this->getCantidad() - $this->getEntregado();
      }

      public function getPendienteTxt()
      {
      // si no se entregó nada devolver la cantidad
      if( is_null( $this->entregado ) )
      return $this->getCantidadTxt();
      // si ya se entregó algo calcular la diferencia
      $dif = $this->getCantidad()-$this->getEntregado();
      if( $this->bulto ){
      $txt =  $dif.' x '.$this->getCantidadxBulto().' '.$this->getProducto()->getUnidadMedida()->getNombre();
      }else{
      $txt = $dif .' '. $this->getProducto()->getUnidadMedida()->getNombre();
      }
      return $txt;
      }

     */

    public function getLotesTxt() {
        $txt = '';
        foreach ($this->lotes as $value) {
            $txt = $txt . '<br> ' . $value->__toString();
        }
        return $txt;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->lotes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add lotes
     *
     * @param \ComprasBundle\Entity\LoteProducto $lotes
     * @return PedidoDetalle
     */
    public function addLote(\ComprasBundle\Entity\LoteProducto $lotes) {
        // $lotes->setPedidoDetalle($this);
        $this->lotes[] = $lotes;
        return $this;
    }

    /**
     * Remove lotes
     *
     * @param \ComprasBundle\Entity\LoteProducto $lotes
     */
    public function removeLote(\ComprasBundle\Entity\LoteProducto $lotes) {
        $this->lotes->removeElement($lotes);
    }

    /**
     * Get lotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLotes() {
        return $this->lotes;
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
     * Set cantidadTotalOriginal
     *
     * @param string $cantidadTotalOriginal
     * @return PedidoDetalle
     */
    public function setCantidadTotalOriginal($cantidadTotalOriginal) {
        $this->cantidadTotalOriginal = $cantidadTotalOriginal;

        return $this;
    }

    /**
     * Get cantidadTotalOriginal
     *
     * @return string
     */
    public function getCantidadTotalOriginal() {
        return $this->cantidadTotalOriginal;
    }

//Agregados nuevos
    public function getCantidadItemTotal() {
        if ($this->bulto)
            return number_format((float) ($this->getCantidad() * $this->cantidadxBulto), 3, '.', '');
        else
            return number_format((float) $this->getCantidad(), 3, '.', '');
    }

    public function getCantidadItemTxt() {
        if ($this->bulto) {
            $txt = number_format((float) $this->getCantidad(), 3, '.', '') . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            $txt = number_format((float) $this->getCantidad(), 3, '.', '') . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        return $txt;
    }

    public function getLotesItemTxt() {
        $txt = '';
        $div = '<br>';
        foreach ($this->lotes as $value) {
            $txt = $txt . $div . $value->__toString();
            $div = ' / ';
        }
        return $txt;
    }

    public function getEntregadoItemTxt() {
        if (is_null($this->entregado) || $this->entregado == 0 &&
                ($this->getPedido()->getEstado() != 'RECIBIDO' || $this->getPedido()->getEstado() != 'FACTURADO')) {
            $ent = '';
        }
        if ($this->bulto && $this->cantidadxBulto) {
            $cant = $this->entregado / $this->cantidadxBulto;
            $ent = number_format((float) ($cant), 3, '.', '') . ' x ' . $this->getCantidadxBulto();
        }
        else
            $ent = number_format((float) $this->entregado, 3, '.', '');

        return $ent . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
    }

    public function getEntregadoItemTotalTxt() {
        if (is_null($this->entregado) || $this->entregado == 0 &&
                ($this->getPedido()->getEstado() != 'RECIBIDO' || $this->getPedido()->getEstado() != 'FACTURADO')) {
            $ent = number_format((float) 0, 3, '.', '');
        }
        else {
            $ent = number_format((float) $this->entregado, 3, '.', '');
        }

        return $ent . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
    }

    public function hayDiferencia() {
        $cant = ($this->getCantidadTotalOriginal()) ? $this->getCantidadTotalOriginal() : $this->entregado;
        if ($cant != $this->entregado) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getFaltanteDelItem() {
        if ($this->hayDiferencia()) {
            $dif = $this->getCantidadTotalOriginal() - $this->entregado;
            return ($dif <= 0) ? 0 : abs($dif);
        }
        else {
            return 0;
        }
    }

    public function xgetEntregadoItemTxt() {
        if (is_null($this->entregado) || $this->entregado == 0 ||
                ($this->getPedido()->getEstado() != 'RECIBIDO' && $this->getPedido()->getEstado() != 'FACTURADO')) {
            return '';
        }
        if ($this->bulto) {
            $ent = $this->getEntregado() / $this->getCantidadxBulto();
            return $ent . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            return $this->getEntregado() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
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