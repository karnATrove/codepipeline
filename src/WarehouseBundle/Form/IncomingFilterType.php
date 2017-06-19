<?php

namespace WarehouseBundle\Form;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WarehouseBundle\Manager\IncomingStatusManager;
use WarehouseBundle\Manager\IncomingTypeManager;


class IncomingFilterType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', Filters\TextFilterType::class,
				['required' => false,
					'attr' => ['placeholder' => 'Name']
				])
			->add('type', ChoiceType::class, [
				'choices' => ['' => ''] + array_flip(IncomingTypeManager::incomingTypeList()),
				'choices_as_values' => true,
				'required' => false
			])
			->add('status', ChoiceType::class, [
				'choices' => ['' => ''] + array_flip(IncomingStatusManager::incomingStatusList()),
				'choices_as_values' => true,
				'required' => false
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
