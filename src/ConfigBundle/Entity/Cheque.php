<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\Cheque
 * @ORM\Table(name="cheque")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\ChequeRepository")
 */
class Cheque {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string $tipo
     * @ORM\Column(name="tipo", type="string", nullable=false)
     */
    // P:propio - T:tercero
    protected $tipo = 'T';
    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string", nullable=false)
     */
    // C:cartera - U:usado - D:devuelto
    protected $estado = 'C';
    /**
     * @var string $nroCheque
     * @ORM\Column(name="nro_cheque", type="string", nullable=false)
     */
    protected $nroCheque;
    /**
     * @var integer $prefijoNro
     * @ORM\Column(name="prefijo_nro", type="string", length=3, nullable=true)
     */
    protected $prefijoNro;
    /**
     * @var integer $chequeNro
     * @ORM\Column(name="cheque_nro", type="string", length=6, nullable=true)
     */
    protected $chequeNro;
    /**
     * @var date $tomado
     * @ORM\Column(name="tomado", type="date", nullable=true)
     */
    protected $tomado;

    /**
     * @var string $dador
     * @ORM\Column(name="dador", type="string", nullable=false)
     */
    protected $dador;
    /**
     * @var string $telefono
     * @ORM\Column(name="telefono", type="string", nullable=true)
     */
    protected $telefono;
    /**
     * @var date $fecha
     * @ORM\Column(name="fecha", type="date", nullable=true)
     */
    protected $fecha;
    /**
     * @var string $valor
     * @ORM\Column(name="valor", type="decimal", precision=15, scale=3, nullable=false)
     */
    protected $valor;

    /**
     * @ORM\Column(name="devuelto", type="boolean", nullable=true)
     */
    protected $devuelto = false;
    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

    /**
     * @ORM\Column(name="usado", type="boolean", nullable=true)
     */
    protected $usado = false;

     /**
     *@ORM\ManyToOne(targetEntity="ConfigBundle\Entity\TitularCheque", inversedBy="cheques")
     *@ORM\JoinColumn(name="titular_cheque_id", referencedColumnName="id")
     */
    protected $titularCheque;

    /**
     *@ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Banco", inversedBy="cheques")
     *@ORM\JoinColumn(name="banco_id", referencedColumnName="id")
     */
    protected $banco;

    /**
     * @var string $sucursal
     * @ORM\Column(name="sucursal", type="string", nullable=true)
     */
    protected $sucursal;

    /**
    * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
    * @ORM\JoinColumn(name="localidad_id", referencedColumnName="id")
    */
    protected $localidad;

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
     *@ORM\ManyToOne(targetEntity="ComprasBundle\Entity\PagoProveedor")
     *@ORM\JoinColumn(name="compras_pago_proveedor_id", referencedColumnName="id")
     */
    private $pagoProveedor;
     /**
     *@ORM\ManyToOne(targetEntity="VentasBundle\Entity\PagoCliente", inversedBy="chequesRecibidos")
     *@ORM\JoinColumn(name="ventas_pago_cliente_id", referencedColumnName="id")
     */
    private $pagoCliente;

    /*
     * Cheque toString
     */
    public function __toString(){
        return $this->nroCheque;
    }

    /**
     * Get nroInterno
     * @return string
     */
    public function getNroInterno()
    {
        return $this->prefijoNro.'-'.$this->chequeNro;
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
     * Set id
     *
     * @param string $id
     * @return Cheque
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return Cheque
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
     * Set estado
     *
     * @param string $estado
     * @return Cheque
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set nroCheque
     *
     * @param string $nroCheque
     * @return Cheque
     */
    public function setNroCheque($nroCheque)
    {
        $this->nroCheque = $nroCheque;

        return $this;
    }

    /**
     * Get nroCheque
     *
     * @return string
     */
    public function getNroCheque()
    {
        return $this->nroCheque;
    }

    /**
     * Set prefijoNro
     *
     * @param string $prefijoNro
     * @return Cheque
     */
    public function setPrefijoNro($prefijoNro)
    {
        $this->prefijoNro = $prefijoNro;

        return $this;
    }

    /**
     * Get prefijoNro
     *
     * @return string
     */
    public function getPrefijoNro()
    {
        return $this->prefijoNro;
    }

    /**
     * Set chequeNro
     *
     * @param string $chequeNro
     * @return Cheque
     */
    public function setChequeNro($chequeNro)
    {
        $this->chequeNro = $chequeNro;

        return $this;
    }

    /**
     * Get chequeNro
     *
     * @return string
     */
    public function getChequeNro()
    {
        return $this->chequeNro;
    }

    /**
     * Set tomado
     *
     * @param \DateTime $tomado
     * @return Cheque
     */
    public function setTomado($tomado)
    {
        $this->tomado = $tomado;

        return $this;
    }

    /**
     * Get tomado
     *
     * @return \DateTime
     */
    public function getTomado()
    {
        return $this->tomado;
    }

    /**
     * Set dador
     *
     * @param string $dador
     * @return Cheque
     */
    public function setDador($dador)
    {
        $this->dador = $dador;

        return $this;
    }

    /**
     * Get dador
     *
     * @return string
     */
    public function getDador()
    {
        return $this->dador;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return Cheque
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Cheque
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
     * Set valor
     *
     * @param string $valor
     * @return Cheque
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set devuelto
     *
     * @param boolean $devuelto
     * @return Cheque
     */
    public function setDevuelto($devuelto)
    {
        $this->devuelto = $devuelto;

        return $this;
    }

    /**
     * Get devuelto
     *
     * @return boolean
     */
    public function getDevuelto()
    {
        return $this->devuelto;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Cheque
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
     * Set usado
     *
     * @param boolean $usado
     * @return Cheque
     */
    public function setUsado($usado)
    {
        $this->usado = $usado;

        return $this;
    }

    /**
     * Get usado
     *
     * @return boolean
     */
    public function getUsado()
    {
        return $this->usado;
    }

    /**
     * Set sucursal
     *
     * @param string $sucursal
     * @return Cheque
     */
    public function setSucursal($sucursal)
    {
        $this->sucursal = $sucursal;

        return $this;
    }

    /**
     * Get sucursal
     *
     * @return string
     */
    public function getSucursal()
    {
        return $this->sucursal;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Cheque
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
     * Set titularCheque
     *
     * @param \ConfigBundle\Entity\TitularCheque $titularCheque
     * @return Cheque
     */
    public function setTitularCheque(\ConfigBundle\Entity\TitularCheque $titularCheque = null)
    {
        $this->titularCheque = $titularCheque;

        return $this;
    }

    /**
     * Get titularCheque
     *
     * @return \ConfigBundle\Entity\TitularCheque
     */
    public function getTitularCheque()
    {
        return $this->titularCheque;
    }

    /**
     * Set banco
     *
     * @param \ConfigBundle\Entity\Banco $banco
     * @return Cheque
     */
    public function setBanco(\ConfigBundle\Entity\Banco $banco = null)
    {
        $this->banco = $banco;

        return $this;
    }

    /**
     * Get banco
     *
     * @return \ConfigBundle\Entity\Banco
     */
    public function getBanco()
    {
        return $this->banco;
    }

    /**
     * Set localidad
     *
     * @param \ConfigBundle\Entity\Localidad $localidad
     * @return Cheque
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
     * @return Cheque
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
     * Set pagoProveedor
     *
     * @param \ComprasBundle\Entity\PagoProveedor $pagoProveedor
     * @return Cheque
     */
    public function setPagoProveedor(\ComprasBundle\Entity\PagoProveedor $pagoProveedor = null)
    {
        $this->pagoProveedor = $pagoProveedor;

        return $this;
    }

    /**
     * Get pagoProveedor
     *
     * @return ComprasBundle\Entity\PagoProveedor
     */
    public function getPagoProveedor()
    {
        return $this->pagoProveedor;
    }
    /**
     * Set pagoCliente
     *
     * @param VentasBundle\Entity\PagoCliente $pagoCliente
     * @return Cheque
     */
    public function setPagoCliente(\VentasBundle\Entity\PagoCliente $pagoCliente = null)
    {
        $this->pagoCliente = $pagoCliente;

        return $this;
    }

    /**
     * Get pagoCliente
     *
     * @return \VentasBundle\Entity\PagoCliente
     */
    public function getPagoCliente()
    {
        return $this->pagoCliente;
    }

}