<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VentasBundle\Form\ChequeType;

class CobroDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('tipoPago','hidden')
                ->add('moneda', 'entity', array(
                    'class' => 'ConfigBundle:Moneda',
                    'required' => true,
                    'label'=>'MONEDA: '
                ))
                ->add('importe')
                ->add('datosTarjeta', new CobroDetalleTarjetaType())
                ->add('chequeRecibido', new ChequeType())
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\CobroDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_cobrodetalle';
    }

}