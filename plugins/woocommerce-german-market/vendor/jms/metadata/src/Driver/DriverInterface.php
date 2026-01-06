<?php

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata\Driver;

use MarketPress\German_Market\Metadata\ClassMetadata;

interface DriverInterface
{
    public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata;
}
