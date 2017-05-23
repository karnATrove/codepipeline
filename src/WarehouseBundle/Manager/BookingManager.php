<?php

use Doctrine\ORM\EntityManagerInterface;

class BookingManager
{
	private $container;
	private $bookingRepository;
	private $em;

	/**
	 * BookingManager constructor.
	 *
	 * @param \Symfony\Component\DependencyInjection\Container $container
	 */
	public function __construct(\Symfony\Component\DependencyInjection\Container $container)
	{
		$this->container = $container;
		$this->em = $container->get('doctrine')->getManager();
		$this->bookingRepository = $this->em->getRepository('WarehouseBundle:Booking');
	}

}