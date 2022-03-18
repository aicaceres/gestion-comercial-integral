<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ConfigBundle\Entity\Empresa
 *
 * @ORM\Table(name="empresa")
 * @ORM\Entity()
 */
class Empresa
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @var string $nombre_corto
     * @ORM\Column(name="nombre_corto", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $nombre_corto;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $nombre;
    /**
     * @var string $leyenda
     * @ORM\Column(name="leyenda", type="string", nullable=true)
     */
    protected $leyenda;
    /**
     * @var string $leyendaFactura
     * @ORM\Column(name="leyenda_factura", type="text", nullable=true)
     */
    protected $leyendaFactura;
    /**
     * @var string $cuit
     * @ORM\Column(name="cuit", type="string", nullable=true)
     * @Assert\NotBlank()
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
     * @var string $email
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;
    /**
     * @var string $iibb
     * @ORM\Column(name="iibb", type="string", nullable=true)
     */
    protected $iibb;
    /**
     * @var string $inicioActividades
     * @ORM\Column(name="inicio_actividades", type="string", nullable=true)
     */
    protected $inicioActividades;
    /**
     * @var string $condicionIva
     * @ORM\Column(name="condicion_iva", type="string", nullable=true)
     */
    protected $condicionIva;

    /**
     * @var string $responsable
     * @ORM\Column(name="responsable", type="string", nullable=true)
     */
    protected $responsable;
    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true;

    /**
     * @var string $label1
     * @ORM\Column(name="label1", type="string", nullable=true)
     */
    protected $label1;
    /**
     * @var string $label2
     * @ORM\Column(name="label2", type="string", nullable=true)
     */
    protected $label2;
    /**
     * @var string $estilo
     * @ORM\Column(name="estilo", type="string", nullable=true)
     */
    protected $estilo;

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
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\UnidadNegocio", mappedBy="empresa",cascade={"persist"})
     */
    protected $unidades;


/**
 * MANEJO DE FOTO
 */
    /**
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * Set path
     * @param string $path
     * @return paciente
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
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // se deshace del __DIR__ para no meter la pata
        // al mostrar el documento/imagen cargada en la vista.
        return 'uploads';
    }

    /**
     * @Assert\File(maxSize="2M", mimeTypes={"image/jpeg", "image/pjpeg", "image/png", "image/x-png"},
     *              mimeTypesMessage="El tipo de imagen no es válido. Debe ser .png o .jpg")
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

    /*
    * FIN MANEJO DE FOTO
    */
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->unidades = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nombre_corto
     *
     * @param string $nombreCorto
     * @return Empresa
     */
    public function setNombreCorto($nombreCorto)
    {
        $this->nombre_corto = $nombreCorto;

        return $this;
    }

    /**
     * Get nombre_corto
     *
     * @return string
     */
    public function getNombreCorto()
    {
        return $this->nombre_corto;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Empresa
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
     * Set leyenda
     *
     * @param string $leyenda
     * @return Empresa
     */
    public function setLeyenda($leyenda)
    {
        $this->leyenda = $leyenda;

        return $this;
    }

    /**
     * Get leyenda
     *
     * @return string
     */
    public function getLeyenda()
    {
        return $this->leyenda;
    }

    /**
     * Set leyendaFactura
     *
     * @param string $leyendaFactura
     * @return Empresa
     */
    public function setLeyendaFactura($leyendaFactura)
    {
        $this->leyendaFactura = $leyendaFactura;

        return $this;
    }

    /**
     * Get leyendaFactura
     *
     * @return string
     */
    public function getLeyendaFactura()
    {
        return $this->leyendaFactura;
    }

    /**
     * Set cuit
     *
     * @param string $cuit
     * @return Empresa
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
     * @return Empresa
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
     * @return Empresa
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
     * Set responsable
     *
     * @param string $responsable
     * @return Empresa
     */
    public function setResponsable($responsable)
    {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return string
     */
    public function getResponsable()
    {
        return $this->responsable;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return Empresa
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
     * Set label1
     *
     * @param string $label1
     * @return Empresa
     */
    public function setLabel1($label1)
    {
        $this->label1 = $label1;

        return $this;
    }

    /**
     * Get label1
     *
     * @return string
     */
    public function getLabel1()
    {
        return $this->label1;
    }

    /**
     * Set label2
     *
     * @param string $label2
     * @return Empresa
     */
    public function setLabel2($label2)
    {
        $this->label2 = $label2;

        return $this;
    }

    /**
     * Get label2
     *
     * @return string
     */
    public function getLabel2()
    {
        return $this->label2;
    }

    /**
     * Set estilo
     *
     * @param string $estilo
     * @return Empresa
     */
    public function setEstilo($estilo)
    {
        $this->estilo = $estilo;

        return $this;
    }

    /**
     * Get estilo
     *
     * @return string
     */
    public function getEstilo()
    {
        return $this->estilo;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Empresa
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Empresa
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
     * Add unidades
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidades
     * @return Empresa
     */
    public function addUnidad(\ConfigBundle\Entity\UnidadNegocio $unidades)
    {
        $unidades->setEmpresa($this);
        $this->unidades[] = $unidades;

        return $this;
    }

    /**
     * Remove unidades
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidades
     */
    public function removeUnidad(\ConfigBundle\Entity\UnidadNegocio $unidades)
    {
        $this->unidades->removeElement($unidades);
    }

    /**
     * Get unidades
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUnidades()
    {
        return $this->unidades;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Empresa
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
     * Set iibb
     *
     * @param string $iibb
     * @return Empresa
     */
    public function setIibb($iibb)
    {
        $this->iibb = $iibb;

        return $this;
    }

    /**
     * Get iibb
     *
     * @return string
     */
    public function getIibb()
    {
        return $this->iibb;
    }

    /**
     * Set inicioActividades
     *
     * @param string $inicioActividades
     * @return Empresa
     */
    public function setInicioActividades($inicioActividades)
    {
        $this->inicioActividades = $inicioActividades;

        return $this;
    }

    /**
     * Get inicioActividades
     *
     * @return string
     */
    public function getInicioActividades()
    {
        return $this->inicioActividades;
    }

    /**
     * Set condicionIva
     *
     * @param string $condicionIva
     * @return Empresa
     */
    public function setCondicionIva($condicionIva)
    {
        $this->condicionIva = $condicionIva;

        return $this;
    }

    /**
     * Get condicionIva
     *
     * @return string
     */
    public function getCondicionIva()
    {
        return $this->condicionIva;
    }

    /**
     * Add unidades
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidades
     * @return Empresa
     */
    public function addUnidade(\ConfigBundle\Entity\UnidadNegocio $unidades)
    {
        $this->unidades[] = $unidades;

        return $this;
    }

    /**
     * Remove unidades
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidades
     */
    public function removeUnidade(\ConfigBundle\Entity\UnidadNegocio $unidades)
    {
        $this->unidades->removeElement($unidades);
    }
}
