<?php

declare (strict_types=1);
namespace MarketPress\German_Market\JMS\Serializer\GraphNavigator\Factory;

use MarketPress\German_Market\JMS\Serializer\Accessor\AccessorStrategyInterface;
use MarketPress\German_Market\JMS\Serializer\Accessor\DefaultAccessorStrategy;
use MarketPress\German_Market\JMS\Serializer\EventDispatcher\EventDispatcher;
use MarketPress\German_Market\JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use MarketPress\German_Market\JMS\Serializer\Expression\ExpressionEvaluatorInterface;
use MarketPress\German_Market\JMS\Serializer\GraphNavigator\SerializationGraphNavigator;
use MarketPress\German_Market\JMS\Serializer\GraphNavigatorInterface;
use MarketPress\German_Market\JMS\Serializer\Handler\HandlerRegistryInterface;
use MarketPress\German_Market\Metadata\MetadataFactoryInterface;
final class SerializationGraphNavigatorFactory implements GraphNavigatorFactoryInterface
{
    /**
     * @var \MetadataFactoryInterface
     */
    private $metadataFactory;
    /**
     * @var HandlerRegistryInterface
     */
    private $handlerRegistry;
    /**
     * @var AccessorStrategyInterface
     */
    private $accessor;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var ExpressionEvaluatorInterface
     */
    private $expressionEvaluator;
    public function __construct(MetadataFactoryInterface $metadataFactory, HandlerRegistryInterface $handlerRegistry, ?AccessorStrategyInterface $accessor = null, ?EventDispatcherInterface $dispatcher = null, ?ExpressionEvaluatorInterface $expressionEvaluator = null)
    {
        $this->metadataFactory = $metadataFactory;
        $this->handlerRegistry = $handlerRegistry;
        $this->accessor = $accessor ?: new DefaultAccessorStrategy();
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->expressionEvaluator = $expressionEvaluator;
    }
    public function getGraphNavigator(): GraphNavigatorInterface
    {
        return new SerializationGraphNavigator($this->metadataFactory, $this->handlerRegistry, $this->accessor, $this->dispatcher, $this->expressionEvaluator);
    }
}