<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\transaction;

abstract class BaseTransaction
{
    public function __construct(public readonly int $amount, public readonly int $decimals) {}
}
