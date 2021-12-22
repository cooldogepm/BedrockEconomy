<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\transaction;

use cooldogedev\BedrockEconomy\constant\TransactionConstants;
use Threaded;

final class Transaction extends Threaded
{
    public function __construct(
        protected int $type,
        protected int $value,
        protected int $issueDate,
    )
    {
    }

    public function getIssueDate(): int
    {
        return $this->issueDate;
    }

    public function call(int $balance): int
    {
        return match ($this->getType()) {
            TransactionConstants::TRANSACTION_TYPE_INCREMENT => $balance + $this->getValue(),
            TransactionConstants::TRANSACTION_TYPE_DECREMENT => $balance - $this->getValue(),
            TransactionConstants::TRANSACTION_TYPE_SET => $this->getValue(),
            default => $balance,
        };
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
