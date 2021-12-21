<?php
namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ComprasBundle\Entity\LoteProducto
 * @ORM\Table(name="compras_lote_producto")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\LoteProductoRepository") 
 */
class LoteProducto
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var integer $nroLote
     * @ORM\Column(name="nro_lote", type="string")
     */
    protected $nroLote;
    
    /**
     * @var datetime $fechaVencimiento
     * @ORM\Column(name="fecha_vencimiento", type="datetime")
     */
    private $fechaVencimiento;    

     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto", inversedBy="lotes")
     *@ORM\JoinColumn(name="producto_id", referencedColumnName="id") 
     */
    protected $producto;    
   
    /**
     * @ORM\ManyToMany(targetEntity="ComprasBundle\Entity\PedidoDetalle", mappedBy="lotes")
     */
    protected $compraPedidoDetalles;     
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\DespachoDetalle", mappedBy="lotes")
     */
    protected $despachoDetalles;      
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\StockAjusteDetalle", mappedBy="lotes")
     */
    protected $ajusteDetalles;    
  
    /**
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    protected $activo = true;    

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nroLote
     *
     * @param string $nroLote
     * @return LoteProducto
     */
    public function setNroLote($nroLote)
    {
        $this->nroLote = $nroLote;

        return $this;
    }

    /**
     * Get nroLote
     *
     * @return string 
     */
    public function getNroLote()
    {
        return $this->nroLote;
    }

    /**
     * Set fechaVencimiento
     *
     * @param \DateTime $fechaVencimiento
     * @return LoteProducto
     */
    public function setFechaVencimiento($fechaVencimiento)
    {
        $this->fechaVencimiento = $fechaVencimiento;

        return $this;
    }

    /**
     * Get fechaVencimiento
     *
     * @return \DateTime 
     */
    public function getFechaVencimiento()
    {
        return $this->fechaVencimiento;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return LoteProducto
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
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return LoteProducto
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return LoteProducto
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
     * Constructor
     */
    public function __construct()
    {
        $this->compraPedidoDetalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ajusteDetalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->despachoDetalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activo=1;
    }

    /**
     * Add ajusteDetalles
     *
     * @param \AppBundle\Entity\StockAjusteDetalle $ajusteDetalles
     * @return LoteProducto
     */
    public function addAjusteDetalle(\AppBundle\Entity\StockAjusteDetalle $ajusteDetalles)
    {
        $this->ajusteDetalles[] = $ajusteDetalles;
        return $this;
    }

    /**
     * Remove ajusteDetalles
     *
     * @param \AppBundle\Entity\StockAjusteDetalle $ajusteDetalles
     */
    public function removeAjusteDetalle(\AppBundle\Entity\StockAjusteDetalle $ajusteDetalles)
    {
        $this->ajusteDetalles->removeElement($ajusteDetalles);
    }

    /**
     * Get ajusteDetalles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAjusteDetalles()
    {
        return $this->ajusteDetalles;
    }
 

    /**
     * Add despachoDetalles
     *
     * @param \AppBundle\Entity\DespachoDetalle $despachoDetalles
     * @return LoteProducto
     */
    public function addDespachoDetalle(\AppBundle\Entity\DespachoDetalle $despachoDetalles)
    {
        $this->despachoDetalles[] = $despachoDetalles;
        return $this;
    }

    /**
     * Remove despachoDetalles
     *
     * @param \AppBundle\Entity\DespachoDetalle $despachoDetalles
     */
    public function removeDespachoDetalle(\AppBundle\Entity\DespachoDetalle $despachoDetalles)
    {
        $this->despachoDetalles->removeElement($despachoDetalles);
    }

    /**
     * Get despachoDetalles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDespachoDetalles()
    {
        return $this->despachoDetalles;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return LoteProducto
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean 
     */
    public function getActivo()
    {
        return $this->activo;
    }
    
    public function getIngreso(){
        $ingreso = 'Ingreso manual';
        //if( count($this->getPedidoDetalle())>0 ){
        if( count($this->getCompraPedidoDetalles())>0 ){
            //ingreso por compra
            $ingreso = 'Compra N°'.$this->getCompraPedidoDetalles()[0]->getPedido()->getNroPedido();
            
        }elseif( count($this->getAjusteDetalles() )>0 ){
            // ingreso x ajuste
            foreach ($this->getAjusteDetalles() as $ajuste) {
                if($ajuste->getSigno()=='+'){
                    $ingreso = 'Ajuste del '.$ajuste->getStockAjuste()->getFecha()->format('d-m-Y');
                }
            }
        }else{
            // ingreso manual
            $ingreso = 'Ingreso manual';
        }
        return $ingreso;
    }
    
    public function tieneSalidas(){
        $tiene = false;
        if( count($this->getAjusteDetalles() )>0 ){
            // salida x ajuste
            foreach ($this->getAjusteDetalles() as $ajuste) {
                if($ajuste->getSigno()=='-'){
                    return true;
                }
            }
        }
        if( count($this->getDespachoDetalles() )>0 ){            
            // salida por despacho
            return true;
        }
        return $tiene;
    }

    public function __toString() {
        return 'N° '. $this->nroLote. ' - Vto ' . $this->fechaVencimiento->format('d/m/Y');
    } 

    public function enStock(){
        return ( $this->despachoDetalle == NULL )? TRUE : FALSE ;
    }
    
    public function vencido(){
        $hoy = new \DateTime(); 
        if ( $hoy->format('Ymd') > $this->fechaVencimiento->format('Ymd') )
            return true;
        return false;
    }
    

    /**
     * Add compraPedidoDetalles
     *
     * @param \ComprasBundle\Entity\PedidoDetalle $compraPedidoDetalles
     * @return LoteProducto
     */
    public function addCompraPedidoDetalle(\ComprasBundle\Entity\PedidoDetalle $compraPedidoDetalles)
    {
        $this->compraPedidoDetalles[] = $compraPedidoDetalles;

        return $this;
    }

    /**
     * Remove compraPedidoDetalles
     *
     * @param \ComprasBundle\Entity\PedidoDetalle $compraPedidoDetalles
     */
    public function removeCompraPedidoDetalle(\ComprasBundle\Entity\PedidoDetalle $compraPedidoDetalles)
    {
        $this->compraPedidoDetalles->removeElement($compraPedidoDetalles);
    }

    /**
     * Get compraPedidoDetalles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompraPedidoDetalles()
    {
        return $this->compraPedidoDetalles;
    }
}