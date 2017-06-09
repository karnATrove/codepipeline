<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use WarehouseBundle\Manager\IncomingStatusManager;
use WarehouseBundle\Manager\IncomingTypeManager;


class IncomingFilterType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', Filters\NumberFilterType::class)
			->add('type', ChoiceType::class, [
				'choices' => ['' => ''] + array_flip(IncomingTypeManager::incomingTypeList()),
				'choices_as_values' => true,
			])
			->add('name', Filters\TextFilterType::class)
			->add('eta', Filters\DateFilterType::class)
			->add('scheduled', Filters\DateFilterType::class)
			->add('arrived', Filters\DateFilterType::class)
			->add('status', ChoiceType::class, [
				'choices' => ['' => ''] + array_flip(IncomingStatusManager::incomingStatusList()),
				'choices_as_values' => true,
			]);
		$builder->setMethod("GET");
	}

	public function getBlockPrefix()
	{
		return null;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'allow_extra_fields' => true,
			'csrf_protection' => false,
			'validation_groups' => ['filtering'] // avoid NotBlank() constraint-related message
		]);
	}
}
