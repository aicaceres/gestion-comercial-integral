<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ProductoRepository;
use Doctrine\ORM\EntityRepository;

class PresupuestoDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('producto', 'entity', array(
                    'required' => true,
                    'placeholder' => 'Seleccionar Producto...',
                    'class' => 'AppBundle\\Entity\\Producto',
                    'attr' => array('class' => 'chzn-select', 'label' => 'Producto:'),
                    'query_builder' => function(ProductoRepository $em) {
                        return $em->getProductosFacturables();
                    }))
                ->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
                ->add('bulto', null, array('required' => false))
                ->add('cantidadxBulto', null, array('required' => false))
                ->add('precio', null, array('label' => 'Costo:', 'required' => true))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\PresupuestoDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_presupuestodetalle';
    }

}