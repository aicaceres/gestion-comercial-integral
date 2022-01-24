<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class VentaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $type = $options['attr']['type'];
            $builder                
                ->add('nroOperacion','hidden')
                ->add('estado','hidden')
                ->add('formaPago','entity', array('class' => 'ConfigBundle:FormaPago', 'required' => true, 'label' => 'FORMA DE PAGO: '))
                ->add('precioLista','entity', array('class' => 'AppBundle:PrecioLista', 
                'required' => true, 'label' => 'LISTA DE PRECIOS: ','choice_label'=>'nombre'))
                ->add('transporte','entity', array('class' => 'ConfigBundle:Transporte', 
                'required' => false, 'label' => 'TRANSPORTE: '))
                ->add('moneda','entity', array('class' => 'ConfigBundle:Moneda', 
                'required' => true, 'label' => 'MONEDA: '))

                ->add('detalles', 'collection', array(
                    'type' => new VentaDetalleType($type),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item',                        
                )))
               
;
        if($type=='new'){
            // en render de nueva venta solo traer cliente por defecto 
            $data = $options['data'];  
            $cliente = $data->getCliente()->getId();
            $builder->add('cliente', 'entity', array('required' => true, 'attr' => array('class' => 'tabbable'),
                    'class' => 'VentasBundle:Cliente', 'label' => 'CLIENTE: ',
                    'query_builder' => function(EntityRepository $repository) use ($cliente){
                        return $qb = $repository->createQueryBuilder('c')                                
                                ->where("c.id=".$cliente);
                    }
                )); 
        }else if($type=='create'){
            // al crear traer objeto completo para match del cliente si fue modificado
            $builder->add('cliente', 'entity', array('class' => 'VentasBundle:Cliente', 
                'required' => true)) ;
        }
                
    }    

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\Venta'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_venta';
    }

}