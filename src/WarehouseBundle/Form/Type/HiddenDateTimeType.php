<?php

namespace WarehouseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use WarehouseBundle\Form\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
//use Symfony\Component\Form\Extension\Core\DataTransformer\DateTi‌​meToStringTransforme‌​r;

class HiddenDateTimeType extends AbstractType
{

    public function __construct()
    {
    }

    public function getName()
    {
        return 'hidden_datetime';
    }

    public function getParent()
    {
        return HiddenType::class;
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }   

     public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $transformer = new DateTimeToStringTransformer();
        $builder->addModelTransformer($transformer);
    }   

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);   
        $resolver->setDefaults(array());
    }
}