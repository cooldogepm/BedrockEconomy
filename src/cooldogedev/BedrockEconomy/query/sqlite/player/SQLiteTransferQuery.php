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
    public function __construct(protected TransferTransaction $transaction)
    {
    }

    public function onRun(SQLite3 $connection): void
    {
        $senderName = strtolower($this->getTransaction()->getSender());
        $receiverName = strtolower($this->getTransaction()->getReceiver());

        /*
         * Deduct the amount from the sender's balance
         */
        $statement = $connection->prepare($this->getDeductionQuery());
        $statement->bindValue(":username", strtolower($senderName));
        // There's a bug with SQLite3::prepare() that causes the statement to be executed multiple times
        $statement->execute()?->finalize();
        $statement->close();

        if ($connection->changes() <= 0) {
            $this->setError(ErrorCodes::ERROR_CODE_TARGET_NOT_FOUND . ": " . $senderName);
            $this->setResult(false);
            return;
        }

        /*
         * Add the amount to the receiver's balance
         */
        $statement = $connection->prepare($this->getAdditionQuery());
        $statement->bindValue(":username", strtolower($receiverName));
        // There's a bug with SQLite3::prepare() that causes the statement to be executed multiple times
        $statement->execute()?->finalize();
        $statement->close();

        if ($connection->changes() === 0) {
            $this->setError(ErrorCodes::ERROR_CODE_TARGET_NOT_FOUND . ": " . $receiverName);
            $this->setResult(false);
        } else {
            $this->setResult(true);
        }
    }

    public function getTransaction(): TransferTransaction
    {
        return $this->transaction;
    }

    public function getDeductionQuery(): string
    {
        $statement = "MAX (balance - " . $this->getTransaction()->getAmount() . ", 0)";

        return "UPDATE " . $this->getTable() . " SET balance = " . $statement . " WHERE username = :username";
    }

    public function getAdditionQuery(): string
    {
        $statement = "MIN (balance + " . $this->getTransaction()->getAmount() . ", 0)";

        return "UPDATE " . $this->getTable() . " SET balance = " . $statement . " WHERE username = :username";
    }
}
