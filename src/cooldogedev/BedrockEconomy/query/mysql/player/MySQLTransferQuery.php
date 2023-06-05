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

namespace cooldogedev\BedrockEconomy\query\mysql\player;

use cooldogedev\BedrockEconomy\query\ErrorCodes;
use cooldogedev\BedrockEconomy\query\QueryManager;
use cooldogedev\BedrockEconomy\transaction\types\TransferTransaction;
use cooldogedev\libSQL\query\MySQLQuery;
use Exception;
use mysqli;

final class MySQLTransferQuery extends MySQLQuery
{
    protected const CHECK_TYPE_SUFFICIENCY = 0;
    protected const CHECK_TYPE_CAP = 1;

    public function __construct(protected TransferTransaction $transaction)
    {
    }

    public function onRun(mysqli $connection): void
    {
        $senderName = strtolower($this->getTransaction()->getSender());
        $receiverName = strtolower($this->getTransaction()->getReceiver());

        if (
            !$this->verifyAccount($connection, $senderName, MySQLTransferQuery::CHECK_TYPE_SUFFICIENCY) ||
            !$this->verifyAccount($connection, $receiverName, MySQLTransferQuery::CHECK_TYPE_CAP)
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

    protected function verifyAccount(mysqli $connection, string $username, int $checkType): bool
    {
        $amount = $this->getTransaction()->getAmount();
        $balanceCap = $this->getTransaction()->getBalanceCap();

        $statement = $connection->prepare($this->getRetrieveQuery());
        $statement->bind_param("s", $username);
        $statement->execute();

        $data = $statement->get_result()?->fetch_assoc();
        $isVerified = $data !== null;
        $balance = $data["balance"] ?? null;

        $statement->close();

        if (!$isVerified) {
            $this->setResult(false);
            throw new Exception(ErrorCodes::ERROR_CODE_ACCOUNT_NOT_FOUND . ":" . $username);
        }

        if ($checkType === MySQLTransferQuery::CHECK_TYPE_SUFFICIENCY && $balance < $amount) {
            $this->setResult(false);
            $isVerified = false;

            throw new Exception(ErrorCodes::ERROR_CODE_BALANCE_INSUFFICIENT . ":" . $username);
        } elseif ($checkType === MySQLTransferQuery::CHECK_TYPE_CAP && $balanceCap !== null && ($balance + $amount) > $balanceCap) {
            $this->setResult(false);
            $isVerified = false;

            throw new Exception(ErrorCodes::ERROR_CODE_BALANCE_CAP_EXCEEDED . ":" . $username);
        }

        return $isVerified;
    }

    public function getRetrieveQuery(): string
    {
        return "SELECT * FROM " . QueryManager::DATA_TABLE_PLAYERS . " WHERE username = ?";
    }

    protected function updateBalance(mysqli $connection, string $username, bool $deduction): bool
    {
        $statement = $connection->prepare($deduction ? $this->getDeductionQuery() : $this->getAdditionQuery());
        $statement->bind_param("s", $username);
        $statement->execute();
        $successful = $statement->affected_rows > 0;
        $statement->close();

        if (!$successful) {
            $this->setResult(false);
            throw new Exception(ErrorCodes::ERROR_CODE_NO_CHANGES_MADE . ":" . $username);
        }

        return true;
    }

    public function getDeductionQuery(): string
    {
        $amount = $this->getTransaction()->getAmount();
        $statement = "balance - $amount";

        return "UPDATE " . QueryManager::DATA_TABLE_PLAYERS . " SET balance = " . $statement . " WHERE username = ?";
    }

    public function getAdditionQuery(): string
    {
        $amount = $this->getTransaction()->getAmount();
        $statement = "balance + $amount";

        return "UPDATE " . QueryManager::DATA_TABLE_PLAYERS . " SET balance = " . $statement . " WHERE username = ?";
    }
}
