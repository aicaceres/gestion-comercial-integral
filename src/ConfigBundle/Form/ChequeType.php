<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ConfigBundle\Form\EventListener\AddLocalidadFieldSubscriber;
use ConfigBundle\Form\EventListener\AddProvinciaFieldSubscriber;
use ConfigBundle\Form\EventListener\AddPaisFieldSubscriber;

class ChequeType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertyPathToLocalidad = 'localidad';

        $builder
            ->addEventSubscriber(new AddLocalidadFieldSubscriber($propertyPathToLocalidad))
            ->addEventSubscriber(new AddProvinciaFieldSubscriber($propertyPathToLocalidad))
            ->addEventSubscriber(new AddPaisFieldSubscriber($propertyPathToLocalidad));           
        $builder  
            ->add('id','hidden')    
            ->add('tipo','hidden') 
            ->add('nroCheque',null,array('label' => 'Nº Cheque:','required'=>true))
           // ->add('nroInterno',null,array('label' => 'Nº Interno:','required'=>false,'mapped'=>false))
            ->add('dador','text',array('label' => 'Dador:','required'=>true))
            ->add('telefono','text',array('label' => 'Telefono:','required'=>false))
            ->add('fecha','date',array('widget' => 'single_text','label' => 'Fecha:', 
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('titularCheque','entity',array('class' => 'ConfigBundle:TitularCheque',
                'placeholder'=>'Seleccionar..' ,'label' => 'Titular:','required' =>false))
            ->add('banco','entity',array('class' => 'ConfigBundle:Banco',
                'label' => 'Banco:','required' =>true))
            ->add('sucursal',null,array('label' => 'Sucursal:','required' => false))
            ->add('tomado','date',array('widget' => 'single_text', 'label' => 'Tomado el:',
                'format' => 'dd-MM-yyyy', 'required' => false))
            ->add('devuelto',null,array('label' => 'Devuelto/Rechazado:','required' => false))
            ->add('observaciones',null,array('label' => 'Observaciones:','required' => false))    
            ->add('usado',null,array('label' => 'Utilizado:','required' => false))
            ->add('valor',null,array('label' => 'Valor:'));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'ConfigBundle\Entity\Cheque'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_cheque';
    }
}
