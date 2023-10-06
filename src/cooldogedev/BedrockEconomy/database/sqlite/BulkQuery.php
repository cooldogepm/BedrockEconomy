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

use cooldogedev\BedrockEconomy\database\exception\NoRecordsException;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class BulkQuery extends SQLiteQuery
{
    use TableHolder;

    protected string $list;

    public function __construct(array $list)
    {
        $this->list = igbinary_serialize(array_values($list));
    }

    /**
     * @throws NoRecordsException
     */
    public function onRun(SQLite3 $connection): void
    {
        $list = igbinary_unserialize($this->list);
        $params = implode(", ", array_fill(0, count($list), "?"));

        $statement = $connection->prepare("SELECT * FROM " . $this->table . " WHERE xuid IN (" . $params . ")");

        for ($i = 0; $i < count($list); $i++) {
            $statement->bindValue($i + 1, $list[$i]);
        }

        $result = $statement->execute();

        $rows = [];

        while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $rows[] = $row;
        }

        if (count($rows) === 0) {
            throw new NoRecordsException(
                _message: "No records found in table " . $this->table,
            );
        }

        $this->setResult($rows);

        $result->finalize();
        $statement->close();
    }
}
