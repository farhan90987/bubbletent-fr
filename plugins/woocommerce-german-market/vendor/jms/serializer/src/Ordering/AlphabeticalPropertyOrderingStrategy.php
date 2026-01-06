<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Ordering;

use MarketPress\German_Market\JMS\Serializer\Metadata\PropertyMetadata;

final class AlphabeticalPropertyOrderingStrategy implements PropertyOrderingInterface
{
    /**
     * {@inheritdoc}
     */
    public function order(array $properties): array
    {
        uasort(
            $properties,
            static fn (PropertyMetadata $a, PropertyMetadata $b): int => strcmp($a->name, $b->name),
        );

        return $properties;
    }
}
