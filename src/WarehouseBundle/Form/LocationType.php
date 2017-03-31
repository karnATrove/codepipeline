<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class LocationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('aisle',TextType::class,array('attr'=>array('placeholder'=>'Enter aisle location','required'=>'required')))
            ->add('row',TextType::class,array('attr'=>array('placeholder'=>'Enter row location','required'=>'required')))
            ->add('level',TextType::class,array('attr'=>array('placeholder'=>'Enter level location','required'=>'required')))
            //->add('onHand',IntegerType::class,array('attr'=>array('placeholder'=>'Enter qty on hand','min'=>1,'max'=>10000)))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'WarehouseBundle\Entity\Location'
        ));
    }
}
