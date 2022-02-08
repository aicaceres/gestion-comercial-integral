<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * Usuario
 * @ORM\Table(name="usuario")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\UsuarioRepository")
 */
class Usuario implements UserInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string $username
     * @ORM\Column(name="username", type="string",unique=true)
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string")
     * @Assert\NotBlank()
     */
    protected $nombre;

    /**
     * @var string $dni
     * @ORM\Column(name="dni", type="string", length=8, nullable=true)
     *
     */
    protected $dni;

    /**
     * @var string $email
     * @ORM\Column(name="email", type="string")
     * @Assert\NotBlank()
     */
    protected $email;

    /**
     * @var string $password
     * @ORM\Column(name="password", type="string")
     */
    protected $password;

    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true;

    /**
     * @ORM\Column(name="fecha_alta", type="datetime")
     */
    protected $fechaAlta;   
    
    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\RolUnidadNegocio", mappedBy="usuario", cascade={"persist","remove"})
     */
    protected $rolesUnidadNegocio;        
    
    protected $roles;     

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __toString() {
        return $this->nombre;
    }

    public function getTitle(){
        return '" '.$this->username.' - '.$this->nombre.' "';
    }
    
    public function getRol($unidneg){
        if( $this->id ){
            foreach ($this->getRolesUnidadNegocio() as $rolUN) {
                 if( $rolUN->getUnidadNegocio()->getId() == $unidneg ){
                     return $rolUN->getRol()->getDescripcion();
                 }
             }
         }
        return false; 
    }
    public function isAdmin($unidneg){
        if( $this->id ){
            foreach ($this->getRolesUnidadNegocio() as $rolUN) {
                 if( $rolUN->getUnidadNegocio()->getId() == $unidneg ){
                     return $rolUN->getRol()->getAdmin();
                 }
             }
         }
        return false; 
    }

    /**
     * Get depositos del usuario según unidad de negocio
     */    
    public function getDepositos($unidneg){
        if( $this->id ){
            foreach ($this->getRolesUnidadNegocio() as $rolUN) {
                 if( $rolUN->getUnidadNegocio()->getId() == $unidneg ){
                     return $rolUN->getDepositos();
                 }
             }
         }
        return false; 
    }
    
    public function getCantPedidosSolicitados($unidneg){
        if( $this->id ){
            $cant = 0;
            foreach ($this->getDepositos($unidneg) as $dep) {
                foreach($dep->getPedidosSolicitados() as $ps){
                    if( $ps->getEstado()=='PENDIENTE' ){
                        $cant = $cant + 1;                        
                    }
                }                
             }
         }
        return $cant; 
    }
    public function getPedidosSolicitados($unidneg){
        if( $this->id ){
            $datos = new \Doctrine\Common\Collections\ArrayCollection(); 
            foreach ($this->getDepositos($unidneg) as $dep) {
                foreach($dep->getPedidosSolicitados() as $ps){
                    if( $ps->getEstado()=='PENDIENTE' ){
                        $datos->add($ps);                        
                    }                    
                }                
             }
         }
          $iterator = $datos->getIterator();
         $iterator->uasort(function ($a, $b) {
            return ($a->getFechaPedido()->format('Y-m-d') > $b->getFechaPedido()->format('Y-m-d')) ? -1 : 1;
        });
        $datosSorted = new \Doctrine\Common\Collections\ArrayCollection(iterator_to_array($iterator));
        return $datosSorted;
    }
    public function getCantPedidosDemandados($unidneg){
        if( $this->id ){
            $cant = 0;
            foreach ($this->getDepositos($unidneg) as $dep) {
                foreach($dep->getPedidosDemandados() as $ps){
                    if( $ps->getEstado()=='PENDIENTE' ){
                        $cant = $cant + 1;                        
                    }
                }                
             }
         }
        return $cant; 
    }
    public function getPedidosDemandados($unidneg){
        if( $this->id ){
            $datos = new \Doctrine\Common\Collections\ArrayCollection(); 
            foreach ($this->getDepositos($unidneg) as $dep) {
                foreach($dep->getPedidosDemandados() as $ps){
                    if( $ps->getEstado()=='PENDIENTE' ){
                        $datos->add($ps);                         
                    }
                }                
             }
         }
         $iterator = $datos->getIterator();
         $iterator->uasort(function ($a, $b) {
            return ($a->getFechaPedido()->format('Y-m-d') > $b->getFechaPedido()->format('Y-m-d')) ? -1 : 1;
        });
        $datosSorted = new \Doctrine\Common\Collections\ArrayCollection(iterator_to_array($iterator));
        return $datosSorted; 
    }

    public function getAccess($unidneg,$slug) {
        foreach ($this->getRolesUnidadNegocio() as $rolUN) {
            if( $rolUN->getUnidadNegocio()->getId() == $unidneg ){
                return $rolUN->getAccess($slug);
            }
        }
        return false;
    }
    
  /*  
    public function getInitRoute(){
        return $this->getRol()->getInitRoute();
    }

    public function getAccess($slug) {
        return $this->rol->getAccess($slug);
    }
   * 
   */
    public function __construct()
    {
        $this->fechaAlta = new \DateTime();        
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = strtoupper($username);
    }
    
    /**
     * Get username
     * @return string
     */
    public function getUsername()
    {
        return strtoupper($this->username);
    }

    /**
     * Set nombre
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
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
     * Set dni
     *
     * @param string $dni
     */
    public function setDni($dni)
    {
        $this->dni = $dni;
    }

    /**
     * Get dni
     *
     * @return string
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     *  password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

     /**
     * Set activo
     * @param boolean $activo
     * @return Usuario
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
     * Set fechaAlta
     *
     * @param \DateTime $fechaAlta
     * @return Usuario
     */
    public function setFechaAlta($fechaAlta)
    {
        $this->fechaAlta = $fechaAlta;

        return $this;
    }

    /**
     * Get fechaAlta
     *
     * @return \DateTime
     */
    public function getFechaAlta()
    {
        return $this->fechaAlta;
    }

    
    
    
// IMPLEMENTACION DE USERINTERFACE
    public function getRoles()
    {
        //$datos[] = $this->getRolesUnidadNegocio()->getRol()->getNombre();
        $datos=array();
        return $datos;
    }
    public function getSalt(){
        return false;
    }
    public function eraseCredentials(){
        return false;
    }
    public function equals(UserInterface $user){
        return $this->getUsername() == $user->getUsername();
    }


    /**
     * Add rolesUnidadNegocio
     *
     * @param \ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio
     * @return Usuario
     */
    public function addRolesUnidadNegocio(\ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio)
    {
        $rolesUnidadNegocio->setUsuario($this);
        $this->rolesUnidadNegocio[] = $rolesUnidadNegocio;
        return $this;
    }

    /**
     * Remove rolesUnidadNegocio
     *
     * @param \ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio
     */
    public function removeRolesUnidadNegocio(\ConfigBundle\Entity\RolUnidadNegocio $rolesUnidadNegocio)
    {
        $this->rolesUnidadNegocio->removeElement($rolesUnidadNegocio);
    }

    /**
     * Get rolesUnidadNegocio
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRolesUnidadNegocio()
    {
        return $this->rolesUnidadNegocio;
    }
}
