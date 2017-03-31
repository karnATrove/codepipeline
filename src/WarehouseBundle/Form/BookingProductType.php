<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use WarehouseBundle\Entity\BookingProduct;

class BookingProductType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            //->add('pickedQty', IntegerType::class, array('label' => 'Picked Qty'))
            ->add('status', ChoiceType::class, array(
                'choices' => array_flip(\WarehouseBundle\Utils\Booking::bookingProductStatusList()),
                'choices_as_values' => true,
            ))
        ;

        # Add locations
        $builder->add('location',CollectionType::class, array('mapped' => FALSE));
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $bookingProduct = $event->getData();
            $form = $event->getForm();

            // Find locations
            $locations = $bookingProduct->getProduct()->getLocations();
            foreach($locations as $productLocation) {
                $form->get('location')->add($productLocation->getId(), IntegerType::class,[
                    'label' => $productLocation->getLocation()->getAisle(). ' - '. $productLocation->getLocation()->getRow(). ' - '. $productLocation->getLocation()->getLevel(),
                    'mapped'=>false,
                    'data' => 0,
                    'attr' => [
                        'min' => 0,
                        'max' => min($bookingProduct->getQty(),$productLocation->getOnHand()),
                        'class' => 'location_pick_qty',
                    ],
                ]);
            }
        });
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => BookingProduct::class,
        ));
    }
}
