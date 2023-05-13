<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParametrizacionType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ventasClienteBydefault', 'entity', array('label' => 'Cliente por defecto en ventas:',
                    'label_attr' => array('style'=>'width:200px'),
                    'class' => 'VentasBundle:Cliente', 'required' => true,
                    'attr' => array('class' => 'smallinput chzn-select')))
            ->add('ventasDepositoBydefault', 'entity', array('label' => 'Depósito por defecto en ventas:',
                    'label_attr' => array('style'=>'width:200px'),
                    'class' => 'AppBundle:Deposito', 'required' => true,
                    'attr' => array('class' => 'smallinput chzn-select')))
            ->add('ultimoNroOperacionVenta',null,array('label' => 'Último N° de Operación de Venta:',
                    'label_attr' => array('style'=>'width:200px')))
            ->add('ultimoNroPresupuesto',null,array('label' => 'Último N° de Presupuesto:',
                    'label_attr' => array('style'=>'width:200px')))
            ->add('cantidadItemsParaFactura',null,array('label' => 'Cantidad de Items para Factura:',
                    'label_attr' => array('style'=>'width:200px')))
            ->add('validezPresupuesto',null,array('label' => 'Validez del presupuesto en días:',
                    'label_attr' => array('style'=>'width:200px')))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Parametrizacion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_parametrizacion';
    }
}