<?php
namespace AppBundle\Form\EventListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddUnidadNegocioFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToDeposito;

    public function __construct($propertyPathToDeposito)
    {
        $this->propertyPathToDeposito = $propertyPathToDeposito;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }

    private function addUnidadNegocioForm($form, $unidneg = null)
    {
        $formOptions = array(
            'class'         => 'ConfigBundle:UnidadNegocio',
            'mapped'        => false,
            'choice_label'      => 'empresaUnidad',
            'label'         => 'Destino del Pedido:',
            'placeholder'   => 'Seleccione Unidad de Negocio',
            'required'      =>false,
            'attr'          => array('class' => 'unidneg_selector'),
        );

        if ($unidneg) {
            $formOptions['data'] = $unidneg;
        }

        $form->add('unidadNegocio', 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        $deposito    = $accessor->getValue($data, $this->propertyPathToDeposito);
        $unidneg = ($deposito) ? $deposito->getUnidadNegocio() : null;

        $this->addUnidadNegocioForm($form, $unidneg);
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        $this->addUnidadNegocioForm($form);
    }
}