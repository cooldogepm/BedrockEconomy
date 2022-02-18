<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\transaction;

use Threaded;

final class Transaction extends Threaded
{
    public const TRANSACTION_TYPE_INCREMENT = 0;
    public const TRANSACTION_TYPE_DECREMENT = 1;
    public const TRANSACTION_TYPE_SET = 2;

    protected int $issueDate;

    public function __construct(
        protected int  $type,
        protected int  $value,
        protected ?int $balanceCap = null,
        ?int           $issueDate = null,
    )
    {
        $this->issueDate = $issueDate ?? time();
    }

    public function getIssueDate(): int
    {
        return $this->issueDate;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getBalanceCap(): ?int
    {
        return $this->balanceCap;
    }
}
