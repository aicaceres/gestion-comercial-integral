<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CajaAperturaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('caja')
                ->add('referer','hidden',array('mapped'=>false))
                ->add('montoApertura','hidden',array('label' => 'Monto Inicial:', 'required' => false));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\CajaApertura'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_apertura';
    }

}