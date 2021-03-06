<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActividadComercialType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo',null,array('label' => 'Código:'))
            ->add('nombre',null,array('label' => 'Nombre:'))
            ->add('exento',null,array('label' => 'Exento:'))
            ->add('inscripto',null,array('label' => 'Inscripto:'))
            ->add('noInscripto',null,array('label' => 'No Inscripto:'))            
            ->add('minimo',null,array('label' => 'Mínimo:'))            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\ActividadComercial'
        ));
    }    

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_actividadcomercial';
    }
}