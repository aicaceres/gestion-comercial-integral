<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * AppBundle\Entity\PrecioActualizacion
 *
 * @ORM\Table(name="precio_actualizacion")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\PrecioActualizacionRepository")
 */
class PrecioActualizacion
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $tipoActualizacion
     * @ORM\Column(name="tipo_actualizacion", type="string")
     */
    protected $tipoActualizacion;

    /**
     * @var string $valor
     * @ORM\Column(name="valor", type="decimal", precision=15, scale=2)
     */
    protected $valor;

    /**
     * @var string $criteria
     * @ORM\Column(name="criteria", type="string", nullable=true)
     */
    protected $criteria;

    /**
     * @var string $valores
     * @ORM\Column(name="valores", type="string", nullable=true)
     */
    protected $valores;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrecioLista", inversedBy="actualizaciones")
     * @ORM\JoinColumn(name="precio_lista_id", referencedColumnName="id")
     */
    protected $precioLista;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tipoActualizacion
     *
     * @param string $tipoActualizacion
     * @return PrecioActualizacion
     */
    public function setTipoActualizacion($tipoActualizacion)
    {
        $this->tipoActualizacion = $tipoActualizacion;

        return $this;
    }

    /**
     * Get tipoActualizacion
     *
     * @return string
     */
    public function getTipoActualizacion()
    {
        return $this->tipoActualizacion;
    }

    /**
     * Set valor
     *
     * @param string $valor
     * @return PrecioActualizacion
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
     * Set criteria
     *
     * @param string $criteria
     * @return PrecioActualizacion
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Get criteria
     *
     * @return string
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return PrecioActualizacion
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
     * Set precioLista
     *
     * @param \AppBundle\Entity\PrecioLista $precioLista
     * @return PrecioActualizacion
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return PrecioActualizacion
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
     * Set valores
     *
     * @param string $valores
     * @return PrecioActualizacion
     */
    public function setValores($valores)
    {
        $this->valores = $valores;

        return $this;
    }

    /**
     * Get valores
     *
     * @return string
     */
    public function getValores()
    {
        return $this->valores;
    }
}