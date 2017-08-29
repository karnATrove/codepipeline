<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\IncomingComment;

class IncomingCommentManager extends BaseManager
{
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, IncomingComment::class);
	}
}