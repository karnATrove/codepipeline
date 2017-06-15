<?php

namespace WarehouseBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;
use WarehouseBundle\Entity\IncomingStatus;
use WarehouseBundle\Form\ProductType;

use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\IncomingProductScan;

class IncomingProductScanType extends AbstractType
{
	private $locationSelection;
	private $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		$builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SET_DATA,
			function (\Symfony\Component\Form\FormEvent $event) use ($builder) {
				$form = $event->getForm();
				$child = $event->getData();

				$locationSelection = $this->getSelection();

				//IncomingProductScan
				if ($child instanceof IncomingProductScan) {
					// Do what ever you like with $child entity data
					$incoming = $child->getIncoming();
					// If not completed
					if ($incoming->getStatus()->getId() !== IncomingStatus::COMPLETED) {
						$form->add('qtyOnScan', IntegerType::class, ['label' => 'Qty', 'attr' => ['min' => 0]]);
						$form->add('locationId', ChoiceType::class, [
							'choices' => $locationSelection,
							'placeholder' => 'Select a location',
						]);
					} else {
						// Completed
						$form->add('qtyOnScan', HiddenType::class, []);
					}
				}
			}
		);

		$builder->setMethod('POST');
	}

	public function getSelection()
	{
		if ($this->locationSelection) {
			return $this->locationSelection;
		}
		$locations = $this->em->getRepository('WarehouseBundle:Location')->findAll();
		$selection = [];
		foreach ($locations as $location) {
			$selection[$location->printLocation()] = $location->getId();
		}
		ksort($selection);
		$this->locationSelection = $selection;
		return $selection;
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => IncomingProductScan::class,
			//'csrf_protection' => false,
		]);
	}
}
