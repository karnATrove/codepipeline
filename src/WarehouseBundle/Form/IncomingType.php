<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use WarehouseBundle\Entity\IncomingType as entityIncomingType;
use WarehouseBundle\Entity\IncomingStatus;
use WarehouseBundle\Form\IncomingFileType;

class IncomingType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('type', EntityType::class, [
				'class' => EntityIncomingType::class,
				'choice_label' => 'detail',
			])
			->add('name')
			->add('eta', DateTimeType::class,
				[
					'widget' => 'single_text',
					'format' => 'yyyy-MM-dd'
				])
			->add('scheduled', DateTimeType::class,
				[
					'widget' => 'single_text',
					'format' => 'yyyy-MM-dd h:mm:ss a',
					'required' => false
				])
//			->add('arrived', TextType::class)
			->add('status', EntityType::class, [
				'class' => IncomingStatus::class,
				'choice_label' => 'detail',
			]);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => 'WarehouseBundle\Entity\Incoming',
		]);
	}
}
