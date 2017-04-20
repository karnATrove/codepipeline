<?php
// src/AppBundle/EventListener/SearchIndexerSubscriber.php
namespace WarehouseBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use WarehouseBundle\Entity\Booking;

class BookingSubscriber implements EventSubscriber
{
	public function getSubscribedEvents()
	{
		return array(
			'postPersist',
			'postUpdate',
		);
	}

	public function postUpdate(LifecycleEventArgs $args)
	{
		$entity = $args->getEntity();
		if ($entity instanceof Booking) {
			$entityManager = $args->getEntityManager();
			// ... do something with the Product
		}
		$this->index($args);
	}

	public function postPersist(LifecycleEventArgs $args)
	{
		$this->index($args);
	}

	public function index(LifecycleEventArgs $args)
	{
		$entity = $args->getEntity();

		// perhaps you only want to act on some "Product" entity
		if ($entity instanceof Booking) {
			$entityManager = $args->getEntityManager();
			// ... do something with the Product
		}
	}
}