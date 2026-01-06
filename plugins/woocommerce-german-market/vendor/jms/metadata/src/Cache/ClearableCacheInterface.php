<?php

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata\Cache;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
interface ClearableCacheInterface
{
    /**
     * Clear all classes metadata from the cache.
     */
    public function clear(): bool;
}
