<?php
namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\Cliente
 * @ORM\Table(name="cliente")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\ClienteRepository")
 */
class Cliente
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     * @Assert\NotBlank()
     */    
    protected $nombre;
    /**
     * @var string $dni
     * @ORM\Column(name="dni", type="string", length=8, nullable=false)
     */
    protected $dni;
    /**
     * @var string $cuit
     * @ORM\Column(name="cuit", type="string", length=13, nullable=true)
     */
    protected $cuit;
    /**
     * @var string $direccion
     * @ORM\Column(name="direccion", type="string", nullable=true)
     */
    protected $direccion;
    /**
     * @var string $telefono
     * @ORM\Column(name="telefono", type="string", nullable=true)
     */
    protected $telefono;
    /**
     * @var string $celular
     * @ORM\Column(name="celular", type="string", nullable=true)
     */
    protected $celular;
    /**
     * @var string $email
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;
    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;      
     /**
     * @var integer $saldoInicial
     * @ORM\Column(name="saldo_inicial", type="decimal", scale=3, nullable=true )
     */
    protected $saldoInicial; 
    /**
     * @ORM\Column(name="consumidor_final", type="boolean",nullable=true)
     */
    protected $consumidorFinal = false;        
    /**
     * @ORM\Column(name="activo", type="boolean",nullable=true)
     */
    protected $activo = true;    

    
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="categoria_iva_id", referencedColumnName="id")
     **/  
    protected $categoria_iva;
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="categoria_dgr_id", referencedColumnName="id")
     **/  
    protected $categoria_dgr;
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="condicion_venta_id", referencedColumnName="id")
     **/  
    protected $condicion_venta;    

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrecioLista")
     * @ORM\JoinColumn(name="precio_lista_id", referencedColumnName="id")
     */
    protected $precioLista;
    
    /**
    * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
    * @ORM\JoinColumn(name="localidad_id", referencedColumnName="id")
    */
    protected $localidad;
     /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\Factura", mappedBy="cliente")
     */
    protected $facturasVenta;
    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\PagoCliente", mappedBy="cliente")
     */
    protected $pagos;   
  
    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\NotaDebCred", mappedBy="cliente")
     */
    protected $notasDebCredVenta;
 
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
     * Constructor
     */
    public function __construct()
    {
        $this->facturasVenta = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activo = true;
        $this->consumidorFinal = false;
        $this->saldoInicial = 0;
    }
    
    
    public function __toString() {
        return $this->nombre;
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
     * Set nombre
     *
     * @param string $nombre
     * @return Cliente
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
     * Set dni
     *
     * @param string $dni
     * @return Cliente
     */
    public function setDni($dni)
    {
        $this->dni = $dni;
    
        return $this;
    }

    /**
     * Get dni
     *
     * @return string 
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * Set cuit
     *
     * @param string $cuit
     * @return Cliente
     */
    public function setCuit($cuit)
    {
        $this->cuit = $cuit;
    
        return $this;
    }

    /**
     * Get cuit
     *
     * @return string 
     */
    public function getCuit()
    {
        return $this->cuit;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return Cliente
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return Cliente
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    
        return $this;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Cliente
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Cliente
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
     * @return Cliente
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
     * Set created
     *
     * @param \DateTime $created
     * @return Cliente
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
     * @return Cliente
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
     * Set categoria_iva
     *
     * @param \ConfigBundle\Entity\Parametro $categoriaIva
     * @return Cliente
     */
    public function setCategoriaIva(\ConfigBundle\Entity\Parametro $categoriaIva = null)
    {
        $this->categoria_iva = $categoriaIva;
    
        return $this;
    }

    /**
     * Get categoria_iva
     *
     * @return \ConfigBundle\Entity\Parametro 
     */
    public function getCategoriaIva()
    {
        return $this->categoria_iva;
    }

    /**
     * Set categoria_dgr
     *
     * @param \ConfigBundle\Entity\Parametro $categoriaDgr
     * @return Cliente
     */
    public function setCategoriaDgr(\ConfigBundle\Entity\Parametro $categoriaDgr = null)
    {
        $this->categoria_dgr = $categoriaDgr;
    
        return $this;
    }

    /**
     * Get categoria_dgr
     *
     * @return \ConfigBundle\Entity\Parametro 
     */
    public function getCategoriaDgr()
    {
        return $this->categoria_dgr;
    }

    /**
     * Set condicion_venta
     *
     * @param \ConfigBundle\Entity\Parametro $condicionVenta
     * @return Cliente
     */
    public function setCondicionVenta(\ConfigBundle\Entity\Parametro $condicionVenta = null)
    {
        $this->condicion_venta = $condicionVenta;    
        return $this;
    }

    /**
     * Get condicion_venta
     *
     * @return \ConfigBundle\Entity\Parametro 
     */
    public function getCondicionVenta()
    {
        return $this->condicion_venta;
    }

    /**
     * Set localidad
     *
     * @param \ConfigBundle\Entity\Localidad $localidad
     * @return Cliente
     */
    public function setLocalidad(\ConfigBundle\Entity\Localidad $localidad = null)
    {
        $this->localidad = $localidad;    
        return $this;
    }

    /**
     * Get localidad
     *
     * @return \ConfigBundle\Entity\Localidad 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Cliente
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
     * @return Cliente
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
     * Set celular
     *
     * @param string $celular
     * @return Cliente
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;
    
        return $this;
    }

    /**
     * Get celular
     *
     * @return string 
     */
    public function getCelular()
    {
        return $this->celular;
    }

    public function getDomicilioCompleto(){
        return $this->direccion.', '.$this->localidad.', '.$this->getLocalidad()->getProvincia();
    }
    
    public function getSaldo(){
        $facturas = $this->facturasVenta;
        $pagos = $this->pagos;
        $notasDebito = $this->notasDebCredVenta;
        $saldo = $this->saldoInicial;
        foreach ($facturas as $fact) {
            if( !in_array($fact->getEstado(),['ANULADO']) )
                $saldo += $fact->getTotal();
        }
        foreach ($notasDebito as $deb) {
            if($deb->getSigno()=='+' )
                $saldo += $deb->getTotal();
            else
                $saldo -= $deb->getTotal();

        }
        foreach ($pagos as $pag) {
            $saldo -= $pag->getTotal();
        }
        return $saldo;
    }    
 
    public function getFechaUltimaCompra(){
        $facturas = $this->facturasVenta;
        $fecha=null;
        foreach ($facturas as $fact) {
            $fecha = $fact->getFechaFactura()->format('Y-m-d');
        }
        return $fecha;
    }
    
    /**
     * Add pagos
     *
     * @param \VentasBundle\Entity\PagoCliente $pagos
     * @return Cliente
     */
    public function addPago(\VentasBundle\Entity\PagoCliente $pagos)
    {
        $this->pagos[] = $pagos;
    
        return $this;
    }

    /**
     * Remove pagos
     *
     * @param \VentasBundle\Entity\PagoCliente $pagos
     */
    public function removePago(\VentasBundle\Entity\PagoCliente $pagos)
    {
        $this->pagos->removeElement($pagos);
    }

    /**
     * Get pagos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPagos()
    {
        return $this->pagos;
    }

    /**
     * Set saldoInicial
     *
     * @param string $saldoInicial
     * @return Cliente
     */
    public function setSaldoInicial($saldoInicial)
    {
        $this->saldoInicial = $saldoInicial;    
        return $this;
    }

    /**
     * Get saldoInicial
     *
     * @return string 
     */
    public function getSaldoInicial()
    {
        return $this->saldoInicial;
    }

    /**
     * Set consumidorFinal
     *
     * @param boolean $consumidorFinal
     * @return Cliente
     */
    public function setConsumidorFinal($consumidorFinal)
    {
        $this->consumidorFinal = $consumidorFinal;    
        return $this;
    }

    /**
     * Get consumidorFinal
     *
     * @return boolean 
     */
    public function getConsumidorFinal()
    {
        return $this->consumidorFinal;
    }

    /**
     * Set precioLista
     *
     * @param AppBundle\Entity\PrecioLista $precioLista
     * @return Cliente
     */
    public function setPrecioLista(\AppBundle\Entity\PrecioLista $precioLista = null)
    {
        $this->precioLista = $precioLista;
    
        return $this;
    }

    /**
     * Get precioLista
     *
     * @return \AppBundle\Entity\PrecioLista 
     */
    public function getPrecioLista()
    {
        return $this->precioLista;
    }



    /**
     * Add facturasVenta
     *
     * @param \VentasBundle\Entity\Factura $facturasVenta
     * @return Cliente
     */
    public function addFacturasVenta(\VentasBundle\Entity\Factura $facturasVenta)
    {
        $this->facturasVenta[] = $facturasVenta;    
        return $this;
    }

    /**
     * Remove facturasVenta
     *
     * @param \VentasBundle\Entity\Factura $facturasVenta
     */
    public function removeFacturasVenta(\VentasBundle\Entity\Factura $facturasVenta)
    {
        $this->facturasVenta->removeElement($facturasVenta);
    }

    /**
     * Get facturasVenta
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFacturasVenta()
    {
        return $this->facturasVenta;
    }    


    /**
     * Add notasDebCredVenta
     *
     * @param \VentasBundle\Entity\NotaDebCred $notasDebCredVenta
     * @return Cliente
     */
    public function addNotasDebCredVenta(\VentasBundle\Entity\NotaDebCred $notasDebCredVenta)
    {
        $this->notasDebCredVenta[] = $notasDebCredVenta;

        return $this;
    }

    /**
     * Remove notasDebCredVenta
     *
     * @param \VentasBundle\Entity\NotaDebCred $notasDebCredVenta
     */
    public function removeNotasDebCredVenta(\VentasBundle\Entity\NotaDebCred $notasDebCredVenta)
    {
        $this->notasDebCredVenta->removeElement($notasDebCredVenta);
    }

    /**
     * Get notasDebCredVenta
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotasDebCredVenta()
    {
        return $this->notasDebCredVenta;
    }
}
