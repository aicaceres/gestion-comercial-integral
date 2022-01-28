<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Entity\ParametroRepository;
use ConfigBundle\Form\ParametroType;

class ProductoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo')
            ->add('nombre')
            ->add('descripcion')
            ->add('stock_minimo',null, array('label'=>'Stock Mínimo'))
            ->add('iva')
            ->add('costo')
            ->add('codigoBarra')
            ->add('observaciones')
            ->add('activo')
            ->add('facturable',null, array('label'=>'Facturable en Ventas'))
            ->add('bulto',null, array('label'=>'Por bulto'))
            ->add('cantidadxBulto',null, array('label'=>'Cantidad por bulto'))
            ->add('stock', 'collection', array(
                'type'           => new StockType(),
                'by_reference'   => false,
                'allow_delete'   => true,
                'allow_add'      => true,
                'prototype_name' => 'items',
                'attr'           => array(
                    'class' => 'row item'
                )))    
            ->add('precios', 'collection', array(
                'type'           => new PrecioProductoType(),
                'by_reference'   => false,
                'allow_delete'   => true,
                'allow_add'      => true,
                'prototype_name' => 'items2',
                'attr'           => array(
                    'class' => 'row item2'
                )))    
            ->add('proveedor','entity',array('label'=>'Proveedor:',
                'class' => 'ComprasBundle:Proveedor', 'required' =>false,
                'attr'  => array('class' => 'chzn-select'),
                'query_builder'=> function(EntityRepository $repository){
                    return $qb = $repository->createQueryBuilder('p')
                            ->where('p.activo=1')
                            ->orderBy('p.nombre');
                    }
                ))         
            ;

         $builder->add('rubro','entity',array(
                    'label'=>'Rubro',
                    'choice_label'=>'nombre',
                    'placeholder'=>'Seleccionar Rubro...',
                    'attr' => array('class' => 'chzn-select'),
                    'required'      =>false,
                    'class' => 'ConfigBundle\\Entity\\Parametro',
                    'query_builder' => function(ParametroRepository $em) 
                    { return $em->getSubRubros();  },))  ;
         
        $optionsUM = array(
            'class'         => 'ConfigBundle:Parametro',
            'placeholder'   => 'Seleccionar...',
            'required'      =>true,
            'attr' => array('class' => 'uniformselect'),
            'label'         => 'Unidad Medida',
            'query_builder' => function (ParametroRepository $repository) {
                return $qb = $repository->createQueryBuilder('p')
                    ->where('p.activo=1 and p.agrupador = :val ')
                    ->setParameter('val', ParametroType::getTablaId($repository, 'unidad-medida'));
            });
         $builder->add('unidadMedida', 'entity', $optionsUM);                    
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Producto'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_producto';
    }
}
