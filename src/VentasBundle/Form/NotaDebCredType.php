<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Entity\ParametroRepository;
use ConfigBundle\Form\ParametroType;

class NotaDebCredType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $type = $options['attr']['type'];
        $builder
                ->add('signo', 'hidden')
                ->add('fecha', 'date', array('widget' => 'single_text', 'label' => 'Fecha Nota:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('nombreCliente',null, array('label' => 'Nombre:'))
                ->add('nroDocumentoCliente',null, array('label'=>'N° Documento:'))
                ->add('formaPago', 'entity', array('class' => 'ConfigBundle:FormaPago',
                    'required' => true, 'label' => 'FORMA DE PAGO: '))
                ->add('precioLista', 'entity', array('label' => 'Lista de Precios:',
                    'class' => 'AppBundle:PrecioLista', 'required' => true))
                ->add('moneda', 'entity', array(
                    'class' => 'ConfigBundle:Moneda',
                    'required' => true, 'label' => 'MONEDA: '
                ))
                ->add('concepto','text',array('label' => 'Concepto Adicional:','required' => false))
                ->add('cotizacion','hidden')
                ->add('descuentoRecargo', null, array('attr'=> array('required' => true)))

                ->add('periodoAsocDesde', 'date', array('widget' => 'single_text', 'label' => 'Período Desde:',
                    'format' => 'dd-MM-yyyy', 'required' => false))
                ->add('periodoAsocHasta', 'date', array('widget' => 'single_text', 'label' => 'Hasta:',
                    'format' => 'dd-MM-yyyy', 'required' => false))

                ->add('detalles', 'collection', array(
                    'type' => new NotaDebCredDetalleType($type),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
                )))
                ->add('cobroDetalles', 'collection', array(
                    'type' => new CobroDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'citems',
                    'attr' => array(
                        'class' => 'row citem'
                )))
                ->add('notaElectronica', new NotaElectronicaType())
        ;

        if ($type == 'new') {
            // en render de nuev presupuesto solo traer cliente por defecto
            $data = $options['data'];
            $cliente = $data->getCliente()->getId();
            $builder
                ->add('cliente', 'entity', array(
                        'required' => true,
                        'class' => 'VentasBundle:Cliente',
                        'label' => 'DATOS DEL CLIENTE: ',
                        'query_builder' => function (EntityRepository $repository) use ($cliente) {
                            return $qb = $repository->createQueryBuilder('c')
                                                    ->where("c.id=" . $cliente);
                        }
                ))
                ->add('comprobanteAsociado', 'entity', array(
                        'class' => 'VentasBundle:FacturaElectronica',
                        'label' => 'Comprobante Asociado:',
                        'choice_label' => 'selectComprobanteTxt',
                        'required' => false,
                        'attr'=> array('class' => 'select2'),
                        'query_builder' => function (EntityRepository $repository) use ($cliente) {
                                return $qb = $repository->createQueryBuilder('f')
                                                        ->innerJoin('f.cobro','c')
                                                        ->innerJoin('c.cliente','cl')
                                                        ->where("cl.id=" . $cliente);
                            }
                    ))
            ;
        } else if ($type == 'create') {
            // al crear traer objeto completo para match del cliente
            $builder->add('cliente', 'entity', array(
                            'class' => 'VentasBundle:Cliente',
                            'required' => true
                            ))
                    ->add('comprobanteAsociado', 'entity', array(
                        'class' => 'VentasBundle:FacturaElectronica',
                        'label' => 'Comprobante Asociado:',
                        'choice_label' => 'selectComprobanteTxt',
                        'required' => false,
                    ))
                ;
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