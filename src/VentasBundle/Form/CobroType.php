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
        $type = $options['attr']['type'];
        $builder
                ->add('nroOperacion', 'hidden')
                ->add('fechaCobro','datetime')
                ->add('nombreCliente',null, array('label' => 'Nombre:'))
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
                //->add('facturaElectronica', new FacturaElectronicaType())
                ->add('detalles', 'collection', array(
                    'type' => new CobroDetalleType($type),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
                )))
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

        $optionsDoc = array(
            'class'         => 'ConfigBundle:Parametro',
            'placeholder'   => 'Seleccionar...',
            'required'      => false,
            'choice_label' => 'nombre',
            'mapped'=>false,
            'label'         => 'Tipo Documento:',
            'query_builder' => function (ParametroRepository $repository) {
                return $qb = $repository->createQueryBuilder('p')
                    ->where('p.activo=1 and p.agrupador = :val ')
                    ->setParameter('val', ParametroType::getTablaId($repository, 'tipo-documento'));
            });
        $builder->add('tipoDocumentoCliente','entity' , $optionsDoc);

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