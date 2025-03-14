<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use VentasBundle\Form\ChequeType;

class CobroDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('tipoPago','hidden')
                ->add('moneda', 'entity', array(
                    'class' => 'ConfigBundle:Moneda',
                    'required' => true,
                    'label'=>'MONEDA: '
                ))
                ->add('importe')
                ->add('datosTarjeta', new CobroDetalleTarjetaType())
                ->add('chequeRecibido', new ChequeType())
                ->add('nroMovTransferencia', null, array('mapped'=> false))
                ->add('bancoTransferencia', 'entity', array('class' => 'ConfigBundle:Banco',
                    'mapped' =>false, 'label' => 'Banco:', 
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('b')
                                ->innerJoin('b.cuentas', 'c')
                                ->where('b.activo=1');
                    }
                ))
                ->add('cuentaTransferencia','entity',array('class' => 'ConfigBundle:CuentaBancaria',
                    'label' => 'Cuenta:','mapped' =>false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\CobroDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_cobrodetalle';
    }

}