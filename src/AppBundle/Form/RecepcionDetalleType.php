<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecepcionDetalleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
        public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $builder->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
                ->add('bulto', null, array('required' => false))
                ->add('cantidadxBulto', null, array('required' => false));                
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\DespachoDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_recepciondetalle';
    }
}
