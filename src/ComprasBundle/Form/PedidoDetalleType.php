<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PedidoDetalleType extends AbstractType {

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
                'class' => 'AppBundle:Producto',
                'choice_label' => 'codigoNombreBarcode',
                'query_builder' => function(EntityRepository $repository) use ($prod) {
                    $qb = $repository->createQueryBuilder('p')
                            ->where('p.id = :prod')
                            ->setParameter('prod', $prod);
                    return $qb;
                },));
        }
        else {
            $builder->add('producto', 'entity', array(
                'required' => true,
                'label' => 'Producto:',
                'choice_label' => 'codigoNombreBarcode',
                'placeholder' => 'Seleccionar Producto...',
                'class' => 'AppBundle:Producto'
            ));
        }
        $builder
                /* ->add('producto', 'entity', array(
                  'required' => true,
                  'placeholder' => 'Seleccionar Producto...',
                  'class' => 'AppBundle\\Entity\\Producto',
                  'attr'  => array('class' => 'chzn-select','label'=>'Producto:')
                  )) */
                ->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
                ->add('bulto', null, array('required' => false))
                ->add('cantidadxBulto', null, array('required' => false))
                ->add('cantidadStock', 'hidden', array('required' => false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ComprasBundle\Entity\PedidoDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_pedidodetalle';
    }

}