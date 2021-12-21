<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class DespachoDetalleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
        public function buildForm(FormBuilderInterface $builder, array $options) {
        $prod = 0;
        if ($options['data']->getProducto()) {
            $prod = $options['data']->getProducto()->getId();
            $builder->add('producto', 'entity', array(
                    'required' => true, 'label' => 'Producto:',
                    'placeholder' => 'Seleccionar Producto...',
                    'choice_label'=> 'codigoNombreBarcode',
                    'class' => 'AppBundle:Producto',
                    'query_builder' => function(EntityRepository $repository) use ($prod) {
                        $qb = $repository->createQueryBuilder('p')
                                ->where('p.id = :prod')
                                ->setParameter('prod', $prod);
                        return $qb;
                    },));
        } else {
            $builder->add('producto', 'entity', array(
                'required' => true,
                'label' => 'Producto:',
                'choice_label'=> 'codigoNombreBarcode',
                'placeholder' => 'Seleccionar Producto...',
                'class' => 'AppBundle:Producto'
            ));
        }

        $builder->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
                ->add('bulto', null, array('required' => false))
                ->add('cantidadxBulto', null, array('required' => false))
                // ->add('lotes', null, array('required' => false,'label'=>'Lotes:', ))
                ->add('lotes', 'entity', array(
                    'required' => false, 'label' => 'Lotes:', 'multiple' => true,
                    'placeholder' => 'Seleccionar lotes...',
                    'class' => 'ComprasBundle:LoteProducto',
                    'query_builder' => function(EntityRepository $repository) use ($prod) {
                        $qb = $repository->createQueryBuilder('l')
                                ->innerJoin('l.producto', 'p')
                                ->where('p.id = :prod')
                                ->andWhere('l.activo=1')
                                ->setParameter('prod', $prod);
                        return $qb;
                    },));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\DespachoDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_despachodetalle';
    }
}
