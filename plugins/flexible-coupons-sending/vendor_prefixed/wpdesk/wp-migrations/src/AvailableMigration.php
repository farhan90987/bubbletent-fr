<?php

declare (strict_types=1);
namespace FCSVendor\WPDesk\Migrations;

use FCSVendor\WPDesk\Migrations\Version\Version;
class AvailableMigration
{
    /** @var Version */
    private $version;
    /** @var AbstractMigration */
    private $migration;
    public function __construct(Version $version, AbstractMigration $migration)
    {
        $this->version = $version;
        $this->migration = $migration;
    }
    public function get_version(): Version
    {
        return $this->version;
    }
    public function get_migration(): AbstractMigration
    {
        return $this->migration;
    }
}
