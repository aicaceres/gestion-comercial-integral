<?php
namespace ConfigBundle\Form\EventListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityRepository;

class AddLocalidadFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToLocalidad;

    public function __construct($propertyPathToLocalidad)
    {
        $this->propertyPathToLocalidad = $propertyPathToLocalidad;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'preSetData' ,
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }

    private function addLocalidadForm($form, $provincia_id)
    {
        $formOptions = array(
            'class'         => 'ConfigBundle:Localidad',
            'placeholder'   => 'Seleccione Localidad',
            'label'         => 'Localidad:',
            'required'      => false,            
            'attr'          => array('class' => 'localidad_selector'),
            'query_builder' => function (EntityRepository $repository) use ($provincia_id) {
                $qb = $repository->createQueryBuilder('localidad')
                    ->innerJoin('localidad.provincia', 'provincia')
                    ->where('provincia.id = :provincia')
                    ->orderBy('localidad.name')    
                    ->setParameter('provincia', $provincia_id);
                return $qb;
            }
        );
        $form->add($this->propertyPathToLocalidad, 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $accessor    = PropertyAccess::createPropertyAccessor();

        $localidad        = $accessor->getValue($data, $this->propertyPathToLocalidad);
        $provincia_id = ($localidad) ? $localidad->getProvincia()->getId() : null;

        $this->addLocalidadForm($form, $provincia_id);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $provincia_id = array_key_exists('provincia', $data) ? $data['provincia'] : null;

        $this->addLocalidadForm($form, $provincia_id);
    }
}