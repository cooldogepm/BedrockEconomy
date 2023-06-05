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

use cooldogedev\BedrockEconomy\query\QueryManager;
use cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class SQLiteBulkRetrieveQuery extends SQLiteQuery
{
    public function __construct(protected ?int $limit = null, protected ?int $offset = null)
    {
    }

    public function onRun(SQLite3 $connection): void
    {
        $players = [];
        $result = $connection->query($this->getQuery());
        if ($result) {
            while ($player = $result->fetchArray(SQLITE3_ASSOC)) {
                $players[] = $player;
            }
        }

        $this->setResult($players);
    }

    public function getQuery(): string
    {
        return $this->getLimit() ? "SELECT * FROM " . QueryManager::DATA_TABLE_PLAYERS . " ORDER BY balance DESC LIMIT " . $this->getLimit() . " OFFSET " . ($this->getOffset() ?? 0) : "SELECT * FROM " . QueryManager::DATA_TABLE_PLAYERS;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }
}
