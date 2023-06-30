<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

class StockAjusteType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $unidadnegocio = $options['attr']['unidadnegocio'];
        $formOptions = array(
            'class'         => 'AppBundle:Deposito',
            'placeholder'   => 'Seleccione Depósito',
            'label'         => 'Depósito:',
            'required'      => true,
            'query_builder' => function (EntityRepository $repository) use ($unidadnegocio) {
                $qb = $repository->createQueryBuilder('d')
                    ->innerJoin('d.unidadNegocio', 'u')
                    ->where('u.id = :unidadnegocio')
                    ->orderBy('d.nombre')
                    ->setParameter('unidadnegocio', $unidadnegocio);
                return $qb;
            }
        );
        $builder
            ->add('fecha','date',array('widget' => 'single_text', 'label'=>'Fecha Ajuste:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('deposito', 'entity', $formOptions)
            ->add('csv', 'file', array('mapped'=>false, 'required' => false, 'label'=>'Importar Items') )
            /*->add('deposito','entity',array('label'=>'Depósito:',
                'class' => 'AppBundle:Deposito', 'required' =>true))    */
            ->add('observaciones',null,array('label' => 'Observaciones:','required' => false))
            ->add('registrarAjuste','checkbox',array('label'=>'Registrar el ajuste ',
                'label_attr' => array('class'=>'infoicon')
                ,'mapped' => false,'required' => false))
            ->add('tipo','choice', array('label'=>'Movimientos de', 'mapped'=>false,'data'=>'+',
                  'choices'   => array( '+' => 'Ingreso','-' => 'Egreso'),'expanded'=>true))
          /*  ->add('detalles', 'collection', array(
                'type'           => new StockAjusteDetalleType(),
                'by_reference'   => false,
                'allow_delete'   => true,
                'allow_add'      => true,
                'prototype_name' => 'items',
                'attr'           => array(
                    'class' => 'row item'
                )))*/
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\StockAjuste'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_stockajuste';
    }
}
