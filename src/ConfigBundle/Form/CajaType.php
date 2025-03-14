<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CajaType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',null,array('label' => 'Nombre Corto:'))
            ->add('descripcion',null,array('label' => 'Descripción:'))
            ->add('hostname',null,array('label' => 'Nombre Equipo:'))
            ->add('ptoVtaWs',null,array('label' => 'Webservice:'))
            ->add('ptoVtaIfu',null,array('label' => 'Tickeadora:'))
            ->add('activo',null,array('label' => 'Activo:','required'=>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Caja'
        ));
    }    

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_caja';
    }
}