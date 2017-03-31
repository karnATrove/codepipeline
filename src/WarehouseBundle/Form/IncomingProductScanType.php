<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;
use WarehouseBundle\Form\ProductType;

use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\IncomingProductScan;

class IncomingProductScanType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SET_DATA,
        function (\Symfony\Component\Form\FormEvent $event) use ($builder)
            {
                $form = $event->getForm();
                $child = $event->getData();

                //IncomingProductScan 
                if ($child instanceof IncomingProductScan) {
                    // Do what ever you like with $child entity data
                    $incoming = $child->getIncoming();

                    //$this->get('app.incoming')->isComplete($incoming->getStatus());
                    // If not completed
                    if ($incoming->getStatus() !== 3) {
                        $form->add('qtyOnScan', IntegerType::class, array('label' => 'Qty','attr'=>array('min'=>0)));
                        $form->add('location', EntityType::class, array(
                            'class' => 'WarehouseBundle:Location',
                            'placeholder' => 'Select a location',
                            'mapped' => true,
                            'choice_label' => function($location) {
                                return $location->getAisle(). ' - '. $location->getRow(). ' - '. $location->getLevel();
                            }
                        ));
                    } else {
                        // Completed
                        $form->add('qtyOnScan', HiddenType::class, array());
                        //$form->add('location', HiddenType::cla
                    }
                }
            }
        );

        $builder
            ->setMethod('POST')
            //->add('qtyOnScan', IntegerType::class, array('label' => 'Qty','attr'=>array('min'=>0)))
            /*->add('location', EntityType::class, array(
                'class' => 'WarehouseBundle:Location',
                'placeholder' => 'Select a location',
                'mapped' => true,
                'choice_label' => function($location) {
                    return $location->getAisle(). ' - '. $location->getRow(). ' - '. $location->getLevel();
                }
            ))*/
           // ->add('aisle', TextType::class, array('label' => 'Aisle','mapped' => false))
            //->add('row', TextType::class, array('label' => 'Row','mapped' => false))
           // ->add('level', TextType::class, array('label' => 'Level','mapped' => false))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => IncomingProductScan::class,
        ));
    }
}
