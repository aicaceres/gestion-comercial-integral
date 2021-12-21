<?php
namespace AppBundle\Form\EventListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityRepository;

class AddDepositoFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToDeposito;

    public function __construct($propertyPathToDeposito)
    {
        $this->propertyPathToDeposito = $propertyPathToDeposito;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'preSetData' ,
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }

    private function addDepositoForm($form, $unidad_negocio_id)
    {
        $formOptions = array(
            'class'         => 'AppBundle:Deposito',
            'placeholder'   => 'Seleccione Depósito',
            'label'         => 'Depósito:',
            'required'      => false,            
            'attr'          => array('class' => 'deposito_selector'),
            'query_builder' => function (EntityRepository $repository) use ($unidad_negocio_id) {
                $qb = $repository->createQueryBuilder('deposito')
                    ->innerJoin('deposito.unidadNegocio', 'unidadNegocio')
                    ->where('unidadNegocio.id = :unidadNegocio')
                    ->orderBy('deposito.nombre')    
                    ->setParameter('unidadNegocio', $unidad_negocio_id);
                return $qb;
            }
        );
        $form->add($this->propertyPathToDeposito, 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $accessor    = PropertyAccess::createPropertyAccessor();

        $deposito     = $accessor->getValue($data, $this->propertyPathToDeposito);
        $unidad_negocio_id = ($deposito) ? $deposito->getUnidadNegocio()->getId() : null;

        $this->addDepositoForm($form, $unidad_negocio_id);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $unidad_negocio_id = array_key_exists('unidadNegocio', $data) ? $data['unidadNegocio'] : null;

        $this->addDepositoForm($form, $unidad_negocio_id);
    }
}