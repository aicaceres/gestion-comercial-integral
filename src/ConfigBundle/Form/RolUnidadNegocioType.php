<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolUnidadNegocioType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unidadNegocio','entity',array('label'=>'Unidad Negocio:',
                    'class' => 'ConfigBundle:UnidadNegocio','choice_label' => 'empresaUnidad', 'required' =>true))  
            ->add('rol','entity',array('label'=>'Rol:',
                    'class' => 'ConfigBundle:Rol', 'required' =>true))  
            ->add('depositos', null, array('label' => 'Depositos:', 'required' => false, 'multiple' => true))
            ->add('puntosVenta', null, array('label' => 'Puntos de Venta:', 'required' => false, 'multiple' => true))            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\RolUnidadNegocio'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_rolunidadnegocio';
    }
}