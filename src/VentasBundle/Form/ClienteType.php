<?php
namespace VentasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ConfigBundle\Form\EventListener\AddLocalidadFieldSubscriber;
use ConfigBundle\Form\EventListener\AddProvinciaFieldSubscriber;
use ConfigBundle\Form\EventListener\AddPaisFieldSubscriber;

use ConfigBundle\Entity\ParametroRepository;
use ConfigBundle\Entity\EscalasRepository;
use ConfigBundle\Form\ParametroType;

class ClienteType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertyPathToLocalidad = 'localidad';
        $propertyPathToLocalidadTrabajo = 'localidadTrabajo';

        $builder
            ->addEventSubscriber(new AddLocalidadFieldSubscriber($propertyPathToLocalidad))
            ->addEventSubscriber(new AddProvinciaFieldSubscriber($propertyPathToLocalidad))
            ->addEventSubscriber(new AddPaisFieldSubscriber($propertyPathToLocalidad)); 
        $builder
            ->addEventSubscriber(new AddLocalidadFieldSubscriber($propertyPathToLocalidadTrabajo))
            ->addEventSubscriber(new AddProvinciaFieldSubscriber($propertyPathToLocalidadTrabajo))
            ->addEventSubscriber(new AddLocalidadFieldSubscriber($propertyPathToLocalidadTrabajo)); 
        
         $builder->add('nombre', 'text', array('label' => 'Nombre y Apellido:','required'=>true))
                 ->add('dni', 'text', array('label' => 'DNI:','required'=>true))
                 ->add('cuit', 'text', array('label' => 'CUIT:','required'=>true))
                 ->add('direccion', 'text', array('label' => 'Dirección:','required'=>false))
                 ->add('telefono', 'text', array('label' => 'Teléfonos:','required'=>false))
                 
                 ->add('observaciones', 'textarea', array('label' => 'Observaciones:','required'=>false))
                 ->add('email', 'email', array('label' => 'Email:','required'=>false))
                 ->add('saldoInicial',null,array('label' => 'Saldo Inicial Cta. Cte.:','required'=>false))    
                 ->add('limiteCredito',null,array('label' => 'Limite de Crédito:','required'=>false))  
                 ->add('ultVerificacionCuit', 'date', array('widget' => 'single_text', 'label' => 'Ult. Verif. CUIT:',
                    'format' => 'dd-MM-yyyy', 'required' => false))  
                 ->add('activo',null,array('label' => 'Activo:','required'=>false))
            
                 ->add('precioLista','entity',array('label'=>'Lista:',
                'class' => 'AppBundle:PrecioLista', 'required' =>true))   
                 ->add('formaPago','entity',array('label'=>'Forma de Pago:',
                'class' => 'ConfigBundle:FormaPago', 'required' =>false))   
                 ->add('transporte','entity',array('label'=>'Transporte:',
                'class' => 'ConfigBundle:Transporte', 'required' =>false))   
                 ->add('provinciaRentas','entity',array('label'=>'Provincia para Rentas:',
                'class' => 'ConfigBundle:Provincia', 'required' =>false))   
                ->add('trabajo', 'text', array('label' => 'Trabajo:','required'=>false))
                ->add('direccionTrabajo', 'text', array('label' => 'Dirección Trabajo:','required'=>false))
                ->add('telefonoTrabajo', 'text', array('label' => 'Tel. Trabajo:','required'=>false))
                
                 ;                       
        
        $optionsIva = array(
            'class'         => 'ConfigBundle:Parametro',
            'placeholder'   => 'Seleccionar...',
            'required'      =>true,
            'choice_label' => 'descripcion',
            'label'         => 'Categoría IVA:',
            'query_builder' => function (ParametroRepository $repository) {
                return $qb = $repository->createQueryBuilder('p')
                    ->where('p.boleano=1 and p.activo=1 and p.agrupador = :val ')
                    ->setParameter('val', ParametroType::getTablaId($repository, 'sit-impositiva'));
            });                       
        $builder->add('categoria_iva', 'entity', $optionsIva);
        $optionsDgr = array(
            'class'         => 'ConfigBundle:Escalas',
            'placeholder'   => 'Seleccionar...',
            'required'      =>false,          
             'choice_label' => 'nombre',
            'label'         => 'Categoría Rentas:',
            'query_builder' => function (EscalasRepository $repository) {
                return $qb = $repository->createQueryBuilder('e')
                    ->where("e.tipo='P' ");
            });
         $builder->add('categoriaRentas', 'entity', $optionsDgr);   
    }
  
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VentasBundle\Entity\Cliente'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ventasbundle_cliente';
    }
}
