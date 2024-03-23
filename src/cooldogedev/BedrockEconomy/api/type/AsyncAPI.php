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
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use Generator;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\SOFe\AwaitGenerator\Await;

final class AsyncAPI extends BaseAPI
{
    public function getName(): string
    {
        return "Async";
    }

    public function getVersion(): string
    {
        return "0.0.1";
    }

    public function bulk(array $list): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->bulk($list, $resolve, $reject)
        );
    }

    public function insert(string $xuid, string $username, int $balance, int $decimals): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->insert($xuid, $username, $balance, $decimals, $resolve, $reject)
        );
    }

    public function migrate(string $xuid, string $username, string $newXuid, string $newUsername): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->migrate($xuid, $username, $newXuid, $newUsername, $resolve, $reject)
        );
    }

    /**
     * WARNING: This method should only be used if you know what you are doing.
     * DO NOT use it for anything other than visual purposes such as leaderboards.
     * It is used internally by the plugin to update the cache.
     * It is recommended to use {@link GlobalCache::ONLINE()} instead.
     * @internal
     */
    public function get(string $xuid, string $username): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->get($xuid, $username, $resolve, $reject)
        );
    }

    public function top(int $limit, int $offset, bool $ascending): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->top($limit, $offset, $ascending, $resolve, $reject)
        );
    }

    public function transfer(array $source, array $target, int $amount, int $decimals): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->transfer($source, $target, $amount, $decimals, $resolve, $reject)
        );
    }

    public function add(string $xuid, string $username, int $amount, int $decimals): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->add($xuid, $username, $amount, $decimals, $resolve, $reject)
        );
    }

    public function subtract(string $xuid, string $username, int $amount, int $decimals): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->subtract($xuid, $username, $amount, $decimals, $resolve, $reject)
        );
    }

    public function set(string $xuid, string $username, int $amount, int $decimals): Generator
    {
        return yield from Await::promise(
            static fn (Closure $resolve, Closure $reject) => BedrockEconomyAPI::CLOSURE()->set($xuid, $username, $amount, $decimals, $resolve, $reject)
        );
    }
}