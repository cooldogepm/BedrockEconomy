<?php

/**
 *  Copyright (c) 2021 cooldogedev
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

namespace cooldogedev\BedrockEconomy\database;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\constant\SessionConstants;
use cooldogedev\BedrockEconomy\database\query\MySQLQueryManager;
use cooldogedev\BedrockEconomy\database\query\QueryManager;
use cooldogedev\BedrockEconomy\database\query\SQLiteQueryManager;
use cooldogedev\BedrockEconomY\interfaces\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\task\BulkSessionsSaveTask;
use cooldogedev\libSQL\constant\DataProviderConstants;
use cooldogedev\libSQL\DatabaseConnector;

final class DatabaseManager extends BedrockEconomyOwned
{
    protected DatabaseConnector $databaseConnector;
    protected QueryManager $queryManager;
    protected int $saveMode;
    protected int $updatePeriod;

    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);

        $databaseConfig = $this->getPlugin()->getConfigManager()->getDatabaseConfig();

        $this->databaseConnector = new DatabaseConnector($this->getPlugin(), $databaseConfig);
        $this->saveMode = $databaseConfig["save-options"]["update-mode"];
        $this->updatePeriod = $databaseConfig["save-options"]["update-fixed-period"];

        $this->queryManager = match ($this->getDatabaseConnector()->getDataProvider()->getName(true)) {
            DataProviderConstants::getLowercaseMySQL() => new MySQLQueryManager(),
            DataProviderConstants::getLowercaseSQLite() => new SQLiteQueryManager()
        };

        if ($this->getSaveMode() === SessionConstants::SESSION_SAVE_MODE_FIXED_PERIOD) {
            $this->getPlugin()->getScheduler()->scheduleRepeatingTask(
                new BulkSessionsSaveTask($this->getPlugin()->getSessionManager()), $this->getUpdatePeriod()
            );
        }

        $this->getDatabaseConnector()->submitQuery(
            $this->getQueryManager()->getTableCreationQuery(
                $this->getPlugin()->getCurrencyManager()->getDefaultBalance()
            )
        );
    }

    public function getDatabaseConnector(): DatabaseConnector
    {
        return $this->databaseConnector;
    }

    public function getSaveMode(): int
    {
        return $this->saveMode;
    }

    public function getUpdatePeriod(): mixed
    {
        return $this->updatePeriod;
    }

    public function getQueryManager(): QueryManager
    {
        return $this->queryManager;
    }
}
