<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\BookingProduct;

class BookingProductRestType extends AbstractType
{

    public function __construct() {
        dump(get_defined_vars());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('qty', IntegerType::class, array('label' => 'Quantity'))
            ->add('product', EntityType::class, array(
                'class' => Product::class,
                //'multiple' => true,
                //'expanded' => false,
                'choice_label' => 'model',
                //'placeholder' => 'Choose a model',
//                'property' => 'model',
                //'query_builder' => function (EntityRepository $er) {
                 //   return $er->createQueryBuilder('p')
                 //       ->orderBy('p.model', 'ASC');
                //},
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => BookingProduct::class,
            'csrf_protection'   => false,
        ));
    }
}
