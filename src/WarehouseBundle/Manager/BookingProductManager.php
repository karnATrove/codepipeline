<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-23
 * Time: 9:51 AM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Exception\Manager\ManagerException;
use WarehouseBundle\Model\Booking\BookingProductSearchModel;

class BookingProductManager
{
	const STATUS_CODE_DELETED = "DELT";
	const STATUS_CODE_PENDING = "PEND";
	const STATUS_CODE_IN_PROGRESS = "PROG";
	const STATUS_CODE_PICKED = "PICK";
	const STATUS_CODE_CLOSED = "CLOS";

    private $bookingProductRepository;
    private $em;

	public function  __construct(EntityManagerInterface $entityManager){
        $this->em = $entityManager;
        $this->bookingProductRepository = $this->em->getRepository('WarehouseBundle:BookingProduct');
	}

	/**
	 * @param $statusId
	 *
	 * @return string
	 * @throws ManagerException
	 */
	public static function getCode($statusId)
	{
		$list = self::getStatusMapper('id');
		if (!isset($list[$statusId])) {
            throw new ManagerException("Can't find match booking item status code by {$statusId}");
        }

        return $list[$statusId];
	}

    /**
     * @param string $statusCode
     *
     * @return int
     * @throws \WarehouseBundle\Exception\Manager\ManagerException
     */
    public static function getStatusIdByCode(string $statusCode)
    {
        $list = self::getStatusMapper('code');
        if (!isset($list[$statusCode])) {
            throw new ManagerException("Can't find match booking item status code by {$statusCode}");
        }

        return $list[$statusCode];
    }

    /**
     * @param string $key
     *
     * @return array
     * @throws \WarehouseBundle\Exception\Manager\ManagerException
     */
    private static function getStatusMapper(string $key = 'id')
    {
        $list = array(
            BookingProduct::STATUS_DELETED     => self::STATUS_CODE_DELETED,
            BookingProduct::STATUS_PENDING     => self::STATUS_CODE_PENDING,
            BookingProduct::STATUS_PICKED      => self::STATUS_CODE_PICKED,
            BookingProduct::STATUS_IN_PROGRESS => self::STATUS_CODE_IN_PROGRESS,
            BookingProduct::STATUS_CLOSED      => self::STATUS_CODE_CLOSED
        );
        switch ($key) {
            case 'id':
                return $list;
                break;
            case 'code':
                return array_flip($list);
                break;
            default:
                throw new ManagerException("Can't find match booking product status list");
                break;
        }
    }

    /**
     * @param BookingProductSearchModel $bookingProductSearchModel
     * @return mixed
     */
    public function count(BookingProductSearchModel $bookingProductSearchModel)
    {
        return $this->bookingProductRepository->count($bookingProductSearchModel);
    }
}