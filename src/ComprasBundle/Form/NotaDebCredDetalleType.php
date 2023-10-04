<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class NotaDebCredDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
//                ->add('producto', 'entity', array(
//                    'required' => true,
//                    'class' => 'AppBundle\\Entity\\Producto',
//                    'choice_label' => 'codigoNombreBarcode',
//                    'attr' => array('class' => 'chzn-select', 'label' => 'Producto:')
//                ))
            //->add('textoProducto','hidden')
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
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ComprasBundle\Entity\NotaDebCredDetalle',
            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_notadebcreddetalle';
    }

}