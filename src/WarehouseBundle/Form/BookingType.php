<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BookingType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderNumber')
            ->add('orderReference')
            ->add('orderType',ChoiceType::class,array(
                'choices' => array(''=>'') + array_flip(\WarehouseBundle\Utils\Booking::bookingOrderTypeList()),
                'choices_as_values' => true,
            ))
            ->add('carrierId',ChoiceType::class,array(
                'choices' => array(''=>'') + array_flip(\WarehouseBundle\Utils\Booking::bookingCarrierList()),
                'choices_as_values' => true,
            ))
            ->add('skidCount')
            ->add('status',ChoiceType::class,array(
                'choices' => array(''=>'') + array_flip(\WarehouseBundle\Utils\Booking::bookingStatusList()),
                'choices_as_values' => true,
            ))
            //->add('status')
            ->add('futureship')
            //->add('shipped')
            //->add('created')
            //->add('modified')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'WarehouseBundle\Entity\Booking'
        ));
    }
}
