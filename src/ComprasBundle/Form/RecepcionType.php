<?php
namespace ComprasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Form\ParametroType;
use ConfigBundle\Entity\ParametroRepository;

class RecepcionType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $CalifProv = array(
            'class'         => 'ConfigBundle:Parametro',
            'placeholder'   => 'Seleccionar...',
            'required'      =>false,
            'attr' => array('class' => 'uniformselect'),
            'label'         => 'Calificación: ',
            'query_builder' => function (ParametroRepository $repository) {
                return $qb = $repository->createQueryBuilder('p')
                    ->where('p.activo=1 and p.agrupador = :val ')
                    ->orderBy('p.numerico')    
                    ->setParameter('val', ParametroType::getTablaId($repository, 'calificacion-proveedor'));
            });
        
        $builder
            ->add('fechaEntrega','date',array('widget' => 'single_text', 'label'=>'Fecha Entrega:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('estado','hidden')
            ->add('obsRecepcion',null,array('label' => 'Observaciones:','required' => false))      
            ->add('generarnuevo','checkbox',array('mapped' => false,'required' => false)) 
            ->add('recibido','checkbox',array('mapped' => false,'required' => false)) 
            ->add('calificacionProveedor', 'entity', $CalifProv);                                                
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
        return 'comprasbundle_recepcion';
    }
}
