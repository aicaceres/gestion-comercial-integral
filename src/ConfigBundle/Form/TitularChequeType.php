<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ConfigBundle\Form\EventListener\AddLocalidadFieldSubscriber;
use ConfigBundle\Form\EventListener\AddProvinciaFieldSubscriber;
use ConfigBundle\Form\EventListener\AddPaisFieldSubscriber;

class TitularChequeType extends AbstractType
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
            ->add('nombre',null,array('label'=>'Nombre:'))
            ->add('cuit',null,array('label'=>'CUIT:'))
            ->add('direccion',null,array('label'=>'Dirección:'))
            ->add('telefono',null,array('label'=>'Teléfono:'))
            ->add('celular',null,array('label'=>'Celular:'))
            ->add('email',null,array('label'=>'Email:'))
            ->add('observaciones',null,array('label'=>'Observaciones:'))
            ->add('devueltos','checkbox',array('label'=>'Devueltos:','required'=>false))
            ->add('activo','checkbox',array('label'=>'Activo:','required'=>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\TitularCheque'
        ));
    }    

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_titularcheque';
    }
}