<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ChequeLoteType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id','hidden')
            ->add('nroCheque',null,array('label' => 'Nº Cheque:','required'=>true))
            ->add('cantidad',null,array('label' => 'Cantidad:','mapped'=>false ,'required'=>true))
            ->add('fecha','date',array('widget' => 'single_text','label' => 'Fecha:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('tipoCheque','choice', array( 'expanded'=>false,
                'choices' => array( 'NORMAL' => 'Normal','DIFERIDO' => 'Diferido','ELECTRONICO' => 'Electrónico')
              ))
            ->add('observaciones',null,array('label' => 'Observaciones:','required' => false))
            ->add('banco', 'entity', array(
                    'class' => 'ConfigBundle:Banco',
                    'label' => 'Banco:',
                    'required' => true,
                    'query_builder' => function(EntityRepository $repository) {
                        return $qb = $repository->createQueryBuilder('c')
                                ->where("c.nombre NOT LIKE '%RETENCION%'")
                                ->andWhere("c.activo=1")
                                ->orderBy("c.nombre", "ASC");
                    }
                ))
            ->add('cuenta', 'entity', array(
                    'class' => 'ConfigBundle:CuentaBancaria',
                    'choice_label' => 'nroCuenta',
                    'label' => 'Cuenta:',
                    'required' => true
                ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'ConfigBundle\Entity\Cheque'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_chequelote';
    }
}
