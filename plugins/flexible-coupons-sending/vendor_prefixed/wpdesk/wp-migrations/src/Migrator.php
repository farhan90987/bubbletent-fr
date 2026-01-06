<?php

declare (strict_types=1);
namespace FCSVendor\WPDesk\Migrations;

interface Migrator
{
    public function migrate(): void;
}
