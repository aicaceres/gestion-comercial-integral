<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecepcionType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {                   
        $builder
            ->add('fechaEntrega','date',array('widget' => 'single_text', 'label'=>'Fecha Recepción:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('estado','hidden')
            ->add('observRecepcion')      
            ->add('despachoRecibido','checkbox',array('mapped' => false,'required' => false)) ; 
        ;                         
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Despacho'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_recepcion';
    }
}
