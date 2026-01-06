<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\ContextFactory;

use MarketPress\German_Market\JMS\Serializer\SerializationContext;

/**
 * Serialization Context Factory Interface.
 */
interface SerializationContextFactoryInterface
{
    public function createSerializationContext(): SerializationContext;
}
