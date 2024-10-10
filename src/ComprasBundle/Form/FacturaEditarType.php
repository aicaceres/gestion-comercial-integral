<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FacturaEditarType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('detalles', 'collection', array(
                'type' => new FacturaDetalleType(),
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype_name' => 'items',
                'attr' => array(
                    'class' => 'row item'
            )))
            // Items adicionales
            ->add('subtotal', null, array('scale' => 2))
            ->add('subtotalNeto', null, array('scale' => 2))
            ->add('impuestoInterno', null, array('scale' => 2))
            ->add('iva', null, array('scale' => 2))
            ->add('percepcionIva', null, array('scale' => 2))
            ->add('percepcionDgr', null, array('scale' => 2))
            ->add('percepcionMunicipal', null, array('scale' => 2))
            ->add('totalBonificado', null, array('scale' => 2))
            ->add('tmc', null, array('scale' => 2))
            ->add('total', null, array('scale' => 2))
            // afip
            ->add('alicuotas', 'collection', array(
                'type' => new FacturaAlicuotaType(),
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
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ComprasBundle\Entity\Factura'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_facturaeditar';
    }

}