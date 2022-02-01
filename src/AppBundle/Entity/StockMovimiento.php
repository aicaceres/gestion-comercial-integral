<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * AppBundle\Entity\StockMovimiento
 *
 * @ORM\Table(name="stock_movimiento")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 */
class StockMovimiento
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var date $fecha
     * @ORM\Column(name="fecha", type="date", nullable=false)
     */
    private $fecha;     
    
    /**
     * @var string $tipo
     * @ORM\Column(name="tipo", type="string", nullable=false)
     */
    // Nombre de la tabla de donde se guarda el id de movimiento.
    private $tipo;     
    
    /**
     * @var integer $movimiento
     * @ORM\Column(name="movimiento", type="integer")
     */
    private $movimiento; 
    
    /**
     * @var string $signo
     * @ORM\Column(name="signo", type="string")
     */
    private $signo='+'; 
    
     /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=3 )
     */
    protected $cantidad;
    
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

    public function getTipoMovimiento(){
        switch ($this->getTipo()) {
            case 'AJUSTE':
                return 'Ajuste de Stock';
            case 'Despacho':
                return 'Despacho Interdepósito';
            case 'compras_pedido':
                return 'Pedido de Compras';
            case 'compras_factura':
                return 'Factura de Compras';
            case 'COMPRAS_NOTADEBCRED':
                return 'Nota débito/crédito de Compras';
            case 'ventas_venta':
                return 'Venta';
            default:
                return NULL;
        }
    }
    public function getEntidadMovimiento(){
        switch ($this->getTipo()) {
            case 'AJUSTE':
                return 'AppBundle:StockAjuste';
            case 'Despacho':
                return 'AppBundle:Despacho';
            case 'compras_pedido':
                return 'ComprasBundle:Pedido';
            case 'compras_factura':
                return 'ComprasBundle:Factura';
            case 'COMPRAS_NOTADEBCRED':
                return 'ComprasBundle:NotaDebCred';
            default:
                return NULL;
        }
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return StockMovimiento
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return StockMovimiento
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    
        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set movimiento
     *
     * @param integer $movimiento
     * @return StockMovimiento
     */
    public function setMovimiento($movimiento)
    {
        $this->movimiento = $movimiento;
    
        return $this;
    }

    /**
     * Get movimiento
     *
     * @return integer 
     */
    public function getMovimiento()
    {
        return $this->movimiento;
    }

    /**
     * Set signo
     *
     * @param string $signo
     * @return StockMovimiento
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
     * @return StockMovimiento
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
     * Set created
     *
     * @param \DateTime $created
     * @return StockMovimiento
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
     * Set deposito
     *
     * @param \AppBundle\Entity\Deposito $deposito
     * @return StockMovimiento
     */
    public function setDeposito(\AppBundle\Entity\Deposito $deposito = null)
    {
        $this->deposito = $deposito;
    
        return $this;
    }

    /**
     * Get deposito
     *
     * @return \AppBundle\Entity\Deposito 
     */
    public function getDeposito()
    {
        return $this->deposito;
    }

    /**
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return StockMovimiento
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
     * @return StockMovimiento
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
}