<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2023 cooldogedev
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
use cooldogedev\BedrockEconomy\database\constant\UpdateMode;
use cooldogedev\BedrockEconomy\database\QueryManager;

final class ClosureAPI extends BaseAPI
{
    public function getName(): string
    {
        return "Closure";
    }

    public function getVersion(): string
    {
        return "0.0.1";
    }

    public function bulk(array $list, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::BULK($list)->execute($onSuccess, $onError);
    }

    public function insert(string $xuid, string $username, int $balance, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::INSERT($xuid, $username, $balance, $decimals)->execute($onSuccess, $onError);
    }

    public function migrate(string $xuid, string $username, string $newXuid, string $newUsername, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::MIGRATE($xuid, $username, $newXuid, $newUsername)->execute($onSuccess, $onError);
    }

    /**
     * WARNING: This method should only be used if you know what you are doing.
     * DO NOT use it for anything other than visual purposes such as leaderboards.
     * It is used internally by the plugin to update the cache.
     * It is recommended to use {@link GlobalCache::ONLINE()} instead.
     * @internal
     */
    public function get(string $xuid, string $username, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::RETRIEVE($xuid, $username)->execute($onSuccess, $onError);
    }

    public function top(int $limit, int $offset, bool $ascending, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::TOP($limit, $offset, $ascending)->execute($onSuccess, $onError);
    }

    public function transfer(array $source, array $target, int $amount, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::TRANSFER($source, $target, $amount, $decimals)->execute($onSuccess, $onError);
    }

    public function add(string $xuid, string $username, int $amount, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::UPDATE($xuid, $username, UpdateMode::ADD, $amount, $decimals)->execute($onSuccess, $onError);
    }

    public function subtract(string $xuid, string $username, int $amount, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::UPDATE($xuid, $username, UpdateMode::SUBTRACT, $amount, $decimals)->execute($onSuccess, $onError);
    }

    public function set(string $xuid, string $username, int $amount, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::UPDATE($xuid, $username, UpdateMode::SET, $amount, $decimals)->execute($onSuccess, $onError);
    }
}
