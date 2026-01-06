<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Visitor\Factory;

use MarketPress\German_Market\JMS\Serializer\Visitor\DeserializationVisitorInterface;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
interface DeserializationVisitorFactory
{
    public function getVisitor(): DeserializationVisitorInterface;
}
