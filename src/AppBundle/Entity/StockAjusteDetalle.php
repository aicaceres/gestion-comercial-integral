<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * AppBundle\Entity\StockAjsuteDetalle
 *
 * @ORM\Table(name="stock_ajuste_detalle")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 */
class StockAjusteDetalle
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $signo
     * @ORM\Column(name="signo", type="string")
     */
    private $signo='+';

     /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=3)
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
     * @ORM\Column(name="motivo", type="text", nullable=true)
     */
    protected $motivo;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto", inversedBy="stock")
     * @ORM\JoinColumn(name="producto_id", referencedColumnName="id")
     */
    protected $producto;

     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\StockAjuste", inversedBy="detalles")
     *@ORM\JoinColumn(name="stock_ajuste_id", referencedColumnName="id")
     */
    protected $stockAjuste;

     /**
     * @ORM\ManyToMany(targetEntity="ComprasBundle\Entity\LoteProducto", inversedBy="ajusteDetalles",cascade={"persist"})
     * @ORM\JoinTable(name="lotes_x_ajustedetalle",
     *      joinColumns={@ORM\JoinColumn(name="stock_ajuste_detalle_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="compras_lote_producto_id", referencedColumnName="id")}
     * )
     */
    private $lotes;

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
     * Set signo
     *
     * @param string $signo
     * @return StockAjusteDetalle
     */
    public function setSigno($signo)
    {
        $this->signo = $signo;
        return $this;
    }

    /**
     * Get signo
     *
     * @return string
     */
    public function getSigno()
    {
        return $this->signo;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return StockAjusteDetalle
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
     * Set motivo
     *
     * @param string $motivo
     * @return StockAjusteDetalle
     */
    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;
        return $this;
    }

    /**
     * Get motivo
     *
     * @return string
     */
    public function getMotivo()
    {
        return $this->motivo;
    }

    /**
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return StockAjusteDetalle
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
     * Set stockAjuste
     *
     * @param \AppBundle\Entity\StockAjuste $stockAjuste
     * @return StockAjusteDetalle
     */
    public function setStockAjuste(\AppBundle\Entity\StockAjuste $stockAjuste = null)
    {
        $this->stockAjuste = $stockAjuste;

        return $this;
    }

    /**
     * Get stockAjuste
     *
     * @return \AppBundle\Entity\StockAjuste
     */
    public function getStockAjuste()
    {
        return $this->stockAjuste;
    }

    /**
     * Set bulto
     *
     * @param boolean $bulto
     * @return StockAjusteDetalle
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
     * @return StockAjusteDetalle
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

    public function getLotesTxt(){
        $txt = '';
        foreach ($this->lotes as $value) {
           $txt = $txt. '<br> '. $value->__toString();
        }
        return $txt;
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
     * @return StockAjusteDetalle
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
}
