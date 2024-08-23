<?php

namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParametrizacionType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('ventasClienteBydefault', 'entity', array('label' => 'Cliente por defecto en ventas:',
                'label_attr' => array('style' => 'width:200px'),
                'class' => 'VentasBundle:Cliente', 'required' => true,
                'attr' => array('class' => 'smallinput chzn-select')))
            ->add('ventasDepositoBydefault', 'entity', array('label' => 'Depósito por defecto en ventas:',
                'label_attr' => array('style' => 'width:200px'),
                'class' => 'AppBundle:Deposito', 'required' => true,
                'attr' => array('class' => 'smallinput chzn-select')))
            ->add('ultimoNroOperacionVenta', null, array('label' => 'Último N° de Operación de Venta:',
                'label_attr' => array('style' => 'width:200px')))
            ->add('ultimoNroPresupuesto', null, array('label' => 'Último N° de Presupuesto:',
                'label_attr' => array('style' => 'width:200px')))
            ->add('cantidadItemsParaFactura', null, array('label' => 'Cantidad de Items para Factura:',
                'label_attr' => array('style' => 'width:200px')))
            ->add('formPagoFE', 'choice', array('expanded' => false, 'label' => 'Forma de Pago FE:', 'label_attr' => array('style' => 'width:200px'),
                'choices' => array('SCA' => 'SCA - Sistema de Circulación Abierta', 'ADC' => 'ADC - Agente de Depósito Colectivo')
            ))
            ->add('cbuEmisor', null, array('label' => 'CBU Emisor:',
                'label_attr' => array('style' => 'width:200px'),
                'attr' => array('class' => 'smallinput')))
            ->add('aliasEmisor', null, array('label' => 'Alias Emisor:',
                'label_attr' => array('style' => 'width:200px'),
                'attr' => array('class' => 'smallinput')))
            ->add('referenciaComercial', null, array('label' => 'Referencia Comercial:',
                'label_attr' => array('style' => 'width:200px'),
                'attr' => array('class' => 'smallinput')))

        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Parametrizacion'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'configbundle_parametrizacion';
    }

}