<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BookingFileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('filepath', FileType::class, array('label' => 'Document'))
            ->add('type',ChoiceType::class,array(
                'choices' => array(
                    'Label(s)' => 'LABEL',
                    'Bill of Lading' => 'BOL',
                    'Packing List' => 'PACK',
                    'Other' => 'OTHER',
                ),
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
            'data_class' => 'WarehouseBundle\Entity\BookingFile',
        ));
    }
}
