<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ScanType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('model',TextType::class,array('attr'=>array('placeholder'=>'Enter SKU','data-validate-words'=>'7','data-validate-length-range'=>'3','required'=>'required')))
            ->add('description',TextType::class,array('attr'=>array('placeholder'=>'Enter product description')))
            ->add('qtyPerCarton',IntegerType::class,array('attr'=>array('placeholder'=>'How many per box','min'=>1,'max'=>1000)))
            ->add('length',NumberType::class,array('required'=>FALSE,'scale'=>2,'attr'=>array('min'=>0,'max'=>1000,'placeholder'=>'0.00')))
            ->add('width',NumberType::class,array('required'=>FALSE,'scale'=>2,'attr'=>array('min'=>0,'max'=>1000,'placeholder'=>'0.00')))
            ->add('height',NumberType::class,array('required'=>FALSE,'scale'=>2,'attr'=>array('min'=>0,'max'=>1000,'placeholder'=>'0.00')))
            ->add('weight',NumberType::class,array('required'=>FALSE,'scale'=>2,'attr'=>array('min'=>0,'max'=>1000,'placeholder'=>'0.00')))
            ->add('dimUnits', ChoiceType::class, array(
                'choices' => array_flip(\WarehouseBundle\Utils\Product::productDimensionUnitList()),
                'choices_as_values' => TRUE,
            ))
            ->add('weightUnits', ChoiceType::class, array(
                'choices' => array_flip(\WarehouseBundle\Utils\Product::productWeightUnitList()),
                'choices_as_values' => TRUE,
            ))
            ->add('status', ChoiceType::class, array(
                'choices' => array_flip(\WarehouseBundle\Utils\Product::productStatusList()),
                'choices_as_values' => TRUE,
            ))
            //->add('created',DateTimeType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'WarehouseBundle\Entity\Product'
        ));
    }
}
