<?php

/**
 *  Copyright (c) 2021-2023 cooldogedev
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

namespace cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\thread;

use mysqli;
use RuntimeException;

final class MySQLThread extends SQLThread
{
    protected static ?mysqli $connection = null;

    public function __construct(
        protected string $host,
        protected string $username,
        protected string $password,
        protected string $database,

        protected int $port,
    ) {
        parent::__construct();
    }

    protected function reconnect(): void
    {
        self::$connection = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);

        if (self::$connection->connect_error) {
            throw new RuntimeException(self::$connection->connect_error, self::$connection->connect_errno);
        }
    }

    protected function onRun(): void
    {
        if (self::$connection === null || !self::$connection->ping()) {
            $this->reconnect();
        }

        parent::onRun();
    }

    public function getConnection(): mysqli
    {
        if (self::$connection === null || !self::$connection->ping()) {
            $this->reconnect();
        }

        return self::$connection;
    }
}