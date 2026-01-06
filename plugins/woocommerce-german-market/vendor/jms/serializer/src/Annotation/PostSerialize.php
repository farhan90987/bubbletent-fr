<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class PostSerialize implements SerializerAttribute
{
}
