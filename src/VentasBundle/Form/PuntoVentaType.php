<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PuntoVentaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $optionsCond = array(
            'class' => 'ConfigBundle:Parametro',
            'required' => true,
            'label' => 'Condición de Venta:',
            'choice_label' => 'descripcion',
            'query_builder' => function (EntityRepository $repository) {
                return $qb = $repository->createQueryBuilder('p')
                        ->where('p.activo=1 and p.agrupador = :val ')
                        ->setParameter('val', FacturaType::getTablaId($repository, 'condicion-pago'));
            });

        $builder
                //->add('nroComprobante',null,array('label'=>'N° Comprobante:', 'required' => true))
                ->add('tipoFactura', 'choice', array('choices' => array('A' => 'A', 'B' => 'B', 'C' => 'C', 'M' => 'M'),
                    'label' => 'Tipo y Nº'))
                ->add('nroFactura')
                ->add('fechaFactura', 'date', array('widget' => 'single_text', 'label' => 'Fecha Factura:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                //->add('estado')
                ->add('cliente', 'entity', array('label' => 'Cliente:',
                    'class' => 'VentasBundle:Cliente', 'required' => false,
                    'attr' => array('class' => 'smallinput chzn-select')))
                ->add('condicionPago', 'entity', $optionsCond)
                ->add('precioLista', 'entity', array('label' => 'Lista de Precios:',
                    'class' => 'AppBundle:PrecioLista', 'required' => true))
                ->add('detalles', 'collection', array(
                    'type' => new FacturaDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
            )))
                ->add('iva')
                ->add('total')
                ->add('cae', 'text', array('label' => 'CAE:', 'required' => true, 'mapped' => false))
                ->add('caeVto', 'date', array('widget' => 'single_text', 'label' => 'Vto. CAE:',
                    'format' => 'dd-MM-yyyy', 'required' => true, 'mapped' => false))
                ->add('facturadoDesde', 'date', array('widget' => 'single_text', 'label' => 'Período Facturado Desde:',
                    'format' => 'dd-MM-yyyy', 'required' => true, 'mapped' => false))
                ->add('facturadoHasta', 'date', array('widget' => 'single_text', 'label' => 'Hasta:',
                    'format' => 'dd-MM-yyyy', 'required' => true, 'mapped' => false))
                ->add('pagoVto', 'date', array('widget' => 'single_text', 'label' => 'Vto. para el pago:',
                    'format' => 'dd-MM-yyyy', 'required' => true, 'mapped' => false))

                // afip
                ->add('afipComprobante', 'entity', array('label' => 'Comprobante:',
                    'class' => 'ConfigBundle:AfipComprobante', 'required' => true,
                    'attr' => array('class' => 'mediuminput'),
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('c')
                                ->where('c.activo=1')
                                ->andWhere('c.valor like :param')
                                ->setParameter('param', '%FAC%');
                    }
                ))
                ->add('afipPuntoVenta', null, array('label' => 'N° Comprobante', 'required' => true))
                ->add('afipNroComprobante', null, array('required' => true))
        ;
    }

    public function getTablaId(EntityRepository $repository, $table) {
        $tablaid = $repository->createQueryBuilder('g')->select('g.id')
                        ->where('g.nombre = :tabla ')
                        ->setParameter('tabla', $table)
                        ->getQuery()->getSingleScalarResult();
        return $tablaid;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\Factura'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_factura';
    }

}