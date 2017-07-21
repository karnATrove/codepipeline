<?php

namespace WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
			->add('carrierId', ChoiceType::class, [
				'choices' => $options['carrier_list'],
				'choices_as_values' => true,
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
            'carrier_list' => [''=>'']
		]);
	}
}
