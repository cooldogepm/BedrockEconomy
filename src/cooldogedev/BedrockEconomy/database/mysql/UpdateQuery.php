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

namespace cooldogedev\BedrockEconomy\database\mysql;

use cooldogedev\BedrockEconomy\database\constant\UpdateMode;
use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\database\exception\InsufficientFundsException;
use cooldogedev\BedrockEconomy\database\helper\AccountHolder;
use cooldogedev\BedrockEconomy\database\helper\ReferenceHolder;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\query\MySQLQuery;
use InvalidArgumentException;
use mysqli;

final class UpdateQuery extends MySQLQuery
{
    use AccountHolder;
    use ReferenceHolder;
    use TableHolder;

    public function __construct(private readonly int $mode, private readonly int $amount, private readonly int $decimals) {}

    /**
     * @throws RecordNotFoundException
     * @throws InsufficientFundsException
     */
    public function onRun(mysqli $connection): void
    {
        $amount = $this->amount . "." . $this->decimals;
        $connection->begin_transaction();

        // check if account exists
        $checkQuery = $connection->prepare("SELECT * FROM " . $this->table . " WHERE xuid = ? OR username = ?");
        $checkQuery->bind_param("ss", $this->getRef($this->xuid), $this->getRef($this->username));
        $checkQuery->execute();

        $checkResult = $checkQuery->get_result();

        if ($checkResult->num_rows === 0) {
            $connection->rollback();
            throw new RecordNotFoundException(
                _message: "Account not found for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        $updateQuery = match ($this->mode) {
            UpdateMode::ADD => "UPDATE " . $this->table . " SET amount = amount + ? WHERE xuid = ? OR username = ?",
            UpdateMode::SUBTRACT => "UPDATE " . $this->table . " SET amount = amount - ? WHERE (xuid = ? OR username = ?) AND amount >= ?",
            UpdateMode::SET => "UPDATE " . $this->table . " SET amount = ? WHERE xuid = ? OR username = ?",

            default => throw new InvalidArgumentException("Invalid mode " . $this->mode)
        };

        // update account
        $updateQuery = $connection->prepare($updateQuery);

        if ($this->mode === UpdateMode::SUBTRACT) {
            $updateQuery->bind_param("issi", $amount, $this->getRef($this->xuid), $this->getRef($this->username), $amount);
        } else {
            $updateQuery->bind_param("iss", $amount, $this->getRef($this->xuid), $this->getRef($this->username));
        }

        $updateQuery->execute();

        if ($updateQuery->affected_rows === 0 && $this->mode === UpdateMode::SUBTRACT) {
            $connection->rollback();
            throw new InsufficientFundsException(
                _message: "Insufficient funds for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        if ($updateQuery->affected_rows === 0) {
            $connection->rollback();
            throw new RecordNotFoundException(
                _message: "Account not found for xuid " . $this->xuid . " or username " . $this->username
            );
        }

        $connection->commit();

        $checkResult->free();

        $checkQuery->close();
        $updateQuery->close();

        $this->setResult($connection->affected_rows > 0);
    }
}