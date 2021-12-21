<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrecioListaType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('descripcion')
            ->add('principal',null,array('required'=>false))
            ->add('activo',null,array('required'=>false))
            ->add('vigenciaDesde','date',array('widget' => 'single_text', 'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('vigenciaHasta','date',array('widget' => 'single_text', 'format' => 'dd-MM-yyyy', 'required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PrecioLista'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_preciolista';
    }
}
