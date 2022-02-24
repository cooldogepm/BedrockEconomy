<?php

/**
 *  Copyright (c) 2022 cooldogedev
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\config;

use cooldogedev\BedrockEconomy\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use JsonException;

final class ConfigManager extends BedrockEconomyOwned
{
    protected array $databaseConfig;
    protected array $currencyConfig;
    protected array $mysqlConfig;
    protected array $sqliteConfig;
    protected array $utilityConfig;

    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);

        if ($this->getPlugin()->getDescription()->getVersion() !== $this->getPlugin()->getConfig()->get("config-version")) {
            $this->getPlugin()->getLogger()->warning("An outdated config was provided attempting to generate a new one...");
            if (!rename($this->getPlugin()->getDataFolder() . "config.yml", $this->getPlugin()->getDataFolder() . "config.old.yml")) {
                $this->getPlugin()->getLogger()->critical("An unknown error occurred while attempting to generate the new config");
                $this->getPlugin()->getServer()->getPluginManager()->disablePlugin($this->getPlugin());
            }
            $this->getPlugin()->reloadConfig();

            // This could happen if the plugin's version was updated from poggit's page and not github which might cause an issue (reloads on every start up)
            try {
                $this->getPlugin()->getConfig()->set("config-version", $this->getPlugin()->getDescription()->getVersion());
                $this->getPlugin()->getConfig()->save();
            } catch (JsonException $e) {
                $this->getPlugin()->getLogger()->critical("An error occurred while attempting to generate the new config, " . $e->getMessage());
            }
        }

        $this->utilityConfig = $this->getPlugin()->getConfig()->get("utility");
        $this->databaseConfig = $this->getPlugin()->getConfig()->get("database");
        $this->currencyConfig = $this->getPlugin()->getConfig()->get("currency");
        $this->mysqlConfig = $this->getDatabaseConfig()["mysql"];
        $this->sqliteConfig = $this->getDatabaseConfig()["sqlite"];
    }

    public function getDatabaseConfig(): array
    {
        return $this->databaseConfig;
    }

    public function getUtilityConfig(): array
    {
        return $this->utilityConfig;
    }

    public function getCurrencyConfig(): array
    {
        return $this->currencyConfig;
    }

    public function getMySQLConfig(): array
    {
        return $this->mysqlConfig;
    }

    public function getSQLiteConfig(): array
    {
        return $this->sqliteConfig;
    }
}
