<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\EventListener\AddDepositoFieldSubscriber;
use AppBundle\Form\EventListener\AddUnidadNegocioFieldSubscriber;
use Doctrine\ORM\EntityRepository;

class PedidoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertyPathToDeposito = 'depositoDestino';

        $builder
            ->addEventSubscriber(new AddDepositoFieldSubscriber($propertyPathToDeposito))
            ->addEventSubscriber(new AddUnidadNegocioFieldSubscriber($propertyPathToDeposito));   
        
        $builder
            ->add('fechaPedido','date',array('widget' => 'single_text', 'label'=>'Fecha Pedido:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('fechaEntrega','date',array('widget' => 'single_text', 'label'=>'Fecha Entrega:',
                'format' => 'dd-MM-yyyy', 'required' => false))
            ->add('estado','hidden',array('label' => 'Estado:'))

            ->add('cerrado','checkbox',array('mapped' => false,'required' => false))                
        ;

        $opunidneg = $options['data']->getUnidadNegocio()->getId();
        $builder->add('depositoOrigen', 'entity', array(
                'required' => true, 'label' => 'Origen del Pedido:',
                'placeholder' => 'Seleccionar Depósito de origen...',
                'class' => 'AppBundle\\Entity\\Deposito',
                'query_builder' => function(EntityRepository $repository) use ($opunidneg) {
            $qb = $repository->createQueryBuilder('deposito')
                    ->innerJoin('deposito.unidadNegocio', 'unidadNegocio')
                    ->where('unidadNegocio.id = :unidadNegocio')
                    ->orderBy('deposito.central','desc')    
                    ->setParameter('unidadNegocio', $opunidneg);
                return $qb;
          },));  
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Pedido'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_pedido';
    }
}