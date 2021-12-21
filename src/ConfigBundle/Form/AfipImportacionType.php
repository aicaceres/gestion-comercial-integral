<?php

namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AfipImportacionType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('periodo', null, array('label' => 'Periodo:', 'required' => true))
        //->add('file', 'file', array('label' => 'Archivo:', 'required' => true, 'mapped' => false))
        //->add('descripcion', null, array('required' => false, 'attr' => array('rows' => '1')))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\AfipImportacionBuffets'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'configbundle_afipimportacionbuffets';
    }

}