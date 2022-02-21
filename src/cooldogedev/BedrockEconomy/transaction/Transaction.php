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

namespace cooldogedev\BedrockEconomy\transaction;

use Threaded;

final class Transaction extends Threaded
{
    public const TRANSACTION_TYPE_INCREMENT = 0;
    public const TRANSACTION_TYPE_DECREMENT = 1;
    public const TRANSACTION_TYPE_SET = 2;

    protected int $issueDate;

    public function __construct(
        protected int  $type,
        protected int  $value,
        protected ?int $balanceCap = null,
        ?int           $issueDate = null,
    )
    {
        $this->issueDate = $issueDate ?? time();
    }

    public function getIssueDate(): int
    {
        return $this->issueDate;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getBalanceCap(): ?int
    {
        return $this->balanceCap;
    }
}
