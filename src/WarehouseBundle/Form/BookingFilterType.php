<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;


class BookingFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           // ->add('id', Filters\NumberFilterType::class)
            ->add('orderNumber', Filters\TextFilterType::class)
            ->add('orderReference', Filters\TextFilterType::class)
            //->add('orderType', Filters\NumberFilterType::class)
            ->add('orderType', ChoiceType::class, array(
                'choices' => array(''=>'') + array_flip(\WarehouseBundle\Utils\Booking::bookingOrderTypeList()),
                'choices_as_values' => true,
                'required' => false,
            ))
            //->add('carrierId', Filters\NumberFilterType::class)
            ->add('carrierId', ChoiceType::class, array(
                'choices' => array(''=>'') + array_flip(\WarehouseBundle\Utils\Booking::bookingCarrierList()),
                'choices_as_values' => true,
                'required' => false,
            ))
            //->add('skidCount', Filters\NumberFilterType::class)
            //->add('status', Filters\NumberFilterType::class)
            ->add('status', ChoiceType::class, array(
                'choices' => array(''=>'') + array_flip(\WarehouseBundle\Utils\Booking::bookingStatusList()),
                'choices_as_values' => true,
                'required' => false,
            ))
            ->add('futureship', Filters\DateFilterType::class)
            //->add('shipped', Filters\DateTimeFilterType::class)
            //->add('created', Filters\DateTimeFilterType::class)
            //->add('modified', Filters\DateTimeFilterType::class)
            
            ->add('pickingFlag', ChoiceType::class, array(
                'required' => FALSE,
                'choices' => array(
                    '' => '',
                    'Yes' => 1,
                    'No' => 0,
                ),
                'choices_as_values' => TRUE,
            ))
            /*
            ->add('contact', Filters\EntityFilterType::class, array(
                    'class' => 'WarehouseBundle\Entity\BookingContact',
                    'choice_label' => 'company',
            )) 
            ->add('files', Filters\EntityFilterType::class, array(
                    'class' => 'WarehouseBundle\Entity\BookingFile',
                    'choice_label' => 'type',
            )) 
            ->add('products', Filters\EntityFilterType::class, array(
                    'class' => 'WarehouseBundle\Entity\BookingProduct',
                    'choice_label' => 'id',
            )) 
            ->add('returns', Filters\EntityFilterType::class, array(
                    'class' => 'WarehouseBundle\Entity\BookingReturn',
                    'choice_label' => 'id',
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
