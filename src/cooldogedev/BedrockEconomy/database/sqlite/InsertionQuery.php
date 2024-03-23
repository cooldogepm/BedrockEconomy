<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2024 cooldogedev
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

use cooldogedev\BedrockEconomy\database\exception\RecordAlreadyExistsException;
use cooldogedev\BedrockEconomy\database\helper\AccountHolder;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class InsertionQuery extends SQLiteQuery
{
    use AccountHolder;
    use TableHolder;

    public function __construct(private readonly int $amount, private readonly int $decimals) {}

    /**
     * @throws RecordAlreadyExistsException
     */
    public function onRun(SQLite3 $connection): void
    {
        $amount = $this->amount . "." . $this->decimals;
        $connection->exec("BEGIN TRANSACTION");

        // check if account exists
        $checkQuery = $connection->prepare("SELECT * FROM " . $this->table . " WHERE xuid = ? OR username = ?");
        $checkQuery->bindValue(1, $this->xuid);
        $checkQuery->bindValue(2, $this->username);
        $checkResult = $checkQuery->execute();

        if ($checkResult->fetchArray(SQLITE3_ASSOC) !== false) {
            $connection->exec("ROLLBACK");
            throw new RecordAlreadyExistsException(
                _message: "Account already exists for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        $insertionQuery = $connection->prepare("INSERT OR IGNORE INTO " . $this->table . " (xuid, username, amount) VALUES (?, ?, ?)");
        $insertionQuery->bindValue(1, $this->xuid);
        $insertionQuery->bindValue(2, $this->username);
        $insertionQuery->bindValue(3, $amount);
        $insertionQuery->execute();

        if ($connection->changes() === 0) {
            $connection->exec("ROLLBACK");
            throw new RecordAlreadyExistsException(
                _message: "Account already exists for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        $connection->exec("COMMIT");

        $checkResult->finalize();

        $checkQuery->close();
        $insertionQuery->close();

        $this->setResult($connection->changes() > 0);
    }
}