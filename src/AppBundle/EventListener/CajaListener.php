<?php
namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CajaListener
{
    private $em;
    private $session;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        // Solo manejar solicitudes principales (ignorar sub-requests)
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $clientIp = $request->getClientIp(); // Obtener IP del cliente
        // Resolver el hostname desde la IP
        $hostname = gethostbyaddr($clientIp);
        // Guardar el hostname en la sesión
        $this->session->set('hostname', $hostname);

        // Buscar la caja asociada al hostname
        $caja = $this->em->getRepository('ConfigBundle:Caja')->findOneBy([
            'hostname' => $hostname,
            'activo' => true
        ]);
        if ($caja) {
            // Guardar la información de la caja en la sesión
            $apertura = $this->em->getRepository('VentasBundle:CajaApertura')
                ->findAperturaSinCerrar($caja->getId());
            $this->session->set('caja', [
                'id' => $caja->getId(),
                'nombre' => $caja->getNombre(),
                'apertura' => $apertura ? $apertura->getId() : false
            ]);
        } else {
            // Si no se encuentra caja, limpiar los datos relacionados
            $this->session->remove('caja');
        }
    }
}
