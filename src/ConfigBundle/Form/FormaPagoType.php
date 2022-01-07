<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormaPagoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',null,array('label' => 'Nombre:'))
            ->add('cuentaCorriente',null,array('label' => 'Cuenta Corriente:','required'=>false))
            ->add('tarjeta',null,array('label' => 'Tarjeta:','required'=>false))
            ->add('cuotas',null,array('label' => 'Cuotas:'))
            ->add('plazo',null,array('label' => 'Plazo:'))
            ->add('vencimiento','choice', array('label'=>'Tipo:',
                          'choices'   => array( 'D' => 'Días','M' => 'Meses'),'expanded'=>false))
            ->add('tipoRecargo',null,array('label' => 'Tipo de Recargo:'))
            ->add('porcentajeRecargo',null,array('label' => 'Recargo:'))
            ->add('descuentoPagoAnticipado',null,array('label' => 'Dto. pago anticipado:'))
            ->add('mora',null,array('label' => 'Mora:'))
            ->add('diasMora',null,array('label' => 'Días de Mora:'))
            ->add('copiasComprobante',null,array('label' => 'Copias por Comprobante:'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\FormaPago'
        ));
    }    

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_formapago';
    }
}