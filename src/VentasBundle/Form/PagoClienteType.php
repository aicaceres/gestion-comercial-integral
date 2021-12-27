<?php
namespace VentasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ConfigBundle\Form\ChequeType;

class PagoClienteType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha','date',array('widget' => 'single_text', 'label'=>'Fecha Pago:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('nroComprobante',null,array('label'=>'Comprobante:', 'required' => false))
            ->add('concepto',null,array('label'=>'Facturas:', 'required' => false))
            ->add('detalle',null,array('required'=>false))
            ->add('importe',null,array('label'=>'Efectivo:'))
            ->add('deposito',null,array('label'=>'Depósito:'))
            ->add('cliente','entity',array('label'=>'Cliente:',
                'class' => 'VentasBundle:Cliente', 'required' =>true,
                'attr'  => array('class' => 'smallinput')))
            ->add('chequesRecibidos', 'collection', array(
                'type'           => new ChequeType(),
                'by_reference'   => false,
                'allow_delete'   => true,
                'allow_add'      => true,
                'prototype_name' => 'items',
                'attr'           => array(
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
            'data_class' => 'VentasBundle\Entity\PagoCliente'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ventasbundle_pagocliente';
    }
}