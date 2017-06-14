<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-13
 * Time: 3:41 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\BookingComment;

class BookingCommentManager extends BaseManager
{
	private $bookingCommentRepository;

	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->bookingCommentRepository = $entityManager->getRepository('WarehouseBundle:BookingComment');
	}

	/**
	 * @param BookingComment $bookingComment
	 * @param null           $entityManager
	 */
	public function update(BookingComment $bookingComment, $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$entityManager->persist($bookingComment);
		if ($flush) {
			$entityManager->flush();
		}
	}
}