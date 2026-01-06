<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Ordering;

use MarketPress\German_Market\JMS\Serializer\Metadata\PropertyMetadata;

interface PropertyOrderingInterface
{
    /**
     * @param PropertyMetadata[] $properties name => property
     *
     * @return PropertyMetadata[] name => property
     */
    public function order(array $properties): array;
}
