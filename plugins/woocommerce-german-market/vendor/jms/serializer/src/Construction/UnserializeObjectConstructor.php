<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Construction;

use MarketPress\German_Market\Doctrine\Instantiator\Instantiator;
use MarketPress\German_Market\JMS\Serializer\DeserializationContext;
use MarketPress\German_Market\JMS\Serializer\Metadata\ClassMetadata;
use MarketPress\German_Market\JMS\Serializer\Visitor\DeserializationVisitorInterface;

final class UnserializeObjectConstructor implements ObjectConstructorInterface
{
    /** @var Instantiator */
    private $instantiator;

    /**
     * {@inheritdoc}
     */
    public function construct(DeserializationVisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context): ?object
    {
        return $this->getInstantiator()->instantiate($metadata->name);
    }

    private function getInstantiator(): Instantiator
    {
        if (null === $this->instantiator) {
            $this->instantiator = new Instantiator();
        }

        return $this->instantiator;
    }
}
