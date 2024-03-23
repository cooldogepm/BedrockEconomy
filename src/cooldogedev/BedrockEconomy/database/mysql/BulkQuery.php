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

use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class BulkQuery extends MySQLQuery
{
    use TableHolder;

    private readonly string $list;

    public function __construct(array $list)
    {
        $this->list = igbinary_serialize(array_values($list));
    }

    /**
     * @throws RecordNotFoundException
     */
    public function onRun(mysqli $connection): void
    {
        $list = igbinary_unserialize($this->list);
        $params = implode(", ", array_fill(0, count($list), "?"));

        $statement = $connection->prepare("SELECT *, COUNT(*) as position FROM " . $this->table . " WHERE xuid IN (" . $params . ") GROUP BY xuid, username, amount ORDER BY amount");
        $statement->bind_param(str_repeat("s", count($list)), ...$list);
        $statement->execute();

        $result = $statement->get_result();

        if ($result->num_rows === 0) {
            throw new RecordNotFoundException(
                _message: "No records found in table " . $this->table,
            );
        }

        $this->setResult($result->fetch_all(MYSQLI_ASSOC));

        $result->free();

        $statement->close();
    }
}