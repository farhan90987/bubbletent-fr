<?php

namespace FlexibleCouponsProVendor\WPDesk\Persistence;

use FlexibleCouponsProVendor\Psr\Container\NotFoundExceptionInterface;
/**
 * @package WPDesk\Persistence
 */
class ElementNotExistsException extends \RuntimeException implements NotFoundExceptionInterface
{
}
