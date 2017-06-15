<?php

namespace WarehouseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Doctrine\ORM\EntityManager;

class NotModifiedValidator extends ConstraintValidator
{
	private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
    	$current = $constraint->current;
    	$original = $constraint->original;

    	if (method_exists($current,'getModified') && method_exists($original,'getModified')) {
    		if ((!is_null($current->getModified()) && is_null($original->getModified())) || $current->getModified() !== $original->getModified()) {
    			if ($current->getModified() > $original->getModified()) {
	    			$this->context->buildViolation($constraint->message)
		                ->setParameter('%type%', get_class($current))
		                ->setParameter('%datetime%', $current->getModified()->format('Y-m-d H:i:s'))
		                ->addViolation();
	    		}
    		}
    	}
    }
}