<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CuentaBancariaType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nroCuenta',null,array('required' => true))
            ->add('tipoCuenta','choice', array( 'expanded'=>false,
                'choices'   => array( 'CTACTE' => 'Cuenta Corriente','CAJA_AHORRO' => 'Caja de Ahorro')
              ))
            ->add('moneda',null,array('required'=>true))
            ->add('activo',null,array('required'=>false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\CuentaBancaria'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_cuentabancaria';
    }
}