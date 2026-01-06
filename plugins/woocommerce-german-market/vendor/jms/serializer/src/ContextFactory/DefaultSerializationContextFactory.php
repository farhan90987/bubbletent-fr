<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\ContextFactory;

use MarketPress\German_Market\JMS\Serializer\SerializationContext;

/**
 * Default Serialization Context Factory.
 */
final class DefaultSerializationContextFactory implements SerializationContextFactoryInterface
{
    public function createSerializationContext(): SerializationContext
    {
        return new SerializationContext();
    }
}
