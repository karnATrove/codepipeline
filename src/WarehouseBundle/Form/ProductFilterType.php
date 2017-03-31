<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

class ProductFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('id', Filters\NumberFilterType::class)
            ->add('model', Filters\TextFilterType::class,array('condition_pattern' => FilterOperands::STRING_STARTS))
            //->add('description', Filters\TextFilterType::class)
            ->add('qtyPerCarton', Filters\NumberFilterType::class)
            //->add('created', Filters\DateTimeFilterType::class)

            ->add('status',ChoiceType::class,array(
                'choices' => array(''=>'') + array_flip(\WarehouseBundle\Utils\Product::productStatusList()),
                'choices_as_values' => TRUE,
                'required' => FALSE,
            ))
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
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'status' => 1,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}
