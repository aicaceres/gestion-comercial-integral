<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class BancoMovimientoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $banco = $data->getBanco()->getId();
        $builder
            ->add('nroMovimiento',null,array('label' => 'N° de movimiento:'))
            ->add('fechaCarga','date',array('widget' => 'single_text','label' => 'Fecha Carga:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('fechaAcreditacion','date',array('widget' => 'single_text','label' => 'Acreditación:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('banco','entity',array(
                  'class' => 'ConfigBundle:Banco', 'required' =>true
                )
              )
            ->add('conciliado',null,array('label' => 'Conciliado:','required'=>false))
            ->add('tipoMovimiento', 'entity', array(
                    'class' => 'ConfigBundle:BancoTipoMovimiento',
                    'choice_label' => 'selectText',
                    'required' => true
                ))
            ->add('cuenta', 'entity', array(
                    'class' => 'ConfigBundle:CuentaBancaria',
                    'choice_label' => 'nroCuenta',
                    'required' => true,
                    'query_builder' => function(EntityRepository $repository) use ($banco){
                        return $qb = $repository->createQueryBuilder('c')
                                ->where("c.banco =".$banco)
                                ->orderBy("c.nroCuenta", "ASC");
                    }
                ))
            ->add('importe', null, array('label' => 'Importe:'))
            ->add('observaciones', 'textarea', array('label' => 'Observaciones:', 'required' =>false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\BancoMovimiento'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_bancomovimiento';
    }
}