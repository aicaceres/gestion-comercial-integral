<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParametrizacionType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder            
            ->add('ventasClienteBydefault', 'entity', array('label' => 'Cliente por defecto en ventas:',
                    'label_attr' => array('style'=>'width:180px'),
                    'class' => 'VentasBundle:Cliente', 'required' => true,
                    'attr' => array('class' => 'smallinput chzn-select')))
            ->add('ventasDepositoBydefault', 'entity', array('label' => 'Depósito por defecto en ventas:',
                    'label_attr' => array('style'=>'width:180px'),
                    'class' => 'AppBundle:Deposito', 'required' => true,
                    'attr' => array('class' => 'smallinput chzn-select')))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Parametrizacion'
        ));
    }    

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_parametrizacion';
    }
}