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

use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\database\exception\InsufficientFundsException;
use cooldogedev\BedrockEconomy\database\helper\AccountHolder;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class TransferQuery extends SQLiteQuery
{
    use AccountHolder;
    use TableHolder;

    public function __construct(protected int $amount, protected int $decimals, protected string $targetUsername, protected string $targetXuid) {}

    /**
     * @throws RecordNotFoundException
     * @throws InsufficientFundsException
     */
    public function onRun(SQLite3 $connection): void
    {
        $connection->exec("BEGIN TRANSACTION");

        // check if the source account exists
        $sourceQuery = $connection->prepare("SELECT * FROM " . $this->table . " WHERE xuid = ? OR username = ?");
        $sourceQuery->bindValue(1, $this->xuid);
        $sourceQuery->bindValue(2, $this->username);

        $sourceResult = $sourceQuery->execute();

        if ($sourceResult->fetchArray(SQLITE3_ASSOC) === false) {
            $connection->exec("ROLLBACK");
            throw new RecordNotFoundException(
                _message: "Account not found for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        // check if the target account exists
        $targetQuery = $connection->prepare("SELECT * FROM " . $this->table . " WHERE xuid = ? OR username = ?");
        $targetQuery->bindValue(1, $this->targetXuid);
        $targetQuery->bindValue(2, $this->targetUsername);

        $targetResult = $targetQuery->execute();

        if ($targetResult->fetchArray(SQLITE3_ASSOC) === false) {
            $connection->exec("ROLLBACK");
            throw new RecordNotFoundException(
                _message: "Account not found for xuid " . $this->targetXuid . " or username " . $this->targetUsername
            );
        }

        // subtract the money from the source account
        $sourceUpdateQuery = $connection->prepare("UPDATE " . $this->table . " SET amount = amount - ?, decimals = decimals - ? WHERE xuid = ? OR username = ? AND amount >= ? AND decimals >= ?");
        $sourceUpdateQuery->bindValue(1, $this->amount, SQLITE3_INTEGER);
        $sourceUpdateQuery->bindValue(2, $this->decimals, SQLITE3_INTEGER);
        $sourceUpdateQuery->bindValue(3, $this->xuid);
        $sourceUpdateQuery->bindValue(4, $this->username);
        $sourceUpdateQuery->bindValue(5, $this->amount, SQLITE3_INTEGER);
        $sourceUpdateQuery->bindValue(6, $this->decimals, SQLITE3_INTEGER);

        $sourceUpdateResult = $sourceUpdateQuery->execute();

        if ($connection->changes() === 0) {
            $connection->exec("ROLLBACK");
            throw new InsufficientFundsException(
                _message: "Insufficient funds for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        // add the money to the target account
        $targetUpdateQuery = $connection->prepare("UPDATE " . $this->table . " SET amount = amount + ?, decimals = decimals + ? WHERE xuid = ? OR username = ?");
        $targetUpdateQuery->bindValue(1, $this->amount, SQLITE3_INTEGER);
        $targetUpdateQuery->bindValue(2, $this->decimals, SQLITE3_INTEGER);
        $targetUpdateQuery->bindValue(3, $this->targetXuid);
        $targetUpdateQuery->bindValue(4, $this->targetUsername);

        $targetUpdateResult = $targetUpdateQuery->execute();

        if ($connection->changes() === 0) {
            $connection->exec("ROLLBACK");
            throw new RecordNotFoundException(
                _message: "Account not found for xuid " . $this->targetXuid . " or username " . $this->targetUsername
            );
        }

        $connection->exec("COMMIT");

        $this->setResult($connection->changes() > 0);

        $targetResult->finalize();
        $sourceResult->finalize();
        $targetUpdateResult->finalize();
        $sourceUpdateResult->finalize();

        $sourceQuery->close();
        $targetQuery->close();
        $sourceUpdateQuery->close();
        $targetUpdateQuery->close();
    }
}
