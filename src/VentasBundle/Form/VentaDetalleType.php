<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ProductoRepository;
use Doctrine\ORM\EntityRepository;

class VentaDetalleType extends AbstractType {

    private $type;
    private $data;
    public function __construct($type,$data)
    {
        $this->type= $type;
        $this->data= $data;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
                //->add('bulto', null, array('required' => false))
                //->add('cantidadxBulto', null, array('required' => false))
                ->add('precio', 'hidden')
                ->add('alicuota', 'hidden')
        ;

        if($this->type=='new'){
            $prodsId = [];
            if( count($this->data->getDetalles() ) >0 ){
                //precargar productos item
                foreach($this->data->getDetalles() as $d){
                    $prodsId[] = $d->getProducto()->getId();
                }
            }

            $builder->add('producto', 'entity', array(
                    'required' => true,
                    'placeholder' => 'Seleccionar Producto...',
                    'class' => 'AppBundle:Producto',
                    'query_builder' => function(EntityRepository $repository)use($prodsId){
                        $qb = $repository->createQueryBuilder('p')
                                        ->where(' p.id IN (:productos)')
                                        ->setParameter('productos', $prodsId, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                        return $qb;
                    }
            ));
        }else{
            $builder->add('producto', 'entity', array(
                    'required' => true,
                    'class' => 'AppBundle:Producto',
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
            'data_class' => 'VentasBundle\Entity\VentaDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_ventadetalle';
    }

}