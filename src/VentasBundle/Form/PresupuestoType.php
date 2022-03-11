<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PresupuestoType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $type = $options['attr']['type'];
        $builder
                ->add('nroPresupuesto')
                ->add('fechaPresupuesto', 'date', array('widget' => 'single_text',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                //->add('estado')
                ->add('deposito', 'entity', array(
                    'class' => 'AppBundle:Deposito',
                    'required' => true, 'label' => 'DEPÓSITO: ',
                    'choice_label' => 'nombre'
                ))
                ->add('descuentaStock',null,array('label' => 'DESCONTAR:','required'=>false))
                ->add('nombreCliente')
                ->add('validez',null,array('label' => 'VALIDEZ [días]:','required'=>false))
                ->add('formaPago', 'entity', array('class' => 'ConfigBundle:FormaPago',
                    'required' => true, 'label' => 'FORMA DE PAGO: '))
                ->add('precioLista', 'entity', array('label' => 'LISTA DE PRECIOS:',
                    'class' => 'AppBundle:PrecioLista', 'required' => true))
                ->add('detalles', 'collection', array(
                    'type' => new PresupuestoDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
            )))
                ->add('total','hidden',array('required'=>false))
                ->add('descuento','hidden',array('required'=>false))
        ;
        if ($type == 'new') {
            // en render de nuev presupuesto solo traer cliente por defecto
            $data = $options['data'];
            $cliente = $data->getCliente()->getId();
            $builder->add('cliente', 'entity', array('required' => true,
                'class' => 'VentasBundle:Cliente', 'label' => 'DATOS DEL CLIENTE: ',
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
            'data_class' => 'VentasBundle\Entity\Presupuesto'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_presupuesto';
    }

}