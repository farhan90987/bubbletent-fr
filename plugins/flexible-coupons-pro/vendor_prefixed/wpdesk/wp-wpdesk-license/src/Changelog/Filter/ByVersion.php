<?php

namespace FlexibleCouponsProVendor\WPDesk\License\Changelog\Filter;

use FilterIterator;
use Iterator;
/**
 * Filters items by version.
 */
class ByVersion extends FilterIterator
{
    private string $version;
    public function __construct(Iterator $changes, string $version)
    {
        parent::__construct($changes);
        $this->version = $version;
    }
    public function accept(): bool
    {
        $change = $this->getInnerIterator()->current();
        return version_compare($change['version'], $this->version, '>');
    }
}
