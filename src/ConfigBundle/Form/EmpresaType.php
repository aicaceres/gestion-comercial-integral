<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmpresaType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('nombre_corto','text',array('label'=>'Título:', 'required' => true))
            ->add('leyenda')
            ->add('leyendaFactura',null,array('label'=>'Leyenda de Factura:', 'required' => false))
            ->add('cuit')
            ->add('direccion')
            ->add('telefono')
            ->add('responsable')
            ->add('unidades', 'collection', array(
                    'type'           => new UnidadNegocioType(),
                    'by_reference'   => false,
                    'allow_delete'   => true,
                    'allow_add'      => true,
                    'prototype_name' => 'itemform',
                    'attr'           => array(
                        'class' => 'row item'
                )))    
                ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Empresa'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_empresa';
    }
}
