<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Exclusion;

use MarketPress\German_Market\JMS\Serializer\Context;
use MarketPress\German_Market\JMS\Serializer\Metadata\ClassMetadata;
use MarketPress\German_Market\JMS\Serializer\Metadata\PropertyMetadata;

/**
 * Interface for exclusion strategies.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface ExclusionStrategyInterface
{
    /**
     * Whether the class should be skipped.
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $context): bool;

    /**
     * Whether the property should be skipped.
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context): bool;
}
