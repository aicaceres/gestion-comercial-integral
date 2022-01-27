<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EscalasType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipo','hidden')
            ->add('nombre',null,array('label' => 'Nombre:'))
            ->add('retencion',null,array('label' => '% Retención:'))
            ->add('adicional',null,array('label' => '% Adicional:'))                       
            ->add('minimo',null,array('label' => 'Mínimo:'))            
            ->add('codigo_atp',null,array('label' => 'Código ATP:'))            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Escalas'
        ));
    }    

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_escalas';
    }
}