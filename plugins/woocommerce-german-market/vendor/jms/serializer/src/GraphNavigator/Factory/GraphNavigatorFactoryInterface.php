<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\GraphNavigator\Factory;

use MarketPress\German_Market\JMS\Serializer\GraphNavigatorInterface;

interface GraphNavigatorFactoryInterface
{
    public function getGraphNavigator(): GraphNavigatorInterface;
}
