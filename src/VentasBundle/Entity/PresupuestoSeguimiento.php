<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * VentasBundle\Entity\PresupuestoSeguimiento
 *
 * Registro de cada llamado de seguimiento comercial de un Presupuesto.
 * Guarda fecha/hora, usuario que llama, estado elegido y comentario.
 * El conjunto de registros conforma el historial de comentarios; el
 * "último llamado" es simplemente el registro más reciente.
 *
 * @ORM\Table(name="ventas_presupuesto_seguimiento", indexes={
 *     @ORM\Index(name="idx_presupuesto_fecha", columns={"presupuesto_id", "fecha"})
 * })
 * @ORM\Entity()
 */
class PresupuestoSeguimiento {

    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Presupuesto", inversedBy="seguimientos")
     * @ORM\JoinColumn(name="presupuesto_id", referencedColumnName="id", nullable=false)
     */
    protected $presupuesto;

    /**
     * @var datetime $fecha
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    protected $fecha;

    /**
     * @var Usuario $usuario
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    protected $usuario;

    /**
     * Estado comercial elegido en este llamado.
     * Valores: PENDIENTE | VENDIDO | NEGOCIANDO | PERDIDO
     *
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado;

    /**
     * @var string $comentario
     * @ORM\Column(name="comentario", type="text", nullable=false)
     */
    protected $comentario;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set presupuesto
     *
     * @param \VentasBundle\Entity\Presupuesto $presupuesto
     * @return PresupuestoSeguimiento
     */
    public function setPresupuesto(\VentasBundle\Entity\Presupuesto $presupuesto = null) {
        $this->presupuesto = $presupuesto;

        return $this;
    }

    /**
     * Get presupuesto
     *
     * @return \VentasBundle\Entity\Presupuesto
     */
    public function getPresupuesto() {
        return $this->presupuesto;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return PresupuestoSeguimiento
     */
    public function setFecha($fecha) {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * Set usuario
     *
     * @param \ConfigBundle\Entity\Usuario $usuario
     * @return PresupuestoSeguimiento
     */
    public function setUsuario(\ConfigBundle\Entity\Usuario $usuario = null) {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUsuario() {
        return $this->usuario;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return PresupuestoSeguimiento
     */
    public function setEstado($estado) {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado() {
        return $this->estado;
    }

    /**
     * Set comentario
     *
     * @param string $comentario
     * @return PresupuestoSeguimiento
     */
    public function setComentario($comentario) {
        $this->comentario = $comentario;

        return $this;
    }

    /**
     * Get comentario
     *
     * @return string
     */
    public function getComentario() {
        return $this->comentario;
    }

}
