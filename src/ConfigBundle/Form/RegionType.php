<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegionType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];

        $builder->add('name',null,array('label' => 'Nombre:'));
        $builder->add('shortname',null,array('label' => 'Abreviatura:'));
        
        if( property_exists($data, 'codpostal') )
            $builder->add('codpostal',null,array('label' => 'Código Postal:', 'required' =>false));

        if( property_exists($data, 'provincia') ){
            $builder->add('provincia','entity',array('label'=>'Provincia:',
                        'class' => 'ConfigBundle:Provincia', 'required' =>true,
                        'placeholder'   => 'Seleccione Provincia'))  
                    ->add('pais','entity',array('label'=>'Pais:',
                        'class' => 'ConfigBundle:Pais', 'mapped' =>false,
                        'placeholder'   => 'Seleccione País'));
        }else{
            if( property_exists($data, 'pais') ){
                $builder->add('pais','entity',array('label'=>'Pais:',
                'class' => 'ConfigBundle:Pais', 'required' =>true));
            }
        }   

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_region';
    }
}