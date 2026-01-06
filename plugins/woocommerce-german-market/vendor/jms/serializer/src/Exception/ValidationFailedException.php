<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Exception;

use MarketPress\German_Market\Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationFailedException extends RuntimeException
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $list;

    public function __construct(ConstraintViolationListInterface $list)
    {
        parent::__construct(sprintf('Validation failed with %d error(s).', \count($list)));

        $this->list = $list;
    }

    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->list;
    }
}
