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

namespace cooldogedev\BedrockEconomy\query\mysql\player;

use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class MySQLPlayerUpdateQuery extends MySQLQuery
{
    public function __construct(protected string $playerName, protected Transaction $transaction)
    {
    }

    public function onRun(mysqli $connection): void
    {
        $playerName = $this->getPlayerName();
        $statement = $connection->prepare($this->getQuery());
        $statement->bind_param("s", $playerName);
        $statement->execute();
        $successful = $statement->affected_rows > 0;
        $statement->close();

        $this->setResult($successful);
    }

    public function getPlayerName(): string
    {
        return $this->playerName;
    }

    public function getQuery(): string
    {
        $statement = match ($this->getTransaction()->getType()) {
            Transaction::TRANSACTION_TYPE_INCREMENT => "balance + " . $this->getTransaction()->getValue(),
            Transaction::TRANSACTION_TYPE_DECREMENT => "balance - " . $this->getTransaction()->getValue(),
            Transaction::TRANSACTION_TYPE_SET => $this->getTransaction()->getValue()
        };
        return "UPDATE " . $this->getTable() . " SET balance = " . $statement . " WHERE username = ?";
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
