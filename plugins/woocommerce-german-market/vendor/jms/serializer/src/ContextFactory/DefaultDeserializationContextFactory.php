<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\ContextFactory;

use MarketPress\German_Market\JMS\Serializer\DeserializationContext;

/**
 * Default Deserialization Context Factory.
 */
final class DefaultDeserializationContextFactory implements DeserializationContextFactoryInterface
{
    public function createDeserializationContext(): DeserializationContext
    {
        return new DeserializationContext();
    }
}
