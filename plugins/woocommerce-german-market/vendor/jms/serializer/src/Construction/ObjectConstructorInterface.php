<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Construction;

use MarketPress\German_Market\JMS\Serializer\DeserializationContext;
use MarketPress\German_Market\JMS\Serializer\Metadata\ClassMetadata;
use MarketPress\German_Market\JMS\Serializer\Visitor\DeserializationVisitorInterface;

/**
 * Implementations of this interface construct new objects during deserialization.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface ObjectConstructorInterface
{
    /**
     * Constructs a new object.
     *
     * Implementations could for example create a new object calling "new", use
     * "unserialize" techniques, reflection, or other means.
     *
     * @param mixed $data
     * @param array $type ["name" => string, "params" => array]
     */
    public function construct(DeserializationVisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context): ?object;
}
