<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017-08-14
 * Time: 3:07 PM
 */

namespace WarehouseBundle\Workflow;


use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\Manager\BookingManager;
use WarehouseBundle\Manager\BookingProductManager;
use WarehouseBundle\Manager\ProductManager;
use WarehouseBundle\Model\Booking\BookingProductSearchModel;
use WarehouseBundle\Model\Booking\BookingSearchModel;
use WarehouseBundle\Model\Product\ProductSearchModel;

class DashboardWorkflow
{
    private $container;
    private $bookingManager;
    private $productManager;
    private $bookingProductManager;

    /**
     * Default Workflow constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->bookingManager = $container->get(BookingManager::class);
        $this->productManager = $container->get(ProductManager::class);
        $this->bookingProductManager = $container->get(BookingProductManager::class);
    }

    public function countBookingsCreatedToday()
    {
        $bookingSearchModel = new BookingSearchModel();
        $bookingSearchModel->setCriteriaStartDate(['created'=>date('Y-m-d 00:00:00')]);
        return $this->bookingManager->count($bookingSearchModel);
    }

    public function countStockedProducts()
    {
        $productSearchModel = new ProductSearchModel();
        return $this->productManager->countStockProduct($productSearchModel);
    }

    public function countPickedBookingProductsToday()
    {
        $bookingProductSearchModel = new BookingProductSearchModel();
        $bookingProductSearchModel->setCriteriaStartDate(['pickedDate'=>date('Y-m-d 00:00:00')]);
        return $this->bookingProductManager->count($bookingProductSearchModel);

    }

    public function countBookingsShippedToday()
    {
        $bookingSearchModel = new BookingSearchModel();
        $bookingSearchModel->setCriteriaStartDate(['shipped'=>date('Y-m-d 00:00:00')]);
        return $this->bookingManager->count($bookingSearchModel);

    }

}