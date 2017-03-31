<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use WarehouseBundle\Form\IncomingFileType;

class IncomingType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, array(
                'choices' => array_flip(\WarehouseBundle\Utils\Incoming::incomingTypeList()),
                'choices_as_values' => true,
            ))
            ->add('name')
            ->add('eta')
            ->add('scheduled')
            ->add('arrived')
            ->add('status', ChoiceType::class, array(
                'choices' => array(''=>'') + array_flip(\WarehouseBundle\Utils\Incoming::incomingStatusList()),
                'choices_as_values' => true,
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'WarehouseBundle\Entity\Incoming',
        ));
    }
}
