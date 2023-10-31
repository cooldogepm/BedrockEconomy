<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\currency;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\database\constant\Limits;

/**
 * @deprecated This class is deprecated and will be removed in the future.
 * Use the new API instead. @see BedrockEconomy::getCurrency()
 */
final class CurrencyManager
{
    public function __construct(private readonly Currency $currency) {}

    public function hasBalanceCap(): bool
    {
        return true;
    }

    public function getNumberSeparator(): string
    {
        return ",";
    }

    public function getSymbol(): string
    {
        return $this->currency->symbol;
    }

    public function getDefaultBalance(): int
    {
        return $this->currency->defaultAmount;
    }

    public function getName(): string
    {
        return $this->currency->name;
    }

    public function getMinimumPayment(): int
    {
        return 1;
    }

    public function hasBalanceLimit(): bool
    {
        return true;
    }

    public function getBalanceCap(): int
    {
        return Limits::INT63_MAX;
    }

    public function hasPaymentLimit(): bool
    {
        return true;
    }

    public function getMaximumPayment(): int
    {
        return Limits::INT63_MAX;
    }
}
