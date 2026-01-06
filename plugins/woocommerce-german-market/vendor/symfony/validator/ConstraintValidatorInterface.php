<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarketPress\German_Market\Symfony\Component\Validator;

use MarketPress\German_Market\Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ConstraintValidatorInterface
{
    /**
     * Initializes the constraint validator.
     *
     * @return void
     */
    public function initialize(ExecutionContextInterface $context);

    /**
     * Checks if the passed value is valid.
     *
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint);
}
