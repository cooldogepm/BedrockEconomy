<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2024 cooldogedev
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @auto-license
 */

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