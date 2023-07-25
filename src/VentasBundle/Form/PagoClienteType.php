<?php
namespace VentasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PagoClienteType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $cliente = $data->getCliente();
        $builder
            ->add('fecha','date',array('widget' => 'single_text', 'label'=>'FECHA DE PAGO:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('pagoNro','hidden')
            ->add('observaciones',null,array('required'=>false,'attr' => array('rows'=>'1')))
            ->add('generaNotaCredito',null,array('label' => 'Generar NC:','required'=>false))
            ->add('total')
            ->add('moneda', 'entity', array(
                    'class' => 'ConfigBundle:Moneda',
                    'required' => true, 'label' => 'MONEDA: '
                ))
            ->add('cotizacion','hidden')
            ->add('cobroDetalles', 'collection', array(
                    'type' => new CobroDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
                )))
            ->add('cliente')
            ->add('comprobantes', 'entity', array(
                  'required' => false, 'label' => 'COMPROBANTES PENDIENTES:', 'multiple' => true,
                  'placeholder' => 'Seleccionar...', 'attr' => array('class' => 'select2'),
                  'choice_label' => 'comprobanteCtaCtePendienteTxt',
                  'class' => 'VentasBundle:FacturaElectronica',
                  'query_builder' => function(EntityRepository $repository) use ($cliente) {
                      $qb = $repository->createQueryBuilder('f')
                              ->leftJoin('f.cobro', 'c')
                              ->leftJoin('f.notaDebCred', 'n')
                              ->where('c.cliente = :cli')
                              ->orWhere('n.cliente = :cli')
                              ->andWhere('f.saldo>0')
                              ->setParameter('cli', $cliente);
                      return $qb;
                  },))
        ;



        // if ($cliente) {
        //     // en render de nueva venta solo traer el cliente seleccionado
        //     $builder->add('cliente', 'entity', array('required' => true,
        //                 'class' => 'VentasBundle:Cliente', 'label' => 'DATOS DEL CLIENTE: ',
        //                 'query_builder' => function (EntityRepository $repository) use ($cliente) {
        //                     return $qb = $repository->createQueryBuilder('c')
        //                         ->where("c.id=" . $cliente->getId());
        //                    }
        //                 ))
        //             ->add('comprobantes', 'entity', array(
        //                 'required' => false, 'label' => 'COMPROBANTES PENDIENTES:', 'multiple' => true,
        //                 'placeholder' => 'Seleccionar...', 'attr' => array('class' => 'select2'),
        //                 'choice_label' => 'comprobanteCtaCtePendienteTxt',
        //                 'class' => 'VentasBundle:FacturaElectronica',
        //                 'query_builder' => function(EntityRepository $repository) use ($cliente) {
        //                     $qb = $repository->createQueryBuilder('f')
        //                             ->leftJoin('f.cobro', 'c')
        //                             ->leftJoin('f.notaDebCred', 'n')
        //                             ->where('c.cliente = :cli')
        //                             ->orWhere('n.cliente = :cli')
        //                             ->andWhere('f.saldo>0')
        //                             ->setParameter('cli', $cliente);
        //                     return $qb;
        //                 },))
        //         ;
        // } else {
        //     // al crear traer objeto completo para match del cliente
        //     $builder->add('cliente', 'entity', array(
        //             'class' => 'VentasBundle:Cliente',
        //             'required' => true
        //             ))
        //             ->add('comprobantes', 'entity', array(
        //                 'required' => false, 'label' => 'COMPROBANTES PENDIENTES:',
        //                 'choice_label' => 'comprobanteCtaCtePendienteTxt', 'multiple' => true,
        //                 'class' => 'VentasBundle:FacturaElectronica'
        //                 ))
        //     ;
        // }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\PagoCliente',
            'error_bubbling' => true,
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
