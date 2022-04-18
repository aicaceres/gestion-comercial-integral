<?php
namespace ComprasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Form\ChequeType;

class PagoProveedorType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
$data = $options['data'];
        $prov = $data->getProveedor();
        $builder
            ->add('fecha','date',array('widget' => 'single_text', 'label'=>'Fecha Pago:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('nroComprobante',null,array('label'=>'Comprobante:', 'required' => false))
            ->add('concepto',null,array('label'=>'COMPROBANTES PENDIENTES:', 'required' => false))

            ->add('importe')

            ->add('proveedor','entity',array('label'=>'Proveedor:',
                'class' => 'ComprasBundle:Proveedor', 'required' =>true,
                'attr'  => array('class' => 'smallinput'),'label' => 'DATOS DEL PROVEEDOR: ',
                        'query_builder' => function (EntityRepository $repository) use ($prov) {
                            return $qb = $repository->createQueryBuilder('p')
                                ->where("p.id=" . $prov->getId());
                           }
                        ))
            ->add('chequesPagados', 'collection', array(
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
            'data_class' => 'ComprasBundle\Entity\PagoProveedor'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'comprasbundle_pagoproveedor';
    }
}
