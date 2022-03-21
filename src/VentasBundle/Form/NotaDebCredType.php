<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class NotaDebCredType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $type = $options['attr']['type'];
        $docType = json_decode($options['attr']['docType'],true)  ;
        $builder
                ->add('signo', 'hidden')
                ->add('fecha', 'date', array('widget' => 'single_text', 'label' => 'Fecha Nota:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('nombreCliente',null, array('label' => 'Nombre:'))
                ->add('tipoDocumentoCliente','choice', array('label'=>'Tipo Documento:', 'required' => false,
                   'choices' => $docType, 'expanded' => false))
                ->add('nroDocumentoCliente',null, array('label'=>'N° Documento:'))
                ->add('formaPago', 'entity', array('class' => 'ConfigBundle:FormaPago',
                    'required' => true, 'label' => 'FORMA DE PAGO: '))
                ->add('precioLista', 'entity', array('label' => 'Lista de Precios:',
                    'class' => 'AppBundle:PrecioLista', 'required' => true))
                ->add('moneda', 'entity', array(
                    'class' => 'ConfigBundle:Moneda',
                    'required' => true, 'label' => 'MONEDA: '
                ))
                ->add('cotizacion','hidden')
                ->add('facturas', 'entity', array(
                    'class' => 'VentasBundle:FacturaElectronica',
                    'label' => 'Comprobantes Asociados:',
                    'choice_label' => 'comprobanteTxt',
                    'multiple' => true,
                    'required' => false,
                ))
                ->add('detalles', 'collection', array(
                    'type' => new NotaDebCredDetalleType($type),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
                )))
                ->add('notaElectronica', new NotaElectronicaType())
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
            'data_class' => 'VentasBundle\Entity\NotaDebCred'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_notadebcred';
    }

}