<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\event\balance;

final class BalanceTransferEvent extends BalanceEvent
{
    public function __construct(string $account, protected string $sender, protected int $amount)
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

    public function getSender(): string
    {
        return $this->sender;
    }
}
