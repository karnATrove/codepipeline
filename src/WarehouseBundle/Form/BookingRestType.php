<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\BookingContact;

use WarehouseBundle\Form\ProductType;
use WarehouseBundle\Form\BookingProductRestType;
use WarehouseBundle\Form\BookingCommentRestType;

class BookingRestType extends AbstractType
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
                'choices' => array_flip(\WarehouseBundle\Utils\Booking::bookingOrderTypeList()),
                'choices_as_values' => true,
            ))
            ->add('carrierId')
            //->add('skidCount')
            ->add('status',ChoiceType::class,array(
                'choices' => array_flip(\WarehouseBundle\Utils\Booking::bookingStatusList()),
                'choices_as_values' => true,
            ))
            //->add('status')
            ->add('futureship')            
            ->add('products', CollectionType::class, array(
                'entry_type' => BookingProductRestType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('comments', CollectionType::class, array(
                'entry_type' => BookingCommentRestType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            
            ->add('contact', BookingContactRestType::class, array(
                'by_reference' => false,
            ))

            /*
            ->add('contact', CollectionType::class, array(
                'entry_type' => BookingContactRestType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
             
            ->add('contact', EntityType::class, array(
                'class' => BookingContact::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a name',
                'property' => 'name',
            ))*/
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
            'data_class' => 'WarehouseBundle\Entity\Booking',
            'csrf_protection'   => false,
        ));
    }

}
