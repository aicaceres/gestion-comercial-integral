<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CobroType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $type = $options['attr']['type'];
        $docType = json_decode($options['attr']['docType'],true)  ;

        $builder
                ->add('nroOperacion', 'hidden')
                ->add('fechaCobro','datetime')
                ->add('nombreCliente',null, array('label' => 'Nombre:'))
                ->add('tipoDocumentoCliente','choice', array('label'=>'Tipo Documento:', 'required' => false,
                   'choices' => $docType, 'expanded' => false))
                ->add('nroDocumentoCliente',null, array('label'=>'N° Documento:'))
                //->add('direccionCliente',null, array('label'=>'Dirección:'))

                ->add('moneda', 'entity', array(
                    'class' => 'ConfigBundle:Moneda',
                    'required' => true, 'label' => 'MONEDA: '
                ))
                ->add('cotizacion','hidden')
                ->add('formaPago', 'entity', array('class' => 'ConfigBundle:FormaPago',
                    'required' => true, 'label' => 'FORMA DE PAGO: '))
                ->add('venta', 'entity', array('class' => 'VentasBundle:Venta'))
                ->add('facturaElectronica', new FacturaElectronicaType())
        ;
        if ($type == 'new') {
            // en render de nueva venta solo traer cliente por defecto
            $data = $options['data'];
            $cliente = $data->getCliente()->getId();
            $builder->add('cliente', 'entity', array('required' => true,
                'class' => 'VentasBundle:Cliente', 'label' => 'CLIENTE: ',
                'query_builder' => function (EntityRepository $repository) use ($cliente) {
                    return $qb = $repository->createQueryBuilder('c')
                        ->where("c.id=" . $cliente);
                }
            ));
        } else if ($type == 'create') {
            // al crear traer objeto completo para match del cliente
            $builder->add('cliente', 'entity', array(
                'class' => 'VentasBundle:Cliente',
                'required' => true
            ));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\Cobro'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_cobro';
    }

}