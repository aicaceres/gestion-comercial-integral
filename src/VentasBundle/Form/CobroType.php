<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Entity\ParametroRepository;
use ConfigBundle\Form\ParametroType;


class CobroType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        // $type = $options['attr']['type'];

        // $optionsDoc = array(
        //     'class'         => 'ConfigBundle:Parametro',
        //     'placeholder'   => 'Seleccionar...',
        //     'required'      => false,
        //     'choice_label' => 'nombre',
        //     'label'         => 'Tipo Documento:',
        //     'query_builder' => function (ParametroRepository $repository) {
        //         return $qb = $repository->createQueryBuilder('p')
        //             ->where('p.activo=1 and p.agrupador = :val ')
        //             ->setParameter('val', ParametroType::getTablaId($repository, 'tipo-documento'));
        //     });

        $builder
                ->add('detalles', 'collection', array(
                    'label_attr' => array('style'=>'display:none'),
                    'type' => new CobroDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'citems',
                    'attr' => array(
                        'class' => 'row citem'
                )))
                //->add('nroOperacion', 'hidden')
               // ->add('moneda','hidden')
                //->add('cotizacion','hidden')
                // ->add('venta','hidden')
                // ->add('nombreCliente',null, array('label' => 'Nombre:'))
                // ->add('tipoDocumentoCliente', null, $optionsDoc )
                // ->add('nroDocumentoCliente',null, array('label'=>'N° Documento:'))

                //->add('fechaCobro','datetime')
                //->add('direccionCliente',null, array('label'=>'Dirección:'))

                // ->add('moneda', 'entity', array(
                //     'class' => 'ConfigBundle:Moneda',
                //     'required' => true, 'label' => 'MONEDA: '
                // ))
                // ->add('formaPago', 'entity', array('class' => 'ConfigBundle:FormaPago',
                //     'required' => true, 'label' => 'FORMA DE PAGO: '))
                // ->add('venta', 'entity', array('class' => 'VentasBundle:Venta'))
                //->add('facturaElectronica', new FacturaElectronicaType())

                    // para ticket
                // ->add('nroTicket','hidden',array('mapped'=>false))
        ;
        // if ($type == 'new') {
        //     // en render de nueva venta solo traer cliente por defecto
        //     $data = $options['data'];
        //     $cliente = $data->getCliente()->getId();
        //     $builder->add('cliente', 'entity', array('required' => true,
        //         'class' => 'VentasBundle:Cliente', 'label' => 'CLIENTE: ',
        //         'query_builder' => function (EntityRepository $repository) use ($cliente) {
        //             return $qb = $repository->createQueryBuilder('c')
        //                 ->where("c.id=" . $cliente);
        //         }
        //     ));
        // } else if ($type == 'create') {
        //     // al crear traer objeto completo para match del cliente
        //     $builder->add('cliente', 'entity', array(
        //         'class' => 'VentasBundle:Cliente',
        //         'required' => true
        //     ));
        // }


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