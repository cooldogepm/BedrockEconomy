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

use cooldogedev\BedrockEconomy\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\event\transaction\TransactionProcessEvent;
use cooldogedev\BedrockEconomy\event\transaction\TransactionSubmitEvent;

final class TransactionManager extends BedrockEconomyOwned
{
    protected static int $nextId;
    /**
     * @var Transaction[]
     */
    protected array $transactions;

    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);

        $this->transactions = [];
        TransactionManager::$nextId = 0;
    }

    public static function getNextId(): int
    {
        return TransactionManager::$nextId++;
    }

    public function submitTransaction(Transaction $transaction): bool
    {
        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        if ($this->hasTransaction($transaction->getId())) {
            return false;
        }

        $this->transactions[$transaction->getId()] = $transaction;

        return true;
    }

    public function hasTransaction(int $id): bool
    {
        return isset($this->transactions[$id]);
    }

    public function processTransaction(Transaction $transaction, bool $successful): bool
    {
        if (!$this->hasTransaction($transaction->getId())) {
            return false;
        }

        $event = new TransactionProcessEvent($transaction, $successful);
        $event->call();

        unset($this->transactions[$transaction->getId()]);

        return true;
    }

    public function getTransaction(int $id): ?Transaction
    {
        return $this->transactions[$id] ?? null;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }
}
