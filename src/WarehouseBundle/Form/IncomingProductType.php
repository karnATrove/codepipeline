<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use WarehouseBundle\Form\ProductType;

use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\IncomingProduct;

class IncomingProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('qty', IntegerType::class, array('label' => 'Qty'))
            ->add('model', HiddenType::class, array(
                'empty_data' => 'x',   # Intentionally blank
            ))
            ->add('product', EntityType::class, array(
                'class' => Product::class,
                'choice_label' => 'model',
                'placeholder' => 'Choose a model',
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => IncomingProduct::class,
        ));
    }
}
