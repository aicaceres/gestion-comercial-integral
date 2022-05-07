<?php
namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChequeType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id','hidden')
            ->add('valor','hidden')
            ->add('nroCheque',null,array('label' => 'Nº Cheque:','required'=>true))
            ->add('dador','text',array('label' => 'Dador:','required'=>true))
            ->add('fecha','date',array('widget' => 'single_text','label' => 'Fecha:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('banco','entity',array('class' => 'ConfigBundle:Banco',
                'label' => 'Banco:','required' =>false))
            ->add('sucursal',null,array('label' => 'Sucursal:','required' => false))
            ;
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
        return 'configbundle_cheque';
    }
}
