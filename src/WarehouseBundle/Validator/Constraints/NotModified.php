<?php

namespace WarehouseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotModified extends Constraint
{
    public $message = 'The entity "%type%" was modified by another user on "%datetime%".';

    public $current;
    public $original;

    public function __construct($options = null)
    {
        if (is_array($options) && !isset($options['value'])) {
            throw new ConstraintDefinitionException(sprintf(
                'The %s constraint requires the "value" option to be set.',
                get_class($this)
            ));
        }

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'value';
    }

    public function validatedBy()
    {
        return "not_modified";
    }
}