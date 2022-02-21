<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\event\balance;

final class BalanceSetEvent extends BalanceEvent
{
    public function __construct(string $account, protected string $issuer, protected int $amount)
    {
        parent::__construct($account);
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getIssuer(): string
    {
        return $this->issuer;
    }
}
