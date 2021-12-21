<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrecioActualizacionType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('precioLista',null,array('label' => 'Lista:','required'=>true))
            ->add('criteria','choice', array('label'=>'Actualizar:',
                          'choices'   => array('T'=>'Todos', 'R' => 'Rubro', 'P' => 'Proveedor'),'expanded'=>true))
            ->add('valores',null,array('label' => 'Valores:','required'=>false))    
            ->add('tipoActualizacion','choice', array('label'=>'Aplicar:',
                          'choices'   => array( 'P' => 'Porcentaje','M' => 'Monto Fijo'),'expanded'=>true))
            ->add('valor')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PrecioActualizacion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_precioactualizacion';
    }
}
