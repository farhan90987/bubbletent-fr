<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarketPress\German_Market\Symfony\Component\Validator\Validator;

use Psr\Container\ContainerInterface;
use MarketPress\German_Market\Symfony\Component\Validator\Constraint;
use MarketPress\German_Market\Symfony\Component\Validator\Constraints\GroupSequence;
use MarketPress\German_Market\Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use MarketPress\German_Market\Symfony\Component\Validator\ConstraintViolationListInterface;
use MarketPress\German_Market\Symfony\Component\Validator\Context\ExecutionContextFactoryInterface;
use MarketPress\German_Market\Symfony\Component\Validator\Context\ExecutionContextInterface;
use MarketPress\German_Market\Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;
use MarketPress\German_Market\Symfony\Component\Validator\Mapping\MetadataInterface;
use MarketPress\German_Market\Symfony\Component\Validator\ObjectInitializerInterface;

/**
 * Recursive implementation of {@link ValidatorInterface}.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RecursiveValidator implements ValidatorInterface
{
    /**
     * Creates a new validator.
     *
     * @param ObjectInitializerInterface[] $objectInitializers The object initializers
     */
    public function __construct(
        protected ExecutionContextFactoryInterface $contextFactory,
        protected MetadataFactoryInterface $metadataFactory,
        protected ConstraintValidatorFactoryInterface $validatorFactory,
        protected array $objectInitializers = [],
        protected ?ContainerInterface $groupProviderLocator = null,
    ) {
    }

    public function startContext(mixed $root = null): ContextualValidatorInterface
    {
        return new RecursiveContextualValidator(
            $this->contextFactory->createContext($this, $root),
            $this->metadataFactory,
            $this->validatorFactory,
            $this->objectInitializers,
            $this->groupProviderLocator,
        );
    }

    public function inContext(ExecutionContextInterface $context): ContextualValidatorInterface
    {
        return new RecursiveContextualValidator(
            $context,
            $this->metadataFactory,
            $this->validatorFactory,
            $this->objectInitializers,
            $this->groupProviderLocator,
        );
    }

    public function getMetadataFor(mixed $object): MetadataInterface
    {
        return $this->metadataFactory->getMetadataFor($object);
    }

    public function hasMetadataFor(mixed $object): bool
    {
        return $this->metadataFactory->hasMetadataFor($object);
    }

    public function validate(mixed $value, Constraint|array|null $constraints = null, string|GroupSequence|array|null $groups = null): ConstraintViolationListInterface
    {
        return $this->startContext($value)
            ->validate($value, $constraints, $groups)
            ->getViolations();
    }

    public function validateProperty(object $object, string $propertyName, string|GroupSequence|array|null $groups = null): ConstraintViolationListInterface
    {
        return $this->startContext($object)
            ->validateProperty($object, $propertyName, $groups)
            ->getViolations();
    }

    public function validatePropertyValue(object|string $objectOrClass, string $propertyName, mixed $value, string|GroupSequence|array|null $groups = null): ConstraintViolationListInterface
    {
        // If a class name is passed, take $value as root
        return $this->startContext(\is_object($objectOrClass) ? $objectOrClass : $value)
            ->validatePropertyValue($objectOrClass, $propertyName, $value, $groups)
            ->getViolations();
    }
}
