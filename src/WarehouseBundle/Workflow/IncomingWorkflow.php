<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 4:24 PM
 */

namespace WarehouseBundle\Workflow;


use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingStatus;
use WarehouseBundle\Exception\WorkflowException\WorkflowException;
use WarehouseBundle\Manager\IncomingManager;

class IncomingWorkflow extends BaseWorkflow
{
	private $locationProductManager;
	private $incomingStatusManager;
	private $incomingManager;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->locationProductManager = $container->get('warehouse.manager.location_product_manager');
		$this->incomingStatusManager = $container->get('warehouse.manager.incoming_status_manager');
		$this->incomingManager = $container->get('warehouse.manager.incoming_manager');
	}

	/**
	 * Sets the Incoming as complete.
	 *
	 * @param Incoming $incoming
	 *
	 * @return bool
	 * @throws WorkflowException
	 */
	public function setIncomingComplete(Incoming $incoming)
	{
		if (!IncomingManager::isActive($incoming)) {
			$msg = "Unable to mark the container as completed because it is not in an active status";
			throw new WorkflowException($msg);
		}
		# Add items to inventory - We use this loop because some items scanned may not be in the container
		foreach ($incoming->getIncomingScannedProducts() as $incomingScannedProduct) {
			$product = $incomingScannedProduct->getProduct();
			$location = $incomingScannedProduct->getLocation();
			$locationProduct = $this->locationProductManager->findOneByProductAndLocation($product, $location);

			if (!$locationProduct) {
				$this->locationProductManager->createLocationProductByIncomingProductScan($incomingScannedProduct, $this->entityManager);
			} else {
				$locationProduct->setModified(new \DateTime('now'));
				$locationProduct->setOnHand($locationProduct->getOnHand());
				$this->locationProductManager->updateLocationProduct($locationProduct, $this->entityManager);
			}
		}

		# Change the Incoming status to completed.
		$incoming->setStatus($this->incomingStatusManager->find(IncomingStatus::COMPLETED));
		$this->incomingManager->updateIncoming($incoming, $this->entityManager);
		$this->entityManager->flush();
		$this->container->get('session')->getFlashBag()->add('success', 'Successfully set Incoming container to Completed.');
		return TRUE;
	}
}