<?php

namespace VentasBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Entity\ParametroRepository;
use ConfigBundle\Form\ParametroType;

class NotaDebCredType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $tipoComp = $options['attr']['tipoComp'];
        $data = $options['data'];
        $tipoNota = $data->getSigno() == '+' ? ['DEB','NDE'] : ['CRE','NCE'];
        $builder
            ->add('signo', 'hidden')
            ->add('precioLista', 'entity', array('label' => 'Lista de Precios:',
                'class' => 'AppBundle:PrecioLista', 'required' => true))
            ->add('moneda', 'entity', array(
                'class' => 'ConfigBundle:Moneda',
                'attr' => array('id' => 'ventasbundle_moneda'),
                'required' => true, 'label' => 'MONEDA: '
            ))
            ->add('concepto', 'text', array('label' => 'Concepto Adicional:', 'required' => false))
            ->add('descuentoRecargo', null, array('attr' => array('required' => true)))
            ->add('periodoAsocDesde', 'date', array('widget' => 'single_text', 'label' => 'Período Desde:',
                'format' => 'dd-MM-yyyy', 'required' => false))
            ->add('periodoAsocHasta', 'date', array('widget' => 'single_text', 'label' => 'Hasta:',
                'format' => 'dd-MM-yyyy', 'required' => false))
            ->add('categoriaIva', 'hidden')
            ->add('percepcionRentas', 'hidden')
            ->add('detalles', 'collection', array(
                'type' => new NotaDebCredDetalleType(),
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype_name' => 'items',
                'attr' => array(
                    'class' => 'row item'
            )))
            ->add('cobroDetalles', 'collection', array(
                'label_attr' => array('style' => 'display:none'),
                'type' => new CobroDetalleType(),
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype_name' => 'citems',
                'attr' => array(
                    'class' => 'row citem'
            )))
            ->add('nroMovTransferencia', null, array('mapped'=> false))
            ->add('bancoTransferencia', 'entity', array('class' => 'ConfigBundle:Banco',
                'mapped' =>false, 'label' => 'Banco:', 
                'query_builder' => function(EntityRepository $repository) {
                    return $qb = $repository->createQueryBuilder('b')
                            ->innerJoin('b.cuentas', 'c')
                            ->where('b.activo=1');
                }
            ))
            ->add('cuentaTransferencia','entity',array('class' => 'ConfigBundle:CuentaBancaria',
                'label' => 'Cuenta:','mapped' =>false))    
            ->add('rechazado','choice', array('mapped' => false,
              'label' => 'Rechazado:','expanded' => false, 'choices' => array('N' => 'No','S'=> 'Si') ))
            ->add('tipoComprobante', 'entity', array('label' => 'Tipo Comprobante:',
                'class' => 'ConfigBundle:AfipComprobante', 'required' => true,
                'query_builder' => function(EntityRepository $repository) use ($tipoNota, $tipoComp) {
                    if ($tipoComp) {
                        return $qb = $repository->createQueryBuilder('c')
                            ->where('c.id=:param1')
                            ->setParameter('param1', $tipoComp);
                    }
                    else {
                        return $qb = $repository->createQueryBuilder('c')
                            ->where('c.activo=1')
                            ->andWhere('c.valor like :param1')
                            ->orWhere('c.valor like :param2')
                            ->setParameter('param1', '%' . $tipoNota[0] . '%')
                            ->setParameter('param2', '%' . $tipoNota[1] . '%');
                    }
                }
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\NotaDebCred',
            'error_bubbling' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ventasbundle_notadebcred';
    }

}