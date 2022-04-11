<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ProductoRepository;
use Doctrine\ORM\EntityRepository;

class PresupuestoDetalleType extends AbstractType {

    private $type;
    private $id;
    public function __construct($type,$id)
    {
        $this->type= $type;
        $this->id= $id;
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
            $pres = ($this->id ) ? $this->id : '0';

            $builder->add('producto', 'entity', array(
                    'required' => true,
                    'placeholder' => 'Seleccionar Producto...',
                    'class' => 'AppBundle:Producto',
                    'query_builder' => function(EntityRepository $repository)use($pres){
                        $qb = $repository->createQueryBuilder('p')
                                ->innerJoin('p.presupuestos','d')
                                ->innerJoin('d.presupuesto','v')
                                ->where("v.id=".$pres);
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
            'data_class' => 'VentasBundle\Entity\PresupuestoDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_presupuestodetalle';
    }

}