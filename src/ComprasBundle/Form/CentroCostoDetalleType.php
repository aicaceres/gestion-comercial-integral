<?php

namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CentroCostoDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('centroCosto', 'entity', array(
                    'class' => 'ConfigBundle:CentroCosto',
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('a')
                                ->where('a.activo=1');
                    }
                ))
                ->add('costo');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ComprasBundle\Entity\CentroCostoDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'comprasbundle_centrocostodetalle';
    }

}