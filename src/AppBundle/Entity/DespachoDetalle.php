<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AppBundle\Entity\DespachoDetalle
 * @ORM\Table(name="stock_despacho_detalle")
 * @ORM\Entity()
 */
class DespachoDetalle
{
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
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto")
     *@ORM\JoinColumn(name="producto_id", referencedColumnName="id")
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
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\PedidoDetalle", inversedBy="despachoDetalle")
    * @ORM\JoinColumn(name="stock_pedido_detalle_id", referencedColumnName="id")
    */
    protected $pedidoDetalle;

     /**
     * @ORM\ManyToMany(targetEntity="ComprasBundle\Entity\LoteProducto", inversedBy="despachoDetalles")
     * @ORM\JoinTable(name="lotes_x_despachodetalle",
     *      joinColumns={@ORM\JoinColumn(name="stock_despacho_detalle_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="compras_lote_producto_id", referencedColumnName="id")}
     * )
     */
    private $lotes;

     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Despacho", inversedBy="detalles")
     *@ORM\JoinColumn(name="stock_despacho_id", referencedColumnName="id")
     */
    protected $despacho;

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


    // variable stock para mostrar en despacho
    protected $stock = 0;
    public function getStock(){
        return $this->stock;
    }
    public function setStock($stock)
    {
        $this->stock = $stock;
        return $this;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return DespachoDetalle
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return DespachoDetalle
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set entregado
     *
     * @param integer $entregado
     * @return DespachoDetalle
     */
    public function setEntregado($entregado)
    {
        $this->entregado = $entregado;

        return $this;
    }

    /**
     * Get entregado
     *
     * @return integer
     */
    public function getEntregado()
    {
        return  $this->entregado  ;
    }

    /**
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return DespachoDetalle
     */
    public function setProducto(\AppBundle\Entity\Producto $producto = null)
    {
        $this->producto = $producto;

        return $this;
    }

    /**
     * Get producto
     *
     * @return \AppBundle\Entity\Producto
     */
    public function getProducto()
    {
        return $this->producto;
    }

     /**
     * Set despacho
     *
     * @param \AppBundle\Entity\Despacho $despacho
     * @return DespachoDetalle
     */
    public function setDespacho(\AppBundle\Entity\Despacho $despacho = null)
    {
        $this->despacho = $despacho;

        return $this;
    }

    /**
     * Get despacho
     *
     * @return \AppBundle\Entity\Despacho
     */
    public function getDespacho()
    {
        return $this->despacho;
    }

    /**
     * Set bulto
     *
     * @param boolean $bulto
     * @return DespachoDetalle
     */
    public function setBulto($bulto)
    {
        $this->bulto = $bulto;

        return $this;
    }

    /**
     * Get bulto
     *
     * @return boolean
     */
    public function getBulto()
    {
        return $this->bulto;
    }

    /**
     * Set cantidadxBulto
     *
     * @param integer $cantidadxBulto
     * @return DespachoDetalle
     */
    public function setCantidadxBulto($cantidadxBulto)
    {
        $this->cantidadxBulto = $cantidadxBulto;

        return $this;
    }

    /**
     * Get cantidadxBulto
     *
     * @return integer
     */
    public function getCantidadxBulto()
    {
        return $this->cantidadxBulto;
    }

    /**
     * Set pedidoDetalle
     *
     * @param \AppBundle\Entity\PedidoDetalle $pedidoDetalle
     * @return DespachoDetalle
     */
    public function setPedidoDetalle(\AppBundle\Entity\PedidoDetalle $pedidoDetalle = null)
    {
        $this->pedidoDetalle = $pedidoDetalle;

        return $this;
    }

    /**
     * Get pedidoDetalle
     *
     * @return \AppBundle\Entity\PedidoDetalle
     */
    public function getPedidoDetalle()
    {
        return $this->pedidoDetalle;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lotes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add lotes
     *
     * @param \ComprasBundle\Entity\LoteProducto $lotes
     * @return DespachoDetalle
     */
    public function addLote(\ComprasBundle\Entity\LoteProducto $lotes)
    {
        $this->lotes[] = $lotes;
        return $this;
    }

    /**
     * Remove lotes
     *
     * @param \ComprasBundle\Entity\LoteProducto $lotes
     */
    public function removeLote(\ComprasBundle\Entity\LoteProducto $lotes)
    {
        $this->lotes->removeElement($lotes);
    }

    /**
     * Get lotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLotes()
    {
        return $this->lotes;
    }

    public function getLotesTxt(){
        $txt = '';
        foreach ($this->lotes as $value) {
           $txt = $txt. '<br> '. $value->__toString();
        }
        return $txt;
    }




    public function getCantidadTxt()
    {
        if( $this->bulto ){
            $txt = $this->getCantidad().' x '.$this->getCantidadxBulto().' '.$this->getProducto()->getUnidadMedida()->getNombre();
        }else{
            $txt = $this->getCantidad().' '. $this->getProducto()->getUnidadMedida()->getNombre();
        }
        return $txt;
    }

    public function getCantidadTotal(){
        if( $this->bulto )  return $this->cantidad * $this->cantidadxBulto;
        else                return $this->cantidad;
    }
  /*  public function getEntregadoTotal(){
        if( $this->bulto )  return $this->entregado * $this->cantidadxBulto;
        else                return $this->entregado;
    }*/

    public function getEntregadoTxt()
    {
        if( is_null( $this->entregado ) || $this->getDespacho()->getEstado() !='ENTREGADO' )
            return '0 '. $this->getProducto()->getUnidadMedida()->getNombre();
        $txt =number_format((float)$this->getEntregado(), 3, '.', '').' '. $this->getProducto()->getUnidadMedida()->getNombre();
        return $txt;
    }
    public function getRecibidoTxt()
    {
        if( is_null( $this->entregado ) )
            return '0 '. $this->getProducto()->getUnidadMedida()->getNombre();
        $txt =number_format((float)$this->getEntregado(), 3, '.', '').' '. $this->getProducto()->getUnidadMedida()->getNombre();
        return $txt;
    }
    public function getSolicitadoTxt()
    {
        if( is_null( $this->getPedidoDetalle() ) )
            return NULL;
        return $this->getPedidoDetalle()->getCantidadTxt();
    }

    public function hayInconsistencia(){
        if( $this->getDespacho()->getEstado()=='ENTREGADO' && $this->getCantidadTotal() <> $this->getEntregado() )
            return true;
        else
            return false;
    }


// Para detalle del despacho
    public function getCantidadItemTxt()
    {
        if( $this->bulto ){
            $txt = number_format((float)$this->getCantidad(), 3, '.', '') .' x '.$this->getCantidadxBulto().' '.$this->getProducto()->getUnidadMedida()->getNombre();
        }else{
            $txt = number_format((float)$this->getCantidad(), 3, '.', '') .' '. $this->getProducto()->getUnidadMedida()->getNombre();
        }
        return $txt;
    }
    public function getCantidadItemTotal(){
        if( $this->bulto )  return number_format((float) ($this->getCantidad()*$this->cantidadxBulto), 3, '.', '');
        else                return number_format((float)$this->getCantidad(), 3, '.', '');
    }
    public function getLotesItemTxt(){
        $txt = '';
        $div = '<br>';
        foreach ($this->lotes as $value) {
           $txt = $txt. $div. $value->__toString();
           $div = ' / ';
        }
        return $txt;
    }
    public function getLotesInputTxt(){
        $txt = '';
        $div = '';
        foreach ($this->lotes as $value) {
           $txt = $txt. $div. $value->__toString();
           $div = ' / ';
        }
        return $txt;
    }


    /**
     * Set created
     *
     * @param \DateTime $created
     * @return DespachoDetalle
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return DespachoDetalle
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return DespachoDetalle
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return DespachoDetalle
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set precio
     *
     * @param string $precio
     * @return DespachoDetalle
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return string
     */
    public function getPrecio()
    {
        return $this->precio;
    }
}
