<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonedaType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',null,array('label' => 'Nombre:'))
            ->add('simbolo',null,array('label' => 'Símbolo:'))
            ->add('cotizacion',null,array('label' => 'Cotización:'))
            ->add('tope',null,array('label' => 'Tope:'))            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Moneda'
        ));
    }    

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_moneda';
    }
}