<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class FacturaAlicuotaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('netoGravado', null, array('required' => true, 'label' => 'Neto Gravado:'))
            ->add('liquidado', null, array('required' => true, 'label' => 'IVA:'))
            ->add('afipAlicuota', 'entity', array('required' => true,
                'class' => 'ConfigBundle:AfipAlicuota',
                'query_builder' => function(EntityRepository $repository) {
                    return $qb = $repository->createQueryBuilder('a')
                        ->where('a.activo=1');
                }
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ComprasBundle\Entity\FacturaAlicuota'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_facturaalicuota';
    }

}