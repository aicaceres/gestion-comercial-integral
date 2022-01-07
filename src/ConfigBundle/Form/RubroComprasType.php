<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RubroComprasType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',null,array('label' => 'Nombre:'))
            ->add('tipo','choice', array('label'=>'Tipo:',
                          'choices'   => array( 'Compra' => 'Compra','Locación' => 'Locación',
                        'Servicio'=>'Servicio','Otros'=>'Otros'),'expanded'=>false))
            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\RubroCompras'
        ));
    }    

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_rubrocompras';
    }
}