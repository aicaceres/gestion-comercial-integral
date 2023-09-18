<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use VentasBundle\Form\CobroDetalleType;

class PagoProveedorType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $options['data'];
        $prov = $data->getProveedor();
        $builder
            ->add('fecha', 'date', array('widget' => 'single_text', 'label' => 'Fecha Pago:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('nroComprobante', null, array('label' => 'Comprobante:', 'required' => false))
            ->add('concepto', null, array('label' => 'COMPROBANTES PENDIENTES:', 'required' => false))
            ->add('detalle', null, array('required' => false, 'attr' => array('rows' => '1')))
            ->add('importe')
            ->add('montoPago')
            ->add('montoIva', null, array('mapped' => false))
            ->add('moneda', 'entity', array(
                'class' => 'ConfigBundle:Moneda',
                'required' => true, 'label' => 'MONEDA: '
            ))
            ->add('montoRentas', null, array('mapped' => false))
            ->add('montoGanancias', null, array('mapped' => false))
            ->add('baseImponibleRentas', 'hidden')
            ->add('retencionRentas', 'hidden')
            ->add('adicionalRentas', 'hidden')
            ->add('retencionGanancias', 'hidden')
            ->add('cotizacion', 'hidden')
            ->add('cobroDetalles', 'collection', array(
                'type' => new CobroDetalleType(),
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype_name' => 'items',
                'attr' => array(
                    'class' => 'row item'
            )))
        ;

        if ($prov) {
            $builder->add('proveedor', 'entity', array('label' => 'Proveedor:',
                'class' => 'ComprasBundle:Proveedor', 'required' => true,
                'attr' => array('class' => 'smallinput'), 'label' => 'DATOS DEL PROVEEDOR: ',
                'query_builder' => function (EntityRepository $repository) use ($prov) {
                    return $qb = $repository->createQueryBuilder('p')
                        ->where("p.id=" . $prov->getId());
                }
            ));
        }
        else {
            $builder->add('proveedor', 'entity', array(
                'class' => 'ComprasBundle:Proveedor',
                'required' => true
            ));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ComprasBundle\Entity\PagoProveedor',
            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_pagoproveedor';
    }

}