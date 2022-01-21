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
     * @var integer $ultimoNroOperacion
     * @ORM\Column(name="ultimo_nro_operacion", type="integer")     
     */
    protected $ultimoNroOperacion = 0;

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
     * Set ultimoNroOperacion
     *
     * @param integer $ultimoNroOperacion
     * @return Parametrizacion
     */
    public function setUltimoNroOperacion($ultimoNroOperacion)
    {
        $this->ultimoNroOperacion = $ultimoNroOperacion;

        return $this;
    }

    /**
     * Get ultimoNroOperacion
     *
     * @return integer 
     */
    public function getUltimoNroOperacion()
    {
        return $this->ultimoNroOperacion;
    }
}
