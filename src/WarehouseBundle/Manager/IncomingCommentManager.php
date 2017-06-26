<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-26
 * Time: 11:55 AM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\IncomingComment;

class IncomingCommentManager extends BaseManager
{
	private $incomingCommentRepository;

	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->incomingCommentRepository = $entityManager->getRepository('WarehouseBundle:IncomingComment');
	}

	/**
	 * @param IncomingComment $incomingComment
	 * @param null            $entityManager
	 */
	public function update(IncomingComment $incomingComment, $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$entityManager->persist($incomingComment);
		if ($flush) {
			$entityManager->flush();
		}
	}
}