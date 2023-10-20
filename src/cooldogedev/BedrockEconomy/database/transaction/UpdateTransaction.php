<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\transaction;

final class UpdateTransaction extends BaseTransaction
{
    public function __construct(
        public readonly string $username,
        public readonly string $xuid,

        public readonly int $mode,

        int $amount,
        int $decimals,
    ) {
        parent::__construct($amount, $decimals);
    }
}
