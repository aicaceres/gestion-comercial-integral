<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * AppBundle\Entity\Producto
 *
 * @ORM\Table(name="producto")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductoRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Producto
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @var string $codigo
     * @ORM\Column(name="codigo", type="string", length=6, nullable=false)
     * @Assert\NotBlank()
     */
    protected $codigo;
    
    /**
     * @var string $codigoBarra
     * @ORM\Column(name="codigo_barra", type="string", length=16, nullable=true)
     */
    protected $codigoBarra;
    
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $nombre;
    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="string", nullable=true)
     */
    protected $descripcion;
    /**
     * @var string $costo
     * @ORM\Column(name="costo", type="decimal", scale=3,  nullable=false)
     */
    protected $costo;
    /**
     * @var string $iva
     * @ORM\Column(name="iva", type="decimal", scale=2,  nullable=false)
     */
    protected $iva = 21;
    /**
     * @var string $stock_minimo
     * @ORM\Column(name="stock_minimo", type="decimal", scale=3,  nullable=true)
     */
    protected $stockMinimo;  
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="rubro_id", referencedColumnName="id", onDelete="SET NULL")
     **/  
    protected $rubro;    
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="unidad_medida_id", referencedColumnName="id")
     **/  
    protected $unidadMedida;    
    /**
     *@ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Proveedor", inversedBy="productos")
     *@ORM\JoinColumn(name="proveedor_id", referencedColumnName="id") 
     */
    protected $proveedor;     
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Precio", mappedBy="producto",cascade={"persist"})
     */
    protected $precios;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Stock", mappedBy="producto",cascade={"persist"})
     */
    protected $stock;
    
    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;  
    /**
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    protected $activo = true;    
    
    /**
     * @ORM\Column(name="facturable", type="boolean", nullable=true)
     */
    protected $facturable = false;    
    
    /**
     * @ORM\Column(name="bulto", type="boolean", nullable=true)
     */
    protected $bulto = false;   
   
     /**
     * @var integer $cantidadxBulto
     * @ORM\Column(name="cantidad_x_bulto", type="decimal", scale=2, nullable=true )
     */
    protected $cantidadxBulto;    
    
     /**
     * @ORM\OneToMany(targetEntity="ComprasBundle\Entity\LoteProducto", mappedBy="producto",cascade={"persist", "remove"})
     */
    protected $lotes;    
    
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
        return $this->codigo.' - '.$this->nombre;
    } 
    public function getCodigoNombreBarcode() {
        return $this->codigo.' - '.$this->nombre.' - '.$this->codigoBarra;
    } 
    public function getCodigoNombre(){
        return $this->codigo.' - '.$this->nombre;
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
     * Set codigo
     *
     * @param string $codigo
     * @return Producto
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Producto
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Producto
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set alto
     *
     * @param integer $alto
     * @return Producto
     */
    public function setAlto($alto)
    {
        $this->alto = $alto;
    
        return $this;
    }

    /**
     * Get alto
     *
     * @return integer 
     */
    public function getAlto()
    {
        return $this->alto;
    }

    /**
     * Set ancho
     *
     * @param integer $ancho
     * @return Producto
     */
    public function setAncho($ancho)
    {
        $this->ancho = $ancho;
    
        return $this;
    }

    /**
     * Get ancho
     *
     * @return integer 
     */
    public function getAncho()
    {
        return $this->ancho;
    }

    /**
     * Set largo
     *
     * @param integer $largo
     * @return Producto
     */
    public function setLargo($largo)
    {
        $this->largo = $largo;
    
        return $this;
    }

    /**
     * Get largo
     *
     * @return integer 
     */
    public function getLargo()
    {
        return $this->largo;
    }

    /**
     * Set densidad
     *
     * @param string $densidad
     * @return Producto
     */
    public function setDensidad($densidad)
    {
        $this->densidad = $densidad;
    
        return $this;
    }

    /**
     * Get densidad
     *
     * @return string 
     */
    public function getDensidad()
    {
        return $this->densidad;
    }

    /**
     * Set stockMinimo
     * @param string $stockMinimo
     * @return Producto
     */
    public function setStockMinimo($stockMinimo)
    {
        $this->stockMinimo = $stockMinimo;
        return $this;
    }

    /**
     * Get stock_minimo
     * @return string 
     */
    public function getStockMinimo()
    {
        return $this->stockMinimo;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Producto
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;    
        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return Producto
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
    
    /**
     * Set facturable
     *
     * @param boolean $facturable
     * @return Producto
     */
    public function setFacturable($facturable)
    {
        $this->facturable = $facturable;
    
        return $this;
    }

    /**
     * Get facturable
     *
     * @return boolean 
     */
    public function getFacturable()
    {
        return $this->facturable;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Producto
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
     * @return Producto
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
     * Set rubro
     *
     * @param \ConfigBundle\Entity\Parametro $rubro
     * @return Producto
     */
    public function setRubro(\ConfigBundle\Entity\Parametro $rubro = null)
    {
        $this->rubro = $rubro;
    
        return $this;
    }

    /**
     * Get rubro
     *
     * @return \ConfigBundle\Entity\Parametro 
     */
    public function getRubro()
    {
        return $this->rubro;
    }

    /**
     * Set proveedor
     *
     * @param \ComprasBundle\Entity\Proveedor $proveedor
     * @return Producto
     */
    public function setProveedor(\ComprasBundle\Entity\Proveedor $proveedor = null)
    {
        $this->proveedor = $proveedor;
    
        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \ComprasBundle\Entity\Proveedor 
     */
    public function getProveedor()
    {
        return $this->proveedor;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Producto
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
     * @return Producto
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
 * MANEJO DE FOTOS
 */    
    /**
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * Set path
     * @param string $path
     * @return Producto
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // la ruta absoluta del directorio donde se deben
        // guardar los archivos cargados
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // se deshace del __DIR__ para no meter la pata
        // al mostrar el documento/imagen cargada en la vista.
        return 'uploads';
    }  
       
    /**
     * @Assert\File(maxSize="150k")
     */
    private $file;
    private $filenameForRemove;
    /**
     * Get file.
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    private $temp;

    /**
     * Sets file.
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            // haz lo que quieras para generar un nombre único
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->getFile()->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        // si hay un error al mover el archivo, move() automáticamente
        // envía una excepción. This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }
    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove()
    {
        $this->filenameForRemove = $this->getAbsolutePath();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($this->filenameForRemove) {
            unlink($this->filenameForRemove);
        }
    }
   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->precios = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add precios
     *
     * @param \AppBundle\Entity\Precio $precios
     * @return Producto
     */
    public function addPrecio(\AppBundle\Entity\Precio $precios)
    {
        $precios->setProducto($this);
        $this->precios[] = $precios;
        return $this;
    }

    /**
     * Remove precios
     *
     * @param \AppBundle\Entity\Precio $precios
     */
    public function removePrecio(\AppBundle\Entity\Precio $precios)
    {
        $this->precios->removeElement($precios);
    }

    /**
     * Get precios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPrecios()
    {
        return $this->precios;
    }

    /**
     * Set iva
     *
     * @param string $iva
     * @return Producto
     */
    public function setIva($iva)
    {
        $this->iva = $iva;
    
        return $this;
    }

    /**
     * Get iva
     *
     * @return string 
     */
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * Add stock
     *
     * @param \AppBundle\Entity\Stock $stock
     * @return Producto
     */
    public function addStock(\AppBundle\Entity\Stock $stock)
    {
        $stock->setProducto($this);
        $this->stock[] = $stock;   
        return $this;
    }

    /**
     * Remove stock
     *
     * @param \AppBundle\Entity\Stock $stock
     */
    public function removeStock(\AppBundle\Entity\Stock $stock)
    {
        $this->stock->removeElement($stock);
    }

    /**
     * Get stock
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStock()
    {
        return $this->stock;
    }
        
    /**
     * Stock actual
     */
    public function getStockActualxDeposito($id){
        $actual = 0;
        foreach ($this->getStock() as $stock) {
            if( $stock->getDeposito()->getId()==$id )
                $actual = $actual + $stock->getCantidad();
        }
        return number_format((float)$actual, 3, '.', '');
    }

    /**
     * Valorizado actual
     */
   /* public function getValorizadoActual(){
        $actual = $this->getStockActual() * $this->costo;
        foreach ($this->getStock() as $value) {
            $actual = $actual + $value->getValorizado();
        }
        return $actual;
    }*/

    /**
     * Set codigoBarra
     *
     * @param string $codigoBarra
     * @return Producto
     */
    public function setCodigoBarra($codigoBarra)
    {
        $this->codigoBarra = $codigoBarra;  
        return $this;
    }

    /**
     * Get codigoBarra
     *
     * @return string 
     */
    public function getCodigoBarra()
    {
        return $this->codigoBarra;
    }

    /**
     * Set costo
     *
     * @param string $costo
     * @return Producto
     */
    public function setCosto($costo)
    {
        $this->costo = $costo;    
        return $this;
    }

    /**
     * Get costo
     *
     * @return string 
     */
    public function getCosto()
    {
        return $this->costo;
    }

    /**
     * Set unidadMedida
     *
     * @param \ConfigBundle\Entity\Parametro $unidadMedida
     * @return Producto
     */
    public function setUnidadMedida(\ConfigBundle\Entity\Parametro $unidadMedida = null)
    {
        $this->unidadMedida = $unidadMedida;

        return $this;
    }

    /**
     * Get unidadMedida
     *
     * @return \ConfigBundle\Entity\Parametro 
     */
    public function getUnidadMedida()
    {
        return $this->unidadMedida;
    }

    /**
     * Set bulto
     *
     * @param boolean $bulto
     * @return Producto
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
     * @param string $cantidadxBulto
     * @return Producto
     */
    public function setCantidadxBulto($cantidadxBulto)
    {
        $this->cantidadxBulto = $cantidadxBulto;

        return $this;
    }

    /**
     * Get cantidadxBulto
     *
     * @return string 
     */
    public function getCantidadxBulto()
    {
        return $this->cantidadxBulto;
    }

    /**
     * Add lotes
     *
     * @param \ComprasBundle\Entity\LoteProducto $lotes
     * @return Producto
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