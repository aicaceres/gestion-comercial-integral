<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class NotaElectronicaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('tipoComprobante', 'entity', array('label' => 'Tipo Comprobante:',
                    'class' => 'ConfigBundle:AfipComprobante', 'required' => true,
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('c')
                                ->where('c.activo=1')
                                ->andWhere('c.valor like :param1')
                                ->orWhere('c.valor like :param2')
                                ->setParameter('param1', '%DEB%')
                                ->setParameter('param2', '%CRE%');
                    }
                ))
                ->add('puntoVenta')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\FacturaElectronica'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_notaelectronica';
    }

}