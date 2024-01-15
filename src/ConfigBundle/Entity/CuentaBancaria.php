<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\CuentaBancaria
 * @ORM\Table(name="cuenta_bancaria")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\BancoRepository")
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
     * @var string $nroCuenta
     * @ORM\Column(name="nro_cuenta", type="string", nullable=false)
     */
    protected $nroCuenta;
    /**
     * @var string $activo
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
     protected $activo;
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
}
