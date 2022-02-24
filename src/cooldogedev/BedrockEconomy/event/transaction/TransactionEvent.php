<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\event\transaction;

use cooldogedev\BedrockEconomy\transaction\Transaction;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

abstract class TransactionEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(protected Transaction $transaction)
    {
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
