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

namespace cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\query;

use Closure;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\ConnectionPool;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\exception\SQLException;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\thread\SQLThread;
use pmmp\thread\Thread as NativeThread;
use pmmp\thread\ThreadSafe;
use Throwable;
use function assert;
use function igbinary_serialize;
use function igbinary_unserialize;
use function is_scalar;

abstract class SQLQuery extends ThreadSafe
{
    protected string $identifier = "";

    protected ?string $error = null;

    protected mixed $result = null;
    protected bool $resultSerialized = false;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    final public function run(): void
    {
        try {
            $this->onRun($this->getThread()->getConnection());
        } catch (Throwable $throwable) {
            $this->error = json_encode([
                "message" => $throwable->getMessage(),
                "code" => $throwable->getCode(),
                "trace" => $throwable->getTrace(),
                "trace_string" => $throwable->getTraceAsString(),
                "file" => $throwable->getFile(),
                "line" => $throwable->getLine(),
                "class" => $throwable instanceof SQLException ? $throwable::class : null
            ]);
        }
    }

    final public function getResult(): mixed
    {
        return $this->resultSerialized ? igbinary_unserialize($this->result) : $this->result;
    }

    final protected function setResult(mixed $result): void
    {
        $this->resultSerialized = !is_scalar($result) && !$result instanceof ThreadSafe;
        $this->result = $this->resultSerialized ? igbinary_serialize($result) : $result;
    }

    final public function getError(): ?string
    {
        return $this->error;
    }

    public function getThread(): SQLThread
    {
        $worker = NativeThread::getCurrentThread();
        assert($worker instanceof SQLThread);

        return $worker;
    }

    final public function execute(?Closure $onSuccess = null, ?Closure $onFail = null): void
    {
        ConnectionPool::getInstance()->submit($this, $onSuccess, $onFail);
    }
}