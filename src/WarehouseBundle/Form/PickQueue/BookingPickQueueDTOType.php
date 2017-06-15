<?php

namespace WarehouseBundle\Form\PickQueue;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\DTO\Booking\PickQueue\PickQueueDTO;
use WarehouseBundle\DTO\Booking\PickQueue\PickQueueItemDTO;
use WarehouseBundle\DTO\Booking\PickQueue\PickQueueItemLocationDTO;

class BookingPickQueueDTOType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MyFormDTO $myDTO */
        $builder
            ->setMethod('POST')
        ;

        # Add locations
        $builder->add('itemLocations', CollectionType::class, array(
            'entry_type' => BookingPickQueueItemLocationDTOType::class,
            'entry_options' => array(
                'attr' => array('class'=>''),
            ),
        ));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'data_class' => PickQueueItemDTO::class,
            //'always_empty' => true,
        ));
    }
}
