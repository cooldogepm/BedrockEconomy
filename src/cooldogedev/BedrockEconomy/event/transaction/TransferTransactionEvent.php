<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\event\transaction;

final class TransferTransactionEvent extends TransactionEvent
{
    public function __construct(
        public readonly array $source,
        public readonly array $target,

        public readonly int $amount,
        public readonly int $decimals,
    ) {}
}
