<?php
namespace VentasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ConfigBundle\Form\EventListener\AddLocalidadFieldSubscriber;
use ConfigBundle\Form\EventListener\AddProvinciaFieldSubscriber;
use ConfigBundle\Form\EventListener\AddPaisFieldSubscriber;

use ConfigBundle\Entity\ParametroRepository;
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

        $builder
            ->addEventSubscriber(new AddLocalidadFieldSubscriber($propertyPathToLocalidad))
            ->addEventSubscriber(new AddProvinciaFieldSubscriber($propertyPathToLocalidad))
            ->addEventSubscriber(new AddPaisFieldSubscriber($propertyPathToLocalidad)); 
        
         $builder->add('nombre', 'text', array('label' => 'Nombre y Apellido:','required'=>true))
                 ->add('dni', 'text', array('label' => 'DNI:','required'=>true))
                 ->add('cuit', 'text', array('label' => 'CUIT:','required'=>true))
                 ->add('direccion', 'text', array('label' => 'Dirección:','required'=>false))
                 ->add('telefono', 'text', array('label' => 'Teléfono:','required'=>false))
                 ->add('celular', 'text', array('label' => 'Celular:','required'=>false))
                 ->add('observaciones', 'textarea', array('label' => 'Observaciones:','required'=>false))
                 ->add('email', 'email', array('label' => 'Email:','required'=>false))
                 ->add('saldoInicial',null,array('label' => 'Saldo Inicial Cta. Cte.:','required'=>false))    
                 ->add('activo',null,array('label' => 'Activo:','required'=>false))
                 ->add('consumidorFinal')
                 ->add('precioLista','entity',array('label'=>'Lista:',
                'class' => 'AppBundle:PrecioLista', 'required' =>true))   
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
