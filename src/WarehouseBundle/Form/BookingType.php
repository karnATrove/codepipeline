<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WarehouseBundle\Entity\Carrier;

class BookingType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('orderNumber')
			->add('orderReference')
			->add('orderType', ChoiceType::class, [
				'choices' => ['' => ''] + array_flip(\WarehouseBundle\Utils\Booking::bookingOrderTypeList()),
				'choices_as_values' => true,
			])
			->add('carrier', EntityType::class, [
			    'class' => Carrier::class,
                'choice_label' => 'name',
			])
			->add('skidCount')
			->add('status', ChoiceType::class, [
				'choices' => ['' => ''] + array_flip(\WarehouseBundle\Utils\Booking::bookingStatusList()),
				'choices_as_values' => true,
			])
			->add('futureship', DateTimeType::class,
				[
					'widget' => 'single_text',
					'format' => 'yyyy-MM-dd',
					'required' => false
				]);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => 'WarehouseBundle\Entity\Booking',
		]);
	}
}
