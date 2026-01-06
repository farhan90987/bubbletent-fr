<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Naming;

use MarketPress\German_Market\JMS\Serializer\Metadata\PropertyMetadata;

final class IdenticalPropertyNamingStrategy implements PropertyNamingStrategyInterface
{
    public function translateName(PropertyMetadata $property): string
    {
        return $property->name;
    }
}
