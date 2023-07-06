<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\CuentaBancaria
 * @ORM\Table(name="cuenta_bancaria")
 * @ORM\Entity()
 */
class CuentaBancaria {
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
    // CC:ctacte o CA:caja ahorro
    protected $tipo;
    /**
     * @var string $nroCuenta
     * @ORM\Column(name="nro_cuenta", type="string", nullable=false)
     */
    protected $nroCuenta;
    /**
     * @var string $cbu
     * @ORM\Column(name="cbu", type="string", nullable=false)
     */
    protected $cbu;
    /**
     * @var string $titular
     * @ORM\Column(name="titular", type="string", nullable=false)
     */
    protected $titular;
    /**
     * @var string $sucursal
     * @ORM\Column(name="sucursal", type="string", nullable=false)
     */
    protected $sucursal;
    /**
     * @var string $activo
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    protected $activo = true;
    /**
    * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
    * @ORM\JoinColumn(name="localidad_id", referencedColumnName="id")
    */
    protected $localidad;
    /**
     *@ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Banco", inversedBy="cuentas")
     *@ORM\JoinColumn(name="banco_id", referencedColumnName="id")
     */
    protected $banco;

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
     * Set tipo
     *
     * @param string $tipo
     * @return CuentaBancaria
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
     * Set nroCuenta
     *
     * @param string $nroCuenta
     * @return CuentaBancaria
     */
    public function setNroCuenta($nroCuenta)
    {
        $this->nroCuenta = $nroCuenta;

        return $this;
    }

    /**
     * Get nroCuenta
     *
     * @return string
     */
    public function getNroCuenta()
    {
        return $this->nroCuenta;
    }

    /**
     * Set cbu
     *
     * @param string $cbu
     * @return CuentaBancaria
     */
    public function setCbu($cbu)
    {
        $this->cbu = $cbu;

        return $this;
    }

    /**
     * Get cbu
     *
     * @return string
     */
    public function getCbu()
    {
        return $this->cbu;
    }

    /**
     * Set titular
     *
     * @param string $titular
     * @return CuentaBancaria
     */
    public function setTitular($titular)
    {
        $this->titular = $titular;

        return $this;
    }

    /**
     * Get titular
     *
     * @return string
     */
    public function getTitular()
    {
        return $this->titular;
    }

    /**
     * Set sucursal
     *
     * @param string $sucursal
     * @return CuentaBancaria
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
     * Set localidad
     *
     * @param \ConfigBundle\Entity\Localidad $localidad
     * @return CuentaBancaria
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
     * Set banco
     *
     * @param \ConfigBundle\Entity\Banco $banco
     * @return CuentaBancaria
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
     * Set activo
     *
     * @param boolean $activo
     * @return CuentaBancaria
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
}