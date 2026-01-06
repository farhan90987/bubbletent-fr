<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarketPress\German_Market\Symfony\Component\Validator\Exception;

use MarketPress\German_Market\Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Jan Vernieuwe <jan.vernieuwe@phpro.be>
 */
class ValidationFailedException extends RuntimeException
{
    public function __construct(
        private mixed $value,
        private ConstraintViolationListInterface $violations,
    ) {
        parent::__construct($violations);
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
