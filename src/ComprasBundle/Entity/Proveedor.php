<?php

namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ConfigBundle\Controller\UtilsController;

/**
 * ComprasBundle\Entity\Proveedor
 *
 * @ORM\Table(name="proveedor")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\ProveedorRepository")
 */
class Proveedor {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $nombre;

    /**
     * @var string $cuenta
     * @ORM\Column(name="cuenta", type="string", length=13, nullable=true)
     */
    protected $cuenta;

    /**
     * @var string $cuit
     * @ORM\Column(name="cuit", type="string", length=13, nullable=true)
     */
    protected $cuit;

    /**
     * @var string $iibb
     * @ORM\Column(name="iibb", type="string", length=13, nullable=true)
     */
    protected $iibb;

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
     * @var string $email
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
     * @ORM\JoinColumn(name="localidad_id", referencedColumnName="id")
     */
    protected $localidad;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="categoria_iva_id", referencedColumnName="id")
     * */
    protected $categoria_iva;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="categoria_dgr_id", referencedColumnName="id")
     * */
    protected $categoria_dgr;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="condicion_venta_id", referencedColumnName="id")
     * */
    protected $condicion_venta;

    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true;

    /**
     * @var integer $saldoInicial
     * @ORM\Column(name="saldo_inicial", type="decimal", scale=3, nullable=true )
     */
    protected $saldoInicial;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Producto", mappedBy="proveedor")
     */
    protected $productos;

    /**
     * @ORM\OneToMany(targetEntity="ComprasBundle\Entity\Factura", mappedBy="proveedor")
     */
    protected $facturasCompra;

    /**
     * @ORM\OneToMany(targetEntity="ComprasBundle\Entity\PagoProveedor", mappedBy="proveedor")
     */
    protected $pagos;

    /**
     * @ORM\OneToMany(targetEntity="ComprasBundle\Entity\NotaDebCred", mappedBy="proveedor")
     */
    protected $notasDebCred;

    /**
     * @ORM\OneToMany(targetEntity="ComprasBundle\Entity\Pedido", mappedBy="proveedor")
     */
    protected $pedidos;

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
    private $saldoxFechas;

    public function getSaldoxFechas() {
        return $this->saldoxFechas;
    }

    public function setSaldoxFechas($saldoxFechas) {
        $this->saldoxFechas = $saldoxFechas;

        return $this;
    }

    public function __toString() {
        return $this->nombre;
    }

    public function getText() {
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
     * @return Proveedor
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
     * Set cuit
     *
     * @param string $cuit
     * @return Proveedor
     */
    public function setCuit($cuit) {
        $this->cuit = $cuit;

        return $this;
    }

    /**
     * Get cuit
     *
     * @return string
     */
    public function getCuit() {
        return $this->cuit;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return Proveedor
     */
    public function setDireccion($direccion) {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string
     */
    public function getDireccion() {
        return $this->direccion;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return Proveedor
     */
    public function setTelefono($telefono) {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono() {
        return $this->telefono;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Proveedor
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Proveedor
     */
    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones() {
        return $this->observaciones;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return Proveedor
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
     * Set created
     *
     * @param \DateTime $created
     * @return Proveedor
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
     * Set localidad
     *
     * @param \ConfigBundle\Entity\Localidad $localidad
     * @return Proveedor
     */
    public function setLocalidad(\ConfigBundle\Entity\Localidad $localidad = null) {
        $this->localidad = $localidad;

        return $this;
    }

    /**
     * Get localidad
     *
     * @return \ConfigBundle\Entity\Localidad
     */
    public function getLocalidad() {
        return $this->localidad;
    }

    /**
     * Set categoria_iva
     *
     * @param \ConfigBundle\Entity\Parametro $categoriaIva
     * @return Proveedor
     */
    public function setCategoriaIva(\ConfigBundle\Entity\Parametro $categoriaIva = null) {
        $this->categoria_iva = $categoriaIva;

        return $this;
    }

    /**
     * Get categoria_iva
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getCategoriaIva() {
        return $this->categoria_iva;
    }

    /**
     * Set categoria_dgr
     *
     * @param \ConfigBundle\Entity\Parametro $categoriaDgr
     * @return Proveedor
     */
    public function setCategoriaDgr(\ConfigBundle\Entity\Parametro $categoriaDgr = null) {
        $this->categoria_dgr = $categoriaDgr;

        return $this;
    }

    /**
     * Get categoria_dgr
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getCategoriaDgr() {
        return $this->categoria_dgr;
    }

    /**
     * Set condicion_venta
     *
     * @param \ConfigBundle\Entity\Parametro $condicionVenta
     * @return Proveedor
     */
    public function setCondicionVenta(\ConfigBundle\Entity\Parametro $condicionVenta = null) {
        $this->condicion_venta = $condicionVenta;

        return $this;
    }

    /**
     * Get condicion_venta
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getCondicionVenta() {
        return $this->condicion_venta;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Proveedor
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
     * Constructor
     */
    public function __construct() {
        $this->productos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activo = true;
        $this->saldoInicial = 0;
    }

    /**
     * Add productos
     *
     * @param \AppBundle\Entity\Producto $productos
     * @return Proveedor
     */
    public function addProducto(\AppBundle\Entity\Producto $productos) {
        $this->productos[] = $productos;

        return $this;
    }

    /**
     * Remove productos
     *
     * @param \AppBundle\Entity\Producto $productos
     */
    public function removeProducto(\AppBundle\Entity\Producto $productos) {
        $this->productos->removeElement($productos);
    }

    /**
     * Get productos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductos() {
        return $this->productos;
    }

    /**
     * Add facturasCompra
     *
     * @param \ComprasBundle\Entity\Factura $facturasCompra
     * @return Proveedor
     */
    public function addFacturasCompra(\ComprasBundle\Entity\Factura $facturasCompra) {
        $this->facturasCompra[] = $facturasCompra;

        return $this;
    }

    /**
     * Remove facturasCompra
     *
     * @param \ComprasBundle\Entity\Factura $facturasCompra
     */
    public function removeFacturasCompra(\ComprasBundle\Entity\Factura $facturasCompra) {
        $this->facturasCompra->removeElement($facturasCompra);
    }

    /**
     * Get facturasCompra
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFacturasCompra() {
        return $this->facturasCompra;
    }

    /**
     * Get notasDebCred
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotasDebCred() {
        return $this->notasDebCred;
    }

    /**
     * Add pagos
     * @param \ComprasBundle\Entity\PagoProveedor $pagos
     * @return Proveedor
     */
    public function addPago(\ComprasBundle\Entity\PagoProveedor $pagos) {
        $this->pagos[] = $pagos;
        return $this;
    }

    /**
     * Remove pagos
     *
     * @param \ComprasBundle\Entity\PagoProveedor $pagos
     */
    public function removePago(\ComprasBundle\Entity\PagoProveedor $pagos) {
        $this->pagos->removeElement($pagos);
    }

    /**
     * Get pagos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPagos() {
        return $this->pagos;
    }

    public function getSaldo() {
        $facturas = $this->facturasCompra;
        $pagos = $this->pagos;
        $notaDebCred = $this->notasDebCred;
        $saldo = $this->saldoInicial;
        foreach ($facturas as $fact) {
            if (!in_array($fact->getEstado(), ['CANCELADO', 'ANULADO']))
                $saldo += $fact->getTotal();
        }
        foreach ($notaDebCred as $cre) {
            if ($cre->getEstado() == 'ACREDITADO')
                $saldo -= $cre->getTotal();
        }
        foreach ($pagos as $pag) {
            $saldo -= $pag->getTotal();
        }
        return $saldo;
    }

    public function getSaldoPorFechas($desde = NULL, $hasta = NULL) {
        $facturas = $this->facturasCompra;
        $pagos = $this->pagos;
        $notaDebCred = $this->notasDebCred;
        $saldo = $this->saldoInicial;
        $fechaDesde = UtilsController::toAnsiDate($desde);
        $fechaHasta = UtilsController::toAnsiDate($hasta);

        foreach ($facturas as $fact) {
            if (in_array($fact->getEstado(), ['CANCELADO', 'ANULADO'])) {
                continue;
            }
            if ($fechaDesde && $fechaHasta) {
                if ($fact->getFechaFactura()->format('Y-m-d') < $fechaDesde || $fact->getFechaFactura()->format('Y-m-d') > $fechaHasta) {
                    continue;
                }
            }
            $saldo += $fact->getTotal();
        }
        foreach ($notaDebCred as $cre) {
            if (!$cre->getEstado() == 'ACREDITADO') {
                continue;
            }
            if ($fechaDesde && $fechaHasta) {
                if ($cre->getFecha()->format('Y-m-d') < $fechaDesde || $cre->getFecha()->format('Y-m-d') > $fechaHasta) {
                    continue;
                }
            }
            $saldo -= $cre->getTotal();
        }
        foreach ($pagos as $pag) {
            if ($fechaDesde && $fechaHasta) {
                if ($pag->getFecha()->format('Y-m-d') < $fechaDesde || $pag->getFecha()->format('Y-m-d') > $fechaHasta) {
                    continue;
                }
            }
            $saldo -= $pag->getTotal();
        }
        return $saldo;
    }

    /**
     * Set saldoInicial
     *
     * @param string $saldoInicial
     * @return Proveedor
     */
    public function setSaldoInicial($saldoInicial) {
        $this->saldoInicial = $saldoInicial;

        return $this;
    }

    /**
     * Get saldoInicial
     *
     * @return string
     */
    public function getSaldoInicial() {
        return $this->saldoInicial;
    }

    /**
     * Set cuenta
     *
     * @param string $cuenta
     * @return Proveedor
     */
    public function setCuenta($cuenta) {
        $this->cuenta = $cuenta;

        return $this;
    }

    /**
     * Get cuenta
     *
     * @return string
     */
    public function getCuenta() {
        return $this->cuenta;
    }

    /**
     * Add notasDebCred
     *
     * @param \ComprasBundle\Entity\NotaDebCred $notasDebCred
     * @return Proveedor
     */
    public function addNotasDebCred(\ComprasBundle\Entity\NotaDebCred $notasDebCred) {
        $this->notasDebCred[] = $notasDebCred;

        return $this;
    }

    /**
     * Remove notasDebCred
     *
     * @param \ComprasBundle\Entity\NotaDebCred $notasDebCred
     */
    public function removeNotasDebCred(\ComprasBundle\Entity\NotaDebCred $notasDebCred) {
        $this->notasDebCred->removeElement($notasDebCred);
    }

    /**
     * Set iibb
     *
     * @param string $iibb
     * @return Proveedor
     */
    public function setIibb($iibb) {
        $this->iibb = $iibb;

        return $this;
    }

    /**
     * Get iibb
     *
     * @return string
     */
    public function getIibb() {
        return $this->iibb;
    }

    /**
     * Add pedidos
     *
     * @param \ComprasBundle\Entity\Pedido $pedidos
     * @return Proveedor
     */
    public function addPedido(\ComprasBundle\Entity\Pedido $pedidos) {
        $this->pedidos[] = $pedidos;

        return $this;
    }

    /**
     * Remove pedidos
     *
     * @param \ComprasBundle\Entity\Pedido $pedidos
     */
    public function removePedido(\ComprasBundle\Entity\Pedido $pedidos) {
        $this->pedidos->removeElement($pedidos);
    }

    /**
     * Get pedidos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPedidos() {
        return $this->pedidos;
    }

}