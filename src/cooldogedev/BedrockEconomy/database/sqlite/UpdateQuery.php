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

use cooldogedev\BedrockEconomy\database\constant\UpdateMode;
use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\database\exception\InsufficientFundsException;
use cooldogedev\BedrockEconomy\database\helper\AccountHolder;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\libSQL\query\SQLiteQuery;
use InvalidArgumentException;
use SQLite3;

final class UpdateQuery extends SQLiteQuery
{
    use AccountHolder;
    use TableHolder;

    public function __construct(protected int $mode, protected int $amount, protected int $decimals) {}

    /**
     * @throws RecordNotFoundException
     * @throws InsufficientFundsException
     */
    public function onRun(SQLite3 $connection): void
    {
        $checkQuery = $connection->prepare("SELECT * FROM " . $this->table . " WHERE xuid = ? OR username = ?");
        $checkQuery->bindValue(1, $this->xuid);
        $checkQuery->bindValue(2, $this->username);

        $checkResult = $checkQuery->execute();

        if ($checkResult->fetchArray(SQLITE3_ASSOC) === false) {
            throw new RecordNotFoundException(
                _message: "Account not found for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        $updateQuery = match ($this->mode) {
            UpdateMode::ADD => "UPDATE " . $this->table . " SET amount = amount + ?, decimals = decimals + ? WHERE xuid = ? OR username = ?",
            UpdateMode::SUBTRACT => "UPDATE " . $this->table . " SET amount = amount - ?, decimals = decimals - ? WHERE xuid = ? OR username = ? AND amount >= ? AND decimals >= ?",
            UpdateMode::SET => "UPDATE " . $this->table . " SET amount = ?, decimals = ? WHERE xuid = ? OR username = ?",

            default => throw new InvalidArgumentException("Invalid mode " . $this->mode)
        };

        $updateQuery = $connection->prepare($updateQuery);
        $updateQuery->bindValue(1, $this->amount, SQLITE3_INTEGER);
        $updateQuery->bindValue(2, $this->decimals, SQLITE3_INTEGER);
        $updateQuery->bindValue(3, $this->xuid);
        $updateQuery->bindValue(4, $this->username);

        if ($this->mode === UpdateMode::SUBTRACT) {
            $updateQuery->bindValue(5, $this->amount, SQLITE3_INTEGER);
            $updateQuery->bindValue(6, $this->decimals, SQLITE3_INTEGER);
        }

        $updateResult = $updateQuery->execute();

        if ($connection->changes() === 0) {
            if ($this->mode === UpdateMode::SUBTRACT) {
                throw new InsufficientFundsException(
                    _message: "Insufficient funds for xuid " . $this->xuid . " or username " . $this->username
                );
            }

            throw new RecordNotFoundException(
                _message: "Account not found for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        $this->setResult($connection->changes() > 0);

        $checkResult->finalize();
        $updateResult->finalize();

        $checkQuery->close();
        $updateQuery->close();
    }
}
