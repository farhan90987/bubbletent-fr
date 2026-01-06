<?php

declare (strict_types=1);
namespace FlexibleCouponsProVendor\WPDesk\License\LicenseServer;

use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use FlexibleCouponsProVendor\Psr\Log\LoggerTrait;
/**
 * Dummy implementation of LoggerInterface.
 */
class DummyLogger implements LoggerInterface
{
    use LoggerTrait;
    public function log($level, $message, array $context = [])
    {
        error_log('wpdesk.license ' . $level . ': ' . $message);
    }
}
