<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class FacturaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /* $optionsCond = array(
          'class'         => 'ConfigBundle:Parametro',
          'required'      =>false,
          'empty_value'   => 'Seleccionar...',
          'label'         => 'Forma Pago:',
          'query_builder' => function (EntityRepository $repository) {
          return $qb = $repository->createQueryBuilder('p')
          ->where('p.activo=1 and p.agrupador = :val ')
          ->setParameter('val', FacturaType::getTablaId($repository, 'medio-pago'));
          }); */

        $builder
                ->add('descripcion', null, array('label' => 'Descipción:', 'required' => false))
                ->add('nroComprobante', null, array('label' => 'N° Comprobante:', 'required' => true))
                ->add('tipoFactura', 'choice', array('choices' => array('A' => 'A', 'B' => 'B', 'C' => 'C', 'M' => 'M'),
                    'label' => 'Tipo y Nº'))
                ->add('fechaFactura', 'date', array('widget' => 'single_text', 'label' => 'Fecha Factura:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                //->add('estado')
                ->add('proveedor', 'entity', array('label' => 'Proveedor:',
                    'class' => 'ComprasBundle:Proveedor', 'required' => false,
                    'attr' => array('class' => 'mediuminput select2'),
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('p')
                                ->where('p.activo=1')
                                ->orderBy('p.nombre');
                    }
                ))
                ->add('modificaStock', 'choice', array('label' => 'MODIFICA STOCK:',
                    'choices' => array( 1 => 'SI',0 => 'NO'), 'expanded' => true))
                ->add('pedidoId', 'hidden', array('mapped' => false))
                ->add('pagadoContado', 'checkbox', array('mapped' => false, 'required' => false))
                /* ->add('condicion_venta', 'entity', $optionsCond) */
                /* ->add('precioLista','entity',array('label'=>'Lista:',
                  'class' => 'AdminBundle:PrecioLista', 'required' =>true)) */
                ->add('detalles', 'collection', array(
                    'type' => new FacturaDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
            )))
                // Items adicionales
                ->add('subtotal', null, array('scale' => 3))
                ->add('subtotalNeto', null, array('scale' => 3))
                ->add('impuestoInterno', null, array('scale' => 3))
                ->add('iva', null, array('scale' => 3))
                ->add('percepcionIva', null, array('scale' => 3))
                ->add('percepcionDgr', null, array('scale' => 3))
                ->add('percepcionMunicipal', null, array('scale' => 3))
                ->add('totalBonificado', null, array('scale' => 3))
                ->add('tmc', null, array('scale' => 3))
                ->add('total', null, array('scale' => 3))
                // afip
                ->add('afipComprobante', 'entity', array('label' => 'Comprobante:',
                    'class' => 'ConfigBundle:AfipComprobante', 'required' => true,
                    'attr' => array('class' => 'mediuminput select2'),
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('c')
                                ->where('c.activo=1')
                                ->andWhere("c.valor like '%FAC%'")
                                ->orWhere("c.valor like '%REC%'");
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
            'data_class' => 'ComprasBundle\Entity\Factura'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_factura';
    }

}