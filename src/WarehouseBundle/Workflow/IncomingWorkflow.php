<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 4:24 PM
 */

namespace WarehouseBundle\Workflow;


use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\DTO\AjaxResponse\AjaxCommandDTO;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingStatus;
use WarehouseBundle\Exception\WorkflowException\WorkflowException;
use WarehouseBundle\Manager\IncomingManager;
use WarehouseBundle\Manager\IncomingProductScanManager;
use WarehouseBundle\Manager\IncomingStatusManager;
use WarehouseBundle\Manager\LocationProductManager;

class IncomingWorkflow extends BaseWorkflow
{
	private $locationProductManager;
	private $incomingStatusManager;
	private $incomingManager;
	private $templating;
	private $incomingProductScanManager;

	/**
	 * IncomingWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->locationProductManager = $container->get(LocationProductManager::class);
		$this->incomingStatusManager = $container->get(IncomingStatusManager::class);
		$this->incomingManager = $container->get(IncomingManager::class);
		$this->incomingProductScanManager = $container->get(IncomingProductScanManager::class);
		$this->templating = $container->get('templating');
	}

	/**
	 * Sets the Incoming as complete.
	 *
	 * @param Incoming $incoming
	 *
	 * @throws WorkflowException
	 */
	public function setIncomingComplete(Incoming $incoming, $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		if (!IncomingManager::isActive($incoming)) {
			$msg = "Unable to mark the container as completed because it is not in an active status";
			throw new WorkflowException($msg);
		}
		# Add items to inventory - We use this loop because some items scanned may not be in the container
		foreach ($incoming->getIncomingScannedProducts() as $incomingScannedProduct) {
			$product = $incomingScannedProduct->getProduct();
			$location = $incomingScannedProduct->getLocation();
			$locationProduct = $this->locationProductManager->findOneByProductAndLocation($product, $location);
			$currentUser = $this->container->get('security.token_storage')->getToken()->getUser();
			if (!$locationProduct) {
				$this->locationProductManager
					->createLocationProductByIncomingProductScan($incomingScannedProduct, $entityManager, $currentUser);
			} else {
				$locationProduct->setModified(new \DateTime('now'));
				$quantity = $locationProduct->getOnHand() + $incomingScannedProduct->getQtyOnScan();
				$locationProduct->setOnHand($quantity);
				$locationProduct->setStaged($locationProduct->getStaged());
				$this->locationProductManager->updateLocationProduct($locationProduct, $entityManager, $currentUser);
			}
		}

		# Change the Incoming status to completed.
		$incoming->setStatus($this->incomingStatusManager->find(IncomingStatus::COMPLETED));
		$this->incomingManager->updateIncoming($incoming, $entityManager);
		if ($flush) {
			$entityManager->flush();
		}
	}

	public function getIndexContent()
	{
		$incoming = $this->incomingManager->findBy([], ['id' => 'desc']);
		$content = $this->templating->render('WarehouseBundle::Incoming/_index_incoming_detail.html.twig', [
			'incoming' => $incoming
		]);
		$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
			AjaxCommandDTO::OP_HTML, $content);
		return $ajaxCommands;
	}
}	