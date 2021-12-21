<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipoType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nroPedidoCompra',null,array('label' => 'Nº de Pedido:'))
            ->add('nroFacturaCompra',null,array('label' => 'Nº de Factura:'))
            ->add('nroNotaDebCredCompra',null,array('label' => 'Nº Nota de Crédito:'))
            ->add('nroPagoCompra',null,array('label' => 'Nº de Pago:'))
                
            ->add('nroPedidoVenta',null,array('label' => 'Nº de Pedido:'))           
            ->add('nroPagoVenta',null,array('label' => 'Nº de Pago:'))
                
            ->add('nroFacturaVentaA',null,array('label' => 'Nº de Factura A:'))
            ->add('nroFacturaVentaB',null,array('label' => 'Nº de Factura B:'))
             
            ->add('nroNotaDebitoVentaA',null,array('label' => 'Nº Nota de Débito A:'))    
            ->add('nroNotaDebitoVentaB',null,array('label' => 'Nº Nota de Débito B:'))    
                
            ->add('nroNotaCreditoVentaA',null,array('label' => 'Nº Nota de Crédito A:'))    
            ->add('nroNotaCreditoVentaB',null,array('label' => 'Nº Nota de Crédito B:'))    
            ->add('nroFacturaVentaC',null,array('label' => 'Nº de Factura C:'))

            ->add('nroInternoCheque',null,array('label' => 'Nº Interno Cheque:'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Equipo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_equipo';
    }
}