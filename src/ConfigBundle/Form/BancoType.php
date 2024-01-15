<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BancoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',null,array('label' => 'Nombre:'))
            ->add('activo',null,array('label' => 'Activo:','required'=>false))
            // ->add('cuentas', 'entity', array(
            //     'class' => 'ConfigBundle:CuentaBancaria',
            //     'label' => 'Cuentas:',
            //     'choice_label' => 'nroCuenta',
            //     'multiple' => true,
            //     'required' => false
            // ))
            ->add('cuentas', 'collection', array(
                    'label_attr' => array('style'=>'display:none'),
                    'type' => new CuentaBancariaType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
                )))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Banco'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_banco';
    }
}