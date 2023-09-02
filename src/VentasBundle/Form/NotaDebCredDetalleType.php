<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ProductoRepository;
use Doctrine\ORM\EntityRepository;

class NotaDebCredDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:',
                'attr' => array('onchange' => 'actualizarImportes()')
            ))
            //->add('bulto', null, array('required' => false))
            //->add('cantidadxBulto', null, array('required' => false))
            ->add('textoComodin', 'text', array('required' => false))
            ->add('precio', 'hidden')
            ->add('alicuota', 'hidden')
        ;
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