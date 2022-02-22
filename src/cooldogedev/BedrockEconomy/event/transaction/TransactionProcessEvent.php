<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\event\transaction;

use cooldogedev\BedrockEconomy\transaction\Transaction;

/**
 * This event is called after the transaction query is executed and returned a result.
 */
final class TransactionProcessEvent extends TransactionEvent
{
    public function __construct(Transaction $transaction, protected bool $successful)
    {
        parent::__construct($transaction);
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }
}
