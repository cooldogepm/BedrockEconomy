<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2023 cooldogedev
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @auto-license
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\sqlite;

use cooldogedev\BedrockEconomy\database\helper\AccountHolder;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class InsertionQuery extends SQLiteQuery
{
    use AccountHolder;
    use TableHolder;

    public function __construct(protected int $amount, protected int $decimals) {}

    public function onRun(SQLite3 $connection): void
    {
        $statement = $connection->prepare("INSERT OR IGNORE INTO " . $this->table . " (xuid, username, amount, decimals) VALUES (?, ?, ?, ?)");
        $statement->bindValue(1, $this->xuid);
        $statement->bindValue(2, $this->username);
        $statement->bindValue(3, $this->amount, SQLITE3_INTEGER);
        $statement->bindValue(4, $this->decimals, SQLITE3_INTEGER);
        $statement->execute();

        $this->setResult($connection->changes() > 0);

        $statement->close();
    }
}
