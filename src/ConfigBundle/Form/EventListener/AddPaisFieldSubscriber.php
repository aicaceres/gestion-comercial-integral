<?php
namespace ConfigBundle\Form\EventListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddPaisFieldSubscriber implements EventSubscriberInterface
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

    private function addPaisForm($form, $pais = null)
    {
        $formOptions = array(
            'class'         => 'ConfigBundle:Pais',
            'mapped'        => false,
            'label'         => 'País:',
            'placeholder'   => 'Seleccione País',
            'required'      =>false,
            'attr'          => array('class' => ' pais_selector'),
        );

        if ($pais) {
            $formOptions['data'] = $pais;
        }

        $form->add('pais', 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        $localidad    = $accessor->getValue($data, $this->propertyPathToLocalidad);
        $pais = ($localidad) ? $localidad->getProvincia()->getPais() : null;

        $this->addPaisForm($form, $pais);
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        $this->addPaisForm($form);
    }
}