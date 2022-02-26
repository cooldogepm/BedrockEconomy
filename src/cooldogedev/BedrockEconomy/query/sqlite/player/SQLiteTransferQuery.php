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

namespace cooldogedev\BedrockEconomy\query\sqlite\player;

use cooldogedev\BedrockEconomy\query\ErrorCodes;
use cooldogedev\BedrockEconomy\transaction\types\TransferTransaction;
use cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class SQLiteTransferQuery extends SQLiteQuery
{
    protected const CHECK_TYPE_SUFFICIENCY = 0;
    protected const CHECK_TYPE_CAP = 1;

    public function __construct(protected TransferTransaction $transaction)
    {
    }

    public function onRun(SQLite3 $connection): void
    {
        $senderName = strtolower($this->getTransaction()->getSender());
        $receiverName = strtolower($this->getTransaction()->getReceiver());

        if (
            !$this->verifyAccount($connection, $senderName, SQLiteTransferQuery::CHECK_TYPE_SUFFICIENCY) ||
            !$this->verifyAccount($connection, $receiverName, SQLiteTransferQuery::CHECK_TYPE_CAP)
        ) {
            return;
        }

        $this->setResult(
            $this->updateBalance($connection, $senderName, true) &&
            $this->updateBalance($connection, $receiverName, false)
        );
    }

    public function getTransaction(): TransferTransaction
    {
        return $this->transaction;
    }

    protected function verifyAccount(SQLite3 $connection, string $username, int $checkType): bool
    {
        $amount = $this->getTransaction()->getAmount();
        $balanceCap = $this->getTransaction()->getBalanceCap();

        $statement = $connection->prepare($this->getRetrieveQuery());
        $statement->bindValue(":username", $username);

        $data = $statement->execute()?->fetchArray(SQLITE3_ASSOC) ?: null;
        $isVerified = $data !== null;
        $balance = $data["balance"] ?? null;

        $statement->close();

        if (!$isVerified) {
            $this->setError(ErrorCodes::ERROR_CODE_ACCOUNT_NOT_FOUND . ":" . $username);
            $this->setResult(false);
        }

        if ($isVerified && $checkType === SQLiteTransferQuery::CHECK_TYPE_SUFFICIENCY && $balance < $amount) {
            $isVerified = false;

            $this->setError(ErrorCodes::ERROR_CODE_BALANCE_INSUFFICIENT . ":" . $username);
            $this->setResult(false);
        }

        if ($isVerified && $checkType === SQLiteTransferQuery::CHECK_TYPE_CAP && $balanceCap !== null && $balance >= $balanceCap) {
            $isVerified = false;

            $this->setError(ErrorCodes::ERROR_CODE_BALANCE_CAP_EXCEEDED . ":" . $username);
            $this->setResult(false);
        }

        if ($isVerified && $checkType === SQLiteTransferQuery::CHECK_TYPE_CAP && $balanceCap !== null && ($balance + $amount) > $balanceCap) {
            $isVerified = false;

            $this->setError(ErrorCodes::ERROR_CODE_NEW_BALANCE_EXCEEDS_CAP . ":" . $username);
            $this->setResult(false);
        }

        return $isVerified;
    }

    public function getRetrieveQuery(): string
    {
        return "SELECT * FROM " . $this->getTable() . " WHERE username = :username";
    }

    // Copied from SQLiteRetrieveQuery

    protected function updateBalance(SQLite3 $connection, string $username, bool $deduction): bool
    {
        $statement = $connection->prepare($deduction ? $this->getDeductionQuery() : $this->getAdditionQuery());
        $statement->bindValue(":username", strtolower($username));
        // There's a bug with SQLite3::prepare() that causes the statement to be executed multiple times
        $statement->execute()?->finalize();
        $statement->close();

        if ($connection->changes() <= 0) {
            $this->setError(ErrorCodes::ERROR_CODE_NO_CHANGES_MADE . ":" . $username);
            $this->setResult(false);
            return false;
        }

        return true;
    }

    public function getDeductionQuery(): string
    {
        $value = $this->getTransaction()->getAmount();
        $statement = "IIF (balance - " . $value . " >= 0, balance - " . $value . ", balance)";

        return "UPDATE " . $this->getTable() . " SET balance = " . $statement . " WHERE username = :username";
    }

    public function getAdditionQuery(): string
    {
        $value = $this->getTransaction()->getAmount();
        $balanceCap = $this->getTransaction()->getBalanceCap();
        $statement = $balanceCap !== null ? "IIF (balance + " . $value . " <= " . $balanceCap . ", balance + " . $value . ", balance)" : "balance + " . $value;

        return "UPDATE " . $this->getTable() . " SET balance = " . $statement . " WHERE username = :username";
    }
}
