<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\event\transaction;

abstract class UpdateTransactionEvent extends TransactionEvent
{
    public function __construct(
        public readonly string $xuid,
        public readonly string $username,

        public readonly int $amount,
        public readonly int $decimals,
    ) {}
}
