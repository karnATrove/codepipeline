<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;


class BookingReturnFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', Filters\NumberFilterType::class)
            ->add('created', Filters\DateTimeFilterType::class)
            ->add('modified', Filters\DateTimeFilterType::class)
            ->add('status', Filters\NumberFilterType::class)
        
            ->add('booking', Filters\EntityFilterType::class, array(
                    'class' => 'WarehouseBundle\Entity\Booking',
                    'choice_label' => 'orderNumber',
            )) 
            ->add('comments', Filters\EntityFilterType::class, array(
                    'class' => 'WarehouseBundle\Entity\BookingReturnComment',
                    'choice_label' => 'id',
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
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}
