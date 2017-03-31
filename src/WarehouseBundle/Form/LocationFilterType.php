<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;


class LocationFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           // ->add('id', Filters\NumberFilterType::class)
            ->add('aisle', Filters\TextFilterType::class)
            ->add('row', Filters\TextFilterType::class)
            ->add('level', Filters\TextFilterType::class)
            /*
            ->add('products', Filters\EntityFilterType::class, array(
                    'class' => 'WarehouseBundle\Entity\Product',
                    'choice_label' => 'model',
            )) 
            */ 
        ;
        $builder->setMethod("GET");


    }

    public function getBlockPrefix()
    {
        return null;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}
