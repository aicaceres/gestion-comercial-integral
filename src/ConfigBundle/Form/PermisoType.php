<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PermisoType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('route',null,array('label' => 'Nombre de ruta:'))
                ->add('text',null,array('label' => 'Descripción:'))
                ->add('orden',null,array('label' => 'Orden:'))
                ->add('padre','entity',array(
                    'class' => 'ConfigBundle:Permiso',
                    'choice_label' => 'text','required' => false,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $em) {
                        return $em->createQueryBuilder('p')->where("p.padre IS NULL")
                                ->orderBy('p.route', 'ASC'); },'label'=>'Padre:'))
                ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_permiso';
    }
}