<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Visitor\Factory;

use MarketPress\German_Market\JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
interface SerializationVisitorFactory
{
    public function getVisitor(): SerializationVisitorInterface;
}
