<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ProductoRepository;
use Doctrine\ORM\EntityRepository;

class NotaDebCredDetalleType extends AbstractType {
    private $type;
    public function __construct($type)
    {
        $this->type= $type;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
                //->add('bulto', null, array('required' => false))
                //->add('cantidadxBulto', null, array('required' => false))
                ->add('textoComodin','text')
                ->add('precio', 'hidden')
                ->add('alicuota', 'hidden')
        ;
        if($this->type=='new'){
            $builder->add('producto', 'entity', array(
                    'required' => true,
                    'placeholder' => 'Seleccionar Producto...',
                    'class' => 'AppBundle\\Entity\\Producto',
                    'query_builder' => function(EntityRepository $repository){
                        return $qb = $repository->createQueryBuilder('c')
                                ->where("c.id=0");
                    }
            ));
        }else{
            $builder->add('producto', 'entity', array(
                    'required' => true,
                    'placeholder' => 'Seleccionar Producto...',
                    'class' => 'AppBundle\\Entity\\Producto',
                    'attr' => array('class' => 'chzn-select', 'label' => 'Producto:'),
                    'query_builder' => function(ProductoRepository $em) {
                        return $em->getProductosFacturables();
                    }));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\NotaDebCredDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_notadebcreddetalle';
    }

}