<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Type;

interface ParserInterface
{
    public function parse(string $type): array;
}
