<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\ContextFactory;

use MarketPress\German_Market\JMS\Serializer\DeserializationContext;

/**
 * Deserialization Context Factory Interface.
 */
interface DeserializationContextFactoryInterface
{
    public function createDeserializationContext(): DeserializationContext;
}
