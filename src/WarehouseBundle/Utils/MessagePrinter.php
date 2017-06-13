<?php

namespace WarehouseBundle\Utils;


use Psr\Container\ContainerInterface;

class MessagePrinter
{
	private $container;

	/**
	 * MessagePrinter constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * print messages to session flash bag
	 *
	 * @param $messages
	 */
	public function printToFlashBag($messages)
	{
		foreach ($messages as $key=>$messageList){
			foreach ($messageList as $message) {
				$this->container->get('session')->getFlashBag()->add($key, $message);
			}
		}
	}
}