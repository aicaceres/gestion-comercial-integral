<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class NotaDebCredType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('tipoNota', 'choice', array('choices' => array('A' => 'A', 'B' => 'B', 'C' => 'C', 'M' => 'M'),
                    'label' => 'Tipo y Nº'))
                ->add('nroComprobante', null, array('label' => 'Comprobante:', 'required' => true))
                ->add('fecha', 'date', array('widget' => 'single_text', 'label' => 'Fecha Nota:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('estado', 'hidden')
                ->add('subtotal')
                ->add('modificaStock', 'choice', array('label' => 'MODIFICA STOCK:',
                    'choices' => array(0 => 'No', 1 => 'Si'), 'expanded' => false))
                /* ->add('signo', 'choice', array('label' => 'Tipo Nota:',
                  'choices' => array('-' => 'CRÉDITO', '+' => 'DÉBITO'), 'expanded' => false)) */
                ->add('signo', 'hidden')
                /* ->add('concepto', 'choice', array('label' => 'Motivo:',
                  'choices' => array('Motivo 1' => 'Motivo 1', 'Motivo 2' => 'Motivo 2'), 'expanded' => false)) */
                ->add('proveedor', 'entity', array('label' => 'Proveedor:',
                    'class' => 'ComprasBundle:Proveedor', 'required' => false,
                    'attr' => array('class' => 'mediuminput'),
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('p')
                                ->where('p.activo=1')
                                ->orderBy('p.nombre');
                    }
                ))
                ->add('facturas', 'entity', array(
                    'class' => 'ComprasBundle:Factura',
                    'label' => 'Facturas:',
                    'choice_label' => 'nroTipoComprobante',
                    'multiple' => true,
                    'required' => false,
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('c')
                                ->innerJoin('c.proveedor', 'p')
                                ->where('c.saldo>0');
                    }
                ))
                ->add('detalles', 'collection', array(
                    'type' => new NotaDebCredDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
            )))

// Items adicionales
                ->add('subtotal', null, array('scale' => 3))
                ->add('subtotalNeto', null, array('scale' => 3))
                ->add('impuestoInterno', null, array('scale' => 3))
                ->add('iva', null, array('scale' => 3))
                ->add('percepcionIva', null, array('scale' => 3))
                ->add('percepcionDgr', null, array('scale' => 3))
                ->add('percepcionMunicipal', null, array('scale' => 3))
                ->add('totalBonificado', null, array('scale' => 3))
                ->add('tmc', null, array('scale' => 3))
                ->add('total', null, array('scale' => 3))

// afip
                ->add('afipComprobante', 'entity', array('label' => 'Comprobante:',
                    'class' => 'ConfigBundle:AfipComprobante', 'required' => true,
                    'attr' => array('class' => 'mediuminput chzn-select'),
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('c')
                                ->where('c.activo=1')
                                ->andWhere('c.valor like :param1')
                                ->setParameter('param1', '%DEB%')
                                ->orWhere('c.valor like :param2')
                                ->setParameter('param2', '%CRE%')
                                ->orderBy('c.codigo');
                    }
                ))
                ->add('afipPuntoVenta', null, array('label' => 'N° Comprobante', 'required' => true))
                ->add('afipNroComprobante', null, array('required' => true))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ComprasBundle\Entity\NotaDebCred',
            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_notadebcred';
    }

}