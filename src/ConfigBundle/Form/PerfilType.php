<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PerfilType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('descripcion',null,array('label' => 'Descripción:'))
                //->add('initRoute',null,array('label' => 'Página Inicial:'))
                ->add('activo',null,array('label' => 'Activo ','required'=>false))
                ;
        $data = $options['data'];     
        if( is_null($data->getId()) ){
            $builder->add('nombre',null,array('label' => 'Nombre:'));
        }
        
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_perfil';
    }
}