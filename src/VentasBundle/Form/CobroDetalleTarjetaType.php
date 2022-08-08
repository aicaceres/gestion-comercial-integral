<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CobroDetalleTarjetaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('tarjeta', 'entity', array(
                    'class' => 'ConfigBundle:Tarjeta',
                    'required' => false
                ))
                ->add('cupon')
                ->add('cuota',null, array('required' => true) )
                ->add('numero')
                ->add('firmante')
                ->add('codigoAutorizacion',null,array('required' => false))
                //->add('presentarAlFacturar','choice', array('required' => false, 'expanded' => false,
                //   'choices' => array(1 => 'Si', 0 => 'No') ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\CobroDetalleTarjeta'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_cobrodetalletarjeta';
    }

}