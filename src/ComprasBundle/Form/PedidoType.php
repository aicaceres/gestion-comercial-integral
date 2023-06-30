<?php
namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PedidoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fechaPedido','date',array('widget' => 'single_text', 'label'=>'Fecha Pedido:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('fechaEntrega','date',array('widget' => 'single_text', 'label'=>'Entrega Estimada:',
                'format' => 'dd-MM-yyyy', 'required' => false))
            ->add('estado','hidden')
            ->add('descuentos', 'text', array('read_only' => true, 'attr'  => array('class' => 'hidden', 'autocomplete'=>'off')))
            ->add('montoDescuento', null, array('read_only' => true, 'attr'  => array('autocomplete'=>'off')))
            ->add('montoIva', null, array('read_only' => true, 'attr'  => array('autocomplete'=>'off')))
            ->add('proveedor','entity',array('label'=>'Proveedor:',
                'class' => 'ComprasBundle:Proveedor', 'required' =>true,
                'attr'  => array('class' => 'uniformselect'),
                'query_builder'=> function(EntityRepository $repository){
                    return $qb = $repository->createQueryBuilder('p')
                            ->where('p.activo=1')
                            ->orderBy('p.nombre');
                    }
                ))
            ->add('cerrado','checkbox',array('mapped' => false,'required' => false))
        ;
        $opunidneg = $options['data']->getUnidadNegocio()->getId();
        $builder->add('deposito', 'entity', array(
               'required' => true, 'label' => 'Depósito:', 'choice_label'=>'empresaUnidadDeposito',
                'class' => 'AppBundle\\Entity\\Deposito',
                'query_builder' => function(EntityRepository $repository) use ($opunidneg) {
            $qb = $repository->createQueryBuilder('deposito')
                    ->innerJoin('deposito.unidadNegocio', 'unidadNegocio')
                    ->where('unidadNegocio.id = :unidadNegocio')
                    ->andWhere('deposito.central = 1')
                    ->setParameter('unidadNegocio', $opunidneg);
                return $qb;
          },));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ComprasBundle\Entity\Pedido'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'comprasbundle_pedido';
    }
}
