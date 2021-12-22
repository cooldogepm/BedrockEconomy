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

namespace cooldogedev\BedrockEconomy\database\query\player\sqlite;

use cooldogedev\BedrockEconomy\constant\SearchConstants;
use cooldogedev\BedrockEconomy\constant\TransactionConstants;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class SQLitePlayerUpdateQuery extends SQLiteQuery
{
    public function __construct(protected string $searchValue, protected Transaction $transaction, protected int $searchMode = SearchConstants::SEARCH_MODE_XUID)
    {
        parent::__construct();
    }

    public function handleIncomingConnection(SQLite3 $connection): bool
    {
        // There's a bug with SQLite3::prepare()
        $statement = $connection->prepare($this->getSecondaryQuery());
        $statement->bindValue(":searchValue", $this->getSearchValue());
        $result = $statement->execute()?->fetchArray(SQLITE3_ASSOC) ?: null;
        $statement->close();
        $hasAccount = $result !== null;

        $statement = $connection->prepare($this->getQuery());
        $statement->bindValue(":searchValue", $this->getSearchValue());
        // There's a bug with SQLite3::prepare() that causes the statement to be executed multiple times
        $statement->execute()?->finalize();
        $statement->close();

        return $hasAccount;
    }

    public function getSecondaryQuery(): string
    {
        return "SELECT * FROM " . $this->getTable() . " WHERE " . ($this->getSearchMode() === SearchConstants::SEARCH_MODE_XUID ? "xuid" : "username") . " = :searchValue";
    }

    public function getSearchMode(): int
    {
        return $this->searchMode;
    }

    public function getSearchValue(): string
    {
        return $this->searchValue;
    }

    public function getQuery(): string
    {
        $statement = match ($this->getTransaction()->getType()) {
            TransactionConstants::TRANSACTION_TYPE_INCREMENT => "balance + " . $this->getTransaction()->getValue(),
            TransactionConstants::TRANSACTION_TYPE_DECREMENT => "balance - " . $this->getTransaction()->getValue(),
            TransactionConstants::TRANSACTION_TYPE_SET => $this->getTransaction()->getValue()
        };
        return "UPDATE " . $this->getTable() . " SET balance = " . $statement . " WHERE " . ($this->getSearchMode() === SearchConstants::SEARCH_MODE_XUID ? "xuid" : "username") . " = :searchValue";
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
