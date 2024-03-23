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

namespace cooldogedev\BedrockEconomy\api\type;

use Closure;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\constant\Search;

/**
 * @deprecated This API is deprecated and will be removed in the future.
 * Use the new APIs instead. (Async, Closure) @see BedrockEconomyAPI
 */
final class LegacyAPI extends BaseAPI
{
    public function getName(): string
    {
        return "Legacy";
    }

    public function getVersion(): string
    {
        return "1.0.0";
    }

    public function setPlayerBalance(string $username, int $amount, ?Closure $handler = null): void
    {
        $handler ??= fn () => null;
        BedrockEconomyAPI::CLOSURE()->set(Search::EMPTY, $username, $amount, 0, fn () => $handler(true), fn () => $handler(false));
    }

    public function getPlayerBalance(string $username, ?Closure $handler = null): void
    {
        $handler ??= fn () => null;
        BedrockEconomyAPI::CLOSURE()->get(Search::EMPTY, $username, fn (array $data) => $handler($data["amount"]), fn () => $handler(null));
    }

    public function addToPlayerBalance(string $username, int $amount, ?Closure $handler = null): void
    {
        $handler ??= fn () => null;
        BedrockEconomyAPI::CLOSURE()->add(Search::EMPTY, $username, $amount, 0, fn () => $handler(true), fn () => $handler(false));
    }

    public function subtractFromPlayerBalance(string $username, int $amount, ?Closure $handler = null): void
    {
        $handler ??= fn () => null;
        BedrockEconomyAPI::CLOSURE()->subtract(Search::EMPTY, $username, $amount, 0, fn () => $handler(true), fn () => $handler(false));
    }

    public function transferFromPlayerBalance(string $source, string $target, int $amount, ?Closure $handler = null): void
    {
        $handler ??= fn () => null;
        BedrockEconomyAPI::CLOSURE()->transfer(
            source: [
                "xuid" => Search::EMPTY,
                "username" => $source
            ],
            target: [
                "xuid" => Search::EMPTY,
                "username" => $target
            ],
            amount: $amount,
            decimals: 0,
            onSuccess: fn () => $handler(true),
            onError: fn () => $handler(false)
        );
    }
}