<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use AppBundle\Entity\ProductoRepository;

class PrecioType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
     public function buildForm(FormBuilderInterface $builder, array $options) {
        $oplista = $options['data']->getPrecioLista()->getId();
        $builder
                ->add('precioLista')
                ->add('precio',null,array('label'=>'Precio'));
        if ($options['data']->getId()) {
            $builder->add('producto');
        } else {
            $builder->add('producto', 'entity', array(
                'required' => true,
                'placeholder' => 'Seleccionar Producto...',
                'class' => 'AppBundle\\Entity\\Producto',
                'query_builder' => function(ProductoRepository $em) use ($oplista) {
            return $em->getProductosSinPrecio($oplista);
        },));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Precio'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_precio';
    }
}
