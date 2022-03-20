<?php

/**
 *  Copyright (c) 2022 cooldogedev
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\currency;

use cooldogedev\BedrockEconomy\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\BedrockEconomy;

final class CurrencyManager extends BedrockEconomyOwned
{
    protected string $name;
    protected string $symbol;
    protected string $numberSeparator;

    protected int $defaultBalance;
    protected int $balanceCap;
    protected bool $hasBalanceCap;

    protected int $minimumPayment;
    protected int $maximumPayment;


    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);

        $this->name = $this->getPlugin()->getConfigManager()->getCurrencyConfig()["name"];
        $this->symbol = $this->getPlugin()->getConfigManager()->getCurrencyConfig()["symbol"];
        $this->numberSeparator = $this->getPlugin()->getConfigManager()->getCurrencyConfig()["number-separator"];

        $balanceConfig = $this->getPlugin()->getConfigManager()->getCurrencyConfig()["balance"];
        $this->defaultBalance = $balanceConfig["default-balance"];
        $this->balanceCap = $balanceConfig["balance-cap"];
        $this->hasBalanceCap = $balanceConfig["enable-balance-cap"];

        $paymentConfig = $this->getPlugin()->getConfigManager()->getCurrencyConfig()["payment"];
        $this->minimumPayment = $paymentConfig["minimum-payment"];
        $this->maximumPayment = $paymentConfig["maximum-payment"];
    }

    public function hasBalanceCap(): bool
    {
        return $this->hasBalanceCap;
    }

    public function getNumberSeparator(): string
    {
        return $this->numberSeparator;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getDefaultBalance(): int
    {
        return $this->defaultBalance;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMinimumPayment(): int
    {
        return $this->minimumPayment;
    }

    public function hasBalanceLimit(): bool
    {
        return $this->getBalanceCap() > -1;
    }

    public function getBalanceCap(): ?int
    {
        return $this->hasBalanceCap ? $this->balanceCap : null;
    }

    public function hasPaymentLimit(): bool
    {
        return $this->getMaximumPayment() > -1;
    }

    public function getMaximumPayment(): int
    {
        return $this->maximumPayment;
    }
}
