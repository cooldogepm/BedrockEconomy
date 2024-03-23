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
use cooldogedev\BedrockEconomy\database\helper\ReferenceHolder;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class TopQuery extends MySQLQuery
{
    use ReferenceHolder;
    use TableHolder;

    public function __construct(private readonly int $limit, private readonly int $offset, private readonly bool $ascending) {}

    /**
     * @throws RecordNotFoundException
     */
    public function onRun(mysqli $connection): void
    {
        $query = match ($this->ascending) {
            true => "SELECT * FROM " . $this->table . " ORDER BY amount LIMIT ? OFFSET ?",
            false => "SELECT * FROM " . $this->table . " ORDER BY amount DESC LIMIT ? OFFSET ?"
        };

        $statement = $connection->prepare($query);
        $statement->bind_param("ii", $this->getRef($this->limit), $this->getRef($this->offset));
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