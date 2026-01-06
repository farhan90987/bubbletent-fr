<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Twig;

use MarketPress\German_Market\JMS\Serializer\SerializationContext;
use MarketPress\German_Market\JMS\Serializer\SerializerInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Serializer helper twig extension
 *
 * Basically provides access to JMSSerializer from Twig
 */
class SerializerExtension extends SerializerBaseExtension
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(SerializerInterface $serializer, string $serializationFunctionsPrefix = '')
    {
        $this->serializer = $serializer;

        parent::__construct($serializationFunctionsPrefix);
    }

    /**
     * @return TwigFilter[]
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     */
    public function getFilters()
    {
        return [
            new TwigFilter($this->serializationFunctionsPrefix . 'serialize', [$this, 'serialize']),
        ];
    }

    /**
     * @return TwigFunction[]
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     */
    public function getFunctions()
    {
        return [
            new TwigFunction($this->serializationFunctionsPrefix . 'serialization_context', '\MarketPress\German_Market\JMS\Serializer\SerializationContext::create'),
        ];
    }

    public function serialize(object $object, string $type = 'json', ?SerializationContext $context = null): string
    {
        return $this->serializer->serialize($object, $type, $context);
    }
}
