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

namespace cooldogedev\BedrockEconomy\query\mysql;

use cooldogedev\BedrockEconomy\query\QueryManager;
use cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class MySQLTableCreationQuery extends MySQLQuery
{
    public function __construct(protected int $defaultBalance)
    {
    }

    public function onRun(mysqli $connection): void
    {
        $statement = $connection->prepare($this->getQuery());
        $statement->execute();
        $statement->close();
    }

    public function getQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . QueryManager::DATA_TABLE_PLAYERS . "
            (
             username VARCHAR (16) PRIMARY KEY UNIQUE NOT NULL,
             balance INT (64) DEFAULT " . $this->getDefaultBalance() . "
             )
             ";
    }

    public function getDefaultBalance(): int
    {
        return $this->defaultBalance;
    }
}
