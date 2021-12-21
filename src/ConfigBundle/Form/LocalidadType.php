<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocalidadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Nombre:','required'=>true))
            ->add('codPostal', 'text', array('label' => 'Código Postal:','required'=>false))
            ->add('provincia','entity',array('class' => 'ConfigBundle:Provincia',
                'label' => 'Provincia:','required' =>true))
            ->add('pais','entity',array('class' => 'ConfigBundle:Pais', 'mapped'=>false,
                'label' => 'Pais:','required' =>true));

    }
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Localidad'
        ));
    }
    public function getName()
    {
        return 'configbundle_localidadtype';
    }
}
