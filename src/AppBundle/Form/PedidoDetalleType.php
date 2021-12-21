<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PedidoDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('producto', 'entity', array(
                    'required' => true,
                    'label' => 'Producto:',
                    'choice_label' => 'codigoNombreBarcode',
                    'placeholder' => 'Seleccionar Producto...',
                    'class' => 'AppBundle:Producto'
                ))
                ->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
                ->add('bulto', null, array('required' => false))
                ->add('cantidadxBulto', null, array('required' => false))
                ->add('cantidadStock', 'hidden', array('required' => false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PedidoDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_pedidodetalle';
    }

}