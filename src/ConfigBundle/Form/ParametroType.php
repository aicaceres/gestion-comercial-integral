<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ConfigBundle\Entity\ParametroRepository;

class ParametroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', 'text', array('label' => 'Abreviatura:','required'=>true))
            ->add('descripcion', 'text', array('label' => 'Descripción:','required'=>false))
            ->add('activo',null,array('required'=>false));
        $slug = $options['attr']['slug'];        
        if ($slug == 'sit-impositiva') {
            $builder->add('boleano','choice', array('label'=>'Situación ante:',
                          'choices'   => array( 1 => 'I.V.A.',0 => 'D.G.R.'),'expanded'=>true));
            $builder->add('numerico','choice', array('label'=>'Emite Factura:',
                          'choices'   => array( '1.00' => 'A', '0.00' => 'B' )));
        }        
        if ($slug == 'calificacion-proveedor') {
            $builder->add('numerico',null, array('label'=>'Orden:'));
        }        
    }
    
    public static function getTablaId(ParametroRepository $repository,$table){
        $tablaid = $repository->createQueryBuilder('g')->select('g.id')
                                      ->where('g.nombre = :tabla ')
                                      ->setParameter('tabla',$table)
                                      ->getQuery()->getSingleScalarResult();
        return $tablaid;
    }    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Parametro'
        ));
    }
    public function getName()
    {
        return 'configbundle_parametrotype';
    }
}
