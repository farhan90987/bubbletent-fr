<?php

declare (strict_types=1);
namespace FCSVendor\WPDesk\Migrations\Finder;

use FCSVendor\WPDesk\Migrations\AbstractMigration;
interface MigrationFinder
{
    /**
     * @param string $directory
     * @return class-string<AbstractMigration>[]
     */
    public function find_migrations(string $directory): array;
}
