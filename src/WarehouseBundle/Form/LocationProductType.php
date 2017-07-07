<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class LocationProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('location',EntityType::class,array(
                'attr'=>array('placeholder'=>'Select Location','required'=>'required'),
                'class' => 'WarehouseBundle:Location',
                'choice_label' => function($location) {
                    return $location->getAisle(). ' - '. $location->getRow(). ' - '. $location->getLevel();
                },
                'placeholder' => 'Select location',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                    ->orderBy('l.aisle,l.row + 0,l.level');
                },
            ))
            ->add('onHand',IntegerType::class,array('attr'=>array('placeholder'=>'Enter input qty','min'=>0,'max'=>10000)))
            ->add('staged',IntegerType::class,array('attr'=>array('placeholder'=>'Enter input qty','min'=>0,'max'=>10000)))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'WarehouseBundle\Entity\LocationProduct'
        ));
    }
}
