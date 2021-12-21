<?php
namespace ConfigBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityRepository;
//use ConfigBundle\Entity\Pais;

class AddProvinciaFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToLocalidad;

    public function __construct($propertyPathToLocalidad)
    {
        $this->propertyPathToLocalidad = $propertyPathToLocalidad;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }

    private function addProvinciaForm($form, $pais_id, $provincia = null)
    {
        $formOptions = array(
            'class'         => 'ConfigBundle:Provincia',
            'placeholder'   => 'Seleccione Provincia',
            'label'         => 'Provincia:',
            'mapped'        => false,
            'required'      =>false,
            'attr'          => array('class' => 'provincia_selector'),
            'query_builder' => function (EntityRepository $repository) use ($pais_id) {
                $qb = $repository->createQueryBuilder('provincia')
                    ->innerJoin('provincia.pais', 'pais')
                    ->where('pais.id = :pais')
                    ->setParameter('pais', $pais_id)
                ;

                return $qb;
            }
        );

        if ($provincia) {
            $formOptions['data'] = $provincia;
        }

        $form->add('provincia','entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        $localidad        = $accessor->getValue($data, $this->propertyPathToLocalidad);
        $provincia    = ($localidad) ? $localidad->getProvincia() : null;
        $pais_id  = ($provincia) ? $provincia->getPais()->getId() : null;

        $this->addProvinciaForm($form, $pais_id, $provincia);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $pais_id = array_key_exists('pais', $data) ? $data['pais'] : null;
        $this->addProvinciaForm($form, $pais_id);
    }
}