<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class FacturaDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
//            ->add('producto', 'entity', array(
//                'required' => true,
//                'placeholder' => 'Seleccionar Producto...',
//                'choice_label' => 'codigoNombreBarcode',
//                'class' => 'AppBundle\\Entity\\Producto',
//                'attr' => array('class' => 'select2', 'label' => 'Producto:')
//            ))
            ->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
            ->add('bulto', null, array('required' => false))
            ->add('cantidadxBulto', null, array('required' => false))
            ->add('precio', null, array('label' => 'Costo:', 'required' => true, 'scale' => 3))
            // afip
            ->add('afipAlicuota', 'entity', array('required' => true,
                'class' => 'ConfigBundle:AfipAlicuota',
                'query_builder' => function(EntityRepository $repository) {
                    return $qb = $repository->createQueryBuilder('a')
                        ->where('a.activo=1');
                }
            ))
            ->add('centroCostoDetalle', 'collection', array(
                'type' => new CentroCostoDetalleType(),
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype_name' => 'cctds',
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
            'data_class' => 'ComprasBundle\Entity\FacturaDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_facturadetalle';
    }

}