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

use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\BedrockEconomy\transaction\types\UpdateTransaction;
use cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class MySQLUpdateQuery extends MySQLQuery
{
    public function __construct(protected UpdateTransaction $transaction)
    {
    }

    public function onRun(mysqli $connection): void
    {
        $playerName = strtolower($this->getTransaction()->getTarget());
        $statement = $connection->prepare($this->getQuery());
        $statement->bind_param("s", $playerName);
        $statement->execute();
        $successful = $statement->affected_rows > 0;
        $statement->close();

        $this->setResult($successful);
    }

    public function getTransaction(): UpdateTransaction
    {
        return $this->transaction;
    }

    public function getQuery(): string
    {
        $statement = match ($this->getTransaction()->getType()) {
            Transaction::TRANSACTION_TYPE_INCREMENT => $this->getTransaction()->getBalanceCap() !== null ? "MIN (balance + " . $this->getTransaction()->getValue() . ", " . $this->getTransaction()->getBalanceCap() . ")" : "balance + " . $this->getTransaction()->getValue(),
            Transaction::TRANSACTION_TYPE_DECREMENT => "MAX (balance - " . $this->getTransaction()->getValue() . ", 0)",
            Transaction::TRANSACTION_TYPE_SET => $this->getTransaction()->getBalanceCap() !== null ? "MIN (" . $this->getTransaction()->getValue() . ", " . $this->getTransaction()->getBalanceCap() . ")" : $this->getTransaction()->getValue(),
        };

        return "UPDATE " . $this->getTable() . " SET balance = " . $statement . " WHERE username = ?";
    }
}
