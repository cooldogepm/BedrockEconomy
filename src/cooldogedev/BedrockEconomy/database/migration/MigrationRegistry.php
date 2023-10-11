<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\migration;

use cooldogedev\BedrockEconomy\database\migration\v2_1_2\Migration as v2_1_2;

final class MigrationRegistry
{
    /**
     * @var array<int, IMigration>
     */
    protected static array $migrations = [];

    public static function init(): void
    {
        MigrationRegistry::register(new v2_1_2());
    }

    public static function get(string $version): ?IMigration
    {
        foreach (MigrationRegistry::$migrations as $migration) {
            if (!version_compare($version, $migration->getMin(), ">=")) {
                continue;
            }

            if (!version_compare($version, $migration->getMax(), "<=")) {
                continue;
            }

            return $migration;
        }

        return null;
    }

    protected static function register(IMigration $migration): void
    {
        MigrationRegistry::$migrations[] = $migration;
    }
}
