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
use WarehouseBundle\Entity\IEntity;

class BookingCommentManager extends BaseManager
{
	private $bookingCommentRepository;

	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, BookingComment::class);
		$this->bookingCommentRepository = $entityManager->getRepository('WarehouseBundle:BookingComment');
	}

	/**
	 * @param BookingComment|IEntity      $bookingComment
	 * @param EntityManagerInterface|null $entityManager
	 */
	public function update(IEntity $bookingComment, EntityManagerInterface $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$entityManager->persist($bookingComment);
		if ($flush) {
			$entityManager->flush();
		}
	}
}