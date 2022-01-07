<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ConfigBundle\Form\EventListener\AddLocalidadFieldSubscriber;
use ConfigBundle\Form\EventListener\AddProvinciaFieldSubscriber;
use ConfigBundle\Form\EventListener\AddPaisFieldSubscriber;

class DepositoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertyPathToLocalidad = 'localidad';

        $builder
            ->addEventSubscriber(new AddLocalidadFieldSubscriber($propertyPathToLocalidad))
            ->addEventSubscriber(new AddProvinciaFieldSubscriber($propertyPathToLocalidad))
            ->addEventSubscriber(new AddPaisFieldSubscriber($propertyPathToLocalidad));      
        
        $builder
            ->add('nombre', 'text', array('label' => 'Depósito:','required'=>true))
            ->add('direccion', 'text', array('label' => 'Dirección:','required'=>true))
            ->add('telefono', 'text', array('label' => 'Teléfono:','required'=>false))
            ->add('central',null,array('label' => 'Central:','required'=>false))
            ->add('pordefecto',null,array('label' => 'Por defecto:','required'=>false))
            ->add('activo',null,array('label' => 'Activo:','required'=>false))
            ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Deposito'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_deposito';
    }
}
