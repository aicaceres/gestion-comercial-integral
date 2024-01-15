<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ConfigBundle\Entity\CentroCosto
 * @ORM\Table(name="banco_tipo_movimiento")
 * @ORM\Entity()
 */
class BancoTipoMovimiento {
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
     * @ORM\Column(name="signo", type="string", nullable=false)
     */
    protected $signo = '+';

    public function __toString() {
        return $this->nombre;
    }

    public function selectText(){
      return $this->nombre.' ['.$this->signo.']';
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
     * @return BancoTipoMovimiento
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
     * Set signo
     *
     * @param string $signo
     * @return BancoTipoMovimiento
     */
    public function setSigno($signo)
    {
        $this->signo = $signo;

        return $this;
    }

    /**
     * Get signo
     *
     * @return string
     */
    public function getSigno()
    {
        return $this->signo;
    }
}
