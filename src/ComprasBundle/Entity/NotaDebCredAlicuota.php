<?php

namespace ComprasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ComprasBundle\Entity\NotaDebCredAlicuota
 * @ORM\Table(name="compras_nota_debcred_alicuota")
 * @ORM\Entity()
 */
class NotaDebCredAlicuota {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="neto_gravado", type="decimal", precision=20, scale=2 )
     */
    protected $netoGravado;
    /**
     * @ORM\Column(name="liquidado", type="decimal", precision=20, scale=2 )
     */
    protected $liquidado;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\AfipAlicuota")
     * @ORM\JoinColumn(name="afip_alicuota_id", referencedColumnName="id")
     * */
    protected $afipAlicuota;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\NotaDebCred", inversedBy="alicuotas")
     * @ORM\JoinColumn(name="compras_nota_debcred_id", referencedColumnName="id")
     */
    protected $notaDebCred;


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
     * Set netoGravado
     *
     * @param string $netoGravado
     * @return NotaDebCredAlicuota
     */
    public function setNetoGravado($netoGravado)
    {
        $this->netoGravado = $netoGravado;

        return $this;
    }

    /**
     * Get netoGravado
     *
     * @return string 
     */
    public function getNetoGravado()
    {
        return $this->netoGravado;
    }

    /**
     * Set liquidado
     *
     * @param string $liquidado
     * @return NotaDebCredAlicuota
     */
    public function setLiquidado($liquidado)
    {
        $this->liquidado = $liquidado;

        return $this;
    }

    /**
     * Get liquidado
     *
     * @return string 
     */
    public function getLiquidado()
    {
        return $this->liquidado;
    }

    /**
     * Set afipAlicuota
     *
     * @param \ConfigBundle\Entity\AfipAlicuota $afipAlicuota
     * @return NotaDebCredAlicuota
     */
    public function setAfipAlicuota(\ConfigBundle\Entity\AfipAlicuota $afipAlicuota = null)
    {
        $this->afipAlicuota = $afipAlicuota;

        return $this;
    }

    /**
     * Get afipAlicuota
     *
     * @return \ConfigBundle\Entity\AfipAlicuota 
     */
    public function getAfipAlicuota()
    {
        return $this->afipAlicuota;
    }

    /**
     * Set notaDebCred
     *
     * @param \ComprasBundle\Entity\NotaDebCred $notaDebCred
     * @return NotaDebCredAlicuota
     */
    public function setNotaDebCred(\ComprasBundle\Entity\NotaDebCred $notaDebCred = null)
    {
        $this->notaDebCred = $notaDebCred;

        return $this;
    }

    /**
     * Get notaDebCred
     *
     * @return \ComprasBundle\Entity\NotaDebCred 
     */
    public function getNotaDebCred()
    {
        return $this->notaDebCred;
    }
}
