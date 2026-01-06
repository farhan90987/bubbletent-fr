<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Handler;

use MarketPress\German_Market\JMS\Serializer\GraphNavigatorInterface;
use MarketPress\German_Market\JMS\Serializer\Metadata\StaticPropertyMetadata;
use MarketPress\German_Market\JMS\Serializer\SerializationContext;
use MarketPress\German_Market\JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
final class StdClassHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $formats = ['json', 'xml'];

        foreach ($formats as $format) {
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type' => \stdClass::class,
                'format' => $format,
                'method' => 'serializeStdClass',
            ];
        }

        return $methods;
    }

    /**
     * @return mixed
     */
    public function serializeStdClass(SerializationVisitorInterface $visitor, \stdClass $stdClass, array $type, SerializationContext $context)
    {
        $classMetadata = $context->getMetadataFactory()->getMetadataForClass('stdClass');
        $visitor->startVisitingObject($classMetadata, $stdClass, ['name' => 'stdClass']);

        foreach ((array) $stdClass as $name => $value) {
            $metadata = new StaticPropertyMetadata('stdClass', $name, $value);
            $visitor->visitProperty($metadata, $value);
        }

        return $visitor->endVisitingObject($classMetadata, $stdClass, ['name' => 'stdClass']);
    }
}
