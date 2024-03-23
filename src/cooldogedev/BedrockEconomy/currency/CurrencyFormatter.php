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

use InvalidArgumentException;

final class CurrencyFormatter
{
    private const COMPACT = "compact";
    private const COMMADOT = "commadot";

    public function __construct(private readonly Currency $currency) {}

    public function format(int $amount, int $decimals): string
    {
        return match ($this->currency->format) {
            CurrencyFormatter::COMPACT => CurrencyFormatter::compact($amount, $this->currency->decimals ? $decimals : null),
            CurrencyFormatter::COMMADOT => CurrencyFormatter::commadot($amount, $this->currency->decimals ? $decimals : null),

            default => throw new InvalidArgumentException("Invalid formatter " . $this->currency->format),
        };
    }

    public  function compact(int $number, ?int $decimals): string
    {
        $str = match (true) {
            $number >= 1_000_000_000_000_000_000 => round($number / 1_000_000_000_000_000, 2) . "Q",
            $number >= 1_000_000_000_000_000 => round($number / 1_000_000_000_000_000, 2) . "q",
            $number >= 1_000_000_000_000 => round($number / 1_000_000_000_000, 2) . "t",
            $number >= 1_000_000_000 => round($number / 1_000_000_000, 2) . "B",
            $number >= 1_000_000 => round($number / 1_000_000, 2) . "M",
            $number >= 1_000 => round($number / 1_000, 2) . "K",

            default => (string)$number,
        };

        if ($decimals !== null && $decimals < 10) {
            $decimals = "0" . $decimals;
        }

        if ($decimals === "00") {
            $decimals = null;
        }

        return $this->currency->symbol . $str . ($decimals !== null && $str === (string)$number ? "." . $decimals : "");
    }

    public function commadot(int $number, ?int $decimals): string
    {
        $number = (string)$number;
        $length = strlen($number);
        $formatted = "";
        $i = 0;

        while ($i < $length) {
            $formatted .= $number[$i];
            if (($length - $i) % 3 === 1 && $i !== $length - 1) {
                $formatted .= ",";
            }
            $i++;
        }

        if ($decimals !== null && $decimals < 10) {
            $decimals = "0" . $decimals;
        }

        return $this->currency->symbol . $formatted . ($decimals !== null ? "." . $decimals : "");
    }
}