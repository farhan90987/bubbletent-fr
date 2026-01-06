<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Accessor;

use MarketPress\German_Market\JMS\Serializer\DeserializationContext;
use MarketPress\German_Market\JMS\Serializer\Metadata\PropertyMetadata;
use MarketPress\German_Market\JMS\Serializer\SerializationContext;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
interface AccessorStrategyInterface
{
    /**
     * @return mixed
     */
    public function getValue(object $object, PropertyMetadata $metadata, SerializationContext $context);

    /**
     * @param mixed $value
     */
    public function setValue(object $object, $value, PropertyMetadata $metadata, DeserializationContext $context): void;
}
