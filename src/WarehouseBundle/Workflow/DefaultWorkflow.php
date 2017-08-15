<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017-08-14
 * Time: 3:07 PM
 */

namespace WarehouseBundle\Workflow;


use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\Model\Booking\BookingSearchModel;

class DefaultWorkflow
{
    private $container;
    private $bookingManager;
    private $productManager;

    /**
     * Default Workflow constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->bookingManager = $container->get('warehouse.manager.booking_manager');
        $this->productManager = $container->get('warehouse.manager.product_manager');

    }

    public function countBookingsCreatedToday()
    {
        $bookingSearchModel = new BookingSearchModel();
        $bookingSearchModel->setCriteriaStartDate(['created'=>date('Y-m-d 00:00:00')]);
        return $this->bookingManager->count($bookingSearchModel);
    }

    public function countStockedProducts()
    {

    }

    public function countPickedProductsToday()
    {

    }

    public function countBookingsShippedToday()
    {
        $bookingSearchModel = new BookingSearchModel();
        $bookingSearchModel->setCriteriaStartDate(['shipped'=>date('Y-m-d 00:00:00')]);
        return $this->bookingManager->count($bookingSearchModel);

    }

}