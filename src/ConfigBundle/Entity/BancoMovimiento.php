<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * ConfigBundle\Entity\BancoMovimiento
 * @ORM\Table(name="banco_movimiento")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\BancoRepository")
 * @Gedmo\Loggable()
 */
class BancoMovimiento {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $nroMovimiento
     * @ORM\Column(name="nro_movimiento", type="string", nullable=false)
     * @Gedmo\Versioned()
     */
    protected $nroMovimiento;
    /**
     * @var date $fechaCarga
     * @ORM\Column(name="fecha_carga", type="date", nullable=true)
     * @Gedmo\Versioned()
     */
    private $fechaCarga;
    /**
     * @var date $fechaAcreditacion
     * @ORM\Column(name="fecha_acreditacion", type="date", nullable=true)
     * @Gedmo\Versioned()
     */
    private $fechaAcreditacion;
    /**
     * @var string $importe
     * @ORM\Column(name="importe", type="decimal", precision=20, scale=2,  nullable=false)
     * @Gedmo\Versioned()
     */
    protected $importe;
    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $observaciones;
    /**
     * @var string $conciliado
     * @ORM\Column(name="conciliado", type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $conciliado = false;

    /* DEPOSITO - CHEQUE - DEBITO - CREDITO - EXTRACCION*/
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\BancoTipoMovimiento")
     * @ORM\JoinColumn(name="banco_tipo_movimiento_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $tipoMovimiento;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Banco", inversedBy="movimientos")
     * @ORM\JoinColumn(name="banco_id", referencedColumnName="id")
     */
    protected $banco;
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\CuentaBancaria", inversedBy="movimientos")
     * @ORM\JoinColumn(name="cuenta_bancaria_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $cuenta;

    /**
     * @ORM\OneToOne(targetEntity="ConfigBundle\Entity\Cheque")
     * @Gedmo\Versioned()
     */
    private $cheque;

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


    public function getImporteMovimiento(){
      return $this->getTipoMovimiento()->getSigno() === '-' ? $this->importe * -1 : $this->importe;
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
     * Set nroMovimiento
     *
     * @param string $nroMovimiento
     * @return BancoMovimiento
     */
    public function setNroMovimiento($nroMovimiento)
    {
        $this->nroMovimiento = $nroMovimiento;

        return $this;
    }

    /**
     * Get nroMovimiento
     *
     * @return string
     */
    public function getNroMovimiento()
    {
        return $this->nroMovimiento;
    }

    /**
     * Set fechaCarga
     *
     * @param \DateTime $fechaCarga
     * @return BancoMovimiento
     */
    public function setFechaCarga($fechaCarga)
    {
        $this->fechaCarga = $fechaCarga;

        return $this;
    }

    /**
     * Get fechaCarga
     *
     * @return \DateTime
     */
    public function getFechaCarga()
    {
        return $this->fechaCarga;
    }

    /**
     * Set fechaAcreditacion
     *
     * @param \DateTime $fechaAcreditacion
     * @return BancoMovimiento
     */
    public function setFechaAcreditacion($fechaAcreditacion)
    {
        $this->fechaAcreditacion = $fechaAcreditacion;

        return $this;
    }

    /**
     * Get fechaAcreditacion
     *
     * @return \DateTime
     */
    public function getFechaAcreditacion()
    {
        return $this->fechaAcreditacion;
    }

    /**
     * Set importe
     *
     * @param string $importe
     * @return BancoMovimiento
     */
    public function setImporte($importe)
    {
        $this->importe = $importe;

        return $this;
    }

    /**
     * Get importe
     *
     * @return string
     */
    public function getImporte()
    {
        return $this->importe;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return BancoMovimiento
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
     * Set conciliado
     *
     * @param boolean $conciliado
     * @return BancoMovimiento
     */
    public function setConciliado($conciliado)
    {
        $this->conciliado = $conciliado;

        return $this;
    }

    /**
     * Get conciliado
     *
     * @return boolean
     */
    public function getConciliado()
    {
        return $this->conciliado;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return BancoMovimiento
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
     * @return BancoMovimiento
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
     * Set tipoMovimiento
     *
     * @param \ConfigBundle\Entity\BancoTipoMovimiento $tipoMovimiento
     * @return BancoMovimiento
     */
    public function setTipoMovimiento(\ConfigBundle\Entity\BancoTipoMovimiento $tipoMovimiento = null)
    {
        $this->tipoMovimiento = $tipoMovimiento;

        return $this;
    }

    /**
     * Get tipoMovimiento
     *
     * @return \ConfigBundle\Entity\BancoTipoMovimiento
     */
    public function getTipoMovimiento()
    {
        return $this->tipoMovimiento;
    }

    /**
     * Set banco
     *
     * @param \ConfigBundle\Entity\Banco $banco
     * @return BancoMovimiento
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
     * Set cuenta
     *
     * @param \ConfigBundle\Entity\CuentaBancaria $cuenta
     * @return BancoMovimiento
     */
    public function setCuenta(\ConfigBundle\Entity\CuentaBancaria $cuenta = null)
    {
        $this->cuenta = $cuenta;

        return $this;
    }

    /**
     * Get cuenta
     *
     * @return \ConfigBundle\Entity\CuentaBancaria
     */
    public function getCuenta()
    {
        return $this->cuenta;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return BancoMovimiento
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
     * @return BancoMovimiento
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
     * Set cheque
     *
     * @param \ConfigBundle\Entity\Cheque $cheque
     * @return BancoMovimiento
     */
    public function setCheque(\ConfigBundle\Entity\Cheque $cheque = null)
    {
        $this->cheque = $cheque;

        return $this;
    }

    /**
     * Get cheque
     *
     * @return \ConfigBundle\Entity\Cheque
     */
    public function getCheque()
    {
        return $this->cheque;
    }
}
