<?php

declare (strict_types=1);
namespace FCSVendor\WPDesk\Migrations\Version;

use FCSVendor\Psr\Log\LoggerInterface;
use FCSVendor\WPDesk\Migrations\AbstractMigration;
class WpdbMigrationFactory implements MigrationFactory
{
    /** @var \wpdb */
    protected $wpdb;
    /** @var LoggerInterface */
    protected $logger;
    public function __construct(\wpdb $wpdb, LoggerInterface $logger)
    {
        $this->wpdb = $wpdb;
        $this->logger = $logger;
    }
    /**
     * @param class-string<AbstractMigration> $migration_class
     *
     * @return AbstractMigration
     */
    public function create_version(string $migration_class): AbstractMigration
    {
        return new $migration_class($this->wpdb, $this->logger);
    }
}
