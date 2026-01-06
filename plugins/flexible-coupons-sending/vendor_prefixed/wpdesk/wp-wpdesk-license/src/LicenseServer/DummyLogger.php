<?php

declare (strict_types=1);
namespace FCSVendor\WPDesk\License\LicenseServer;

use FCSVendor\Psr\Log\LoggerInterface;
use FCSVendor\Psr\Log\LoggerTrait;
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
