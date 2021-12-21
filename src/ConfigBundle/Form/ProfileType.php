<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
     public function buildForm(FormBuilderInterface $builder, array $options) {
         $builder->add('nombre', 'text', array('label' => 'Nombre:','required'=>true))
                 ->add('dni', 'text', array('label' => 'DNI:','required'=>false))
                 ->add('email', 'email', array('label' => 'Email:','required'=>true))
                 ;
       if ($options['data']->getId() == 0) {
            $builder->add('username', 'text', array('label' => 'Usuario:','required'=>true))
                    ->add('password', 'repeated', array('type' => 'password','required' => true,
                    'invalid_message' => 'Las dos contraseñas deben coincidir',
                    'first_name' => 'Password:', 'second_name' => 'Repetir:'));
        } else {
            $builder->add('username', 'text', array('label' => 'Usuario:','read_only'=>true)) 
                    ->add('password', 'repeated', array('required' => false,'invalid_message' => 'Las dos contraseñas deben coincidir',
                        'type' => 'password','first_name' => 'Password:', 'second_name' => 'Repetir:'));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Usuario'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_profile';
    }

}
