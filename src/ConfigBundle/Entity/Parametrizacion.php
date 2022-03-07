<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\Parametrizacion
 * @ORM\Table(name="parametrizacion")
 * @ORM\Entity()
 */
class Parametrizacion {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente")
     * @ORM\JoinColumn(name="ventas_cliente_bydefault_id", referencedColumnName="id")
     */
    protected $ventasClienteBydefault;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Deposito")
     * @ORM\JoinColumn(name="ventas_deposito_bydefault_id", referencedColumnName="id")
     */
    protected $ventasDepositoBydefault;

    /**
     * @var integer $ultimoNroOperacionVenta
     * @ORM\Column(name="ultimo_nro_operacion_venta", type="integer")     
     */
    protected $ultimoNroOperacionVenta = 0;

    /**
     * @var integer $ultimoNroOperacionCobro
     * @ORM\Column(name="ultimo_nro_operacion_cobro", type="integer")     
     */
    protected $ultimoNroOperacionCobro = 0;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

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
     * Set ventasClienteBydefault
     *
     * @param \VentasBundle\Entity\Cliente $ventasClienteBydefault
     * @return Parametrizacion
     */
    public function setVentasClienteBydefault(\VentasBundle\Entity\Cliente $ventasClienteBydefault = null)
    {
        $this->ventasClienteBydefault = $ventasClienteBydefault;

        return $this;
    }

    /**
     * Get ventasClienteBydefault
     *
     * @return \VentasBundle\Entity\Cliente 
     */
    public function getVentasClienteBydefault()
    {
        return $this->ventasClienteBydefault;
    }


    /**
     * Set ventasDepositoBydefault
     *
     * @param \AppBundle\Entity\Deposito $ventasDepositoBydefault
     * @return Parametrizacion
     */
    public function setVentasDepositoBydefault(\AppBundle\Entity\Deposito $ventasDepositoBydefault = null)
    {
        $this->ventasDepositoBydefault = $ventasDepositoBydefault;

        return $this;
    }

    /**
     * Get ventasDepositoBydefault
     *
     * @return \AppBundle\Entity\Deposito 
     */
    public function getVentasDepositoBydefault()
    {
        return $this->ventasDepositoBydefault;
    }

    /**
     * Set ultimoNroOperacionVenta
     *
     * @param integer $ultimoNroOperacionVenta
     * @return Parametrizacion
     */
    public function setUltimoNroOperacionVenta($ultimoNroOperacionVenta)
    {
        $this->ultimoNroOperacionVenta = $ultimoNroOperacionVenta;

        return $this;
    }

    /**
     * Get ultimoNroOperacionVenta
     *
     * @return integer 
     */
    public function getUltimoNroOperacionVenta()
    {
        return $this->ultimoNroOperacionVenta;
    }

    /**
     * Set ultimoNroOperacionCobro
     *
     * @param integer $ultimoNroOperacionCobro
     * @return Parametrizacion
     */
    public function setUltimoNroOperacionCobro($ultimoNroOperacionCobro)
    {
        $this->ultimoNroOperacionCobro = $ultimoNroOperacionCobro;

        return $this;
    }

    /**
     * Get ultimoNroOperacionCobro
     *
     * @return integer 
     */
    public function getUltimoNroOperacionCobro()
    {
        return $this->ultimoNroOperacionCobro;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return Parametrizacion
     */
    public function setUnidadNegocio(\ConfigBundle\Entity\UnidadNegocio $unidadNegocio = null)
    {
        $this->unidadNegocio = $unidadNegocio;

        return $this;
    }

    /**
     * Get unidadNegocio
     *
     * @return \ConfigBundle\Entity\UnidadNegocio 
     */
    public function getUnidadNegocio()
    {
        return $this->unidadNegocio;
    }
}
