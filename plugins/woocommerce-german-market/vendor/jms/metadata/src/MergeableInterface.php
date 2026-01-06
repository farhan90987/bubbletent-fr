<?php

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata;

interface MergeableInterface
{
    public function merge(MergeableInterface $object): void;
}
