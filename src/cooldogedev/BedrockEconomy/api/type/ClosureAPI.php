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
use cooldogedev\BedrockEconomy\api\util\ClosureWrapper;
use cooldogedev\BedrockEconomy\database\constant\UpdateMode;
use cooldogedev\BedrockEconomy\database\QueryManager;
use cooldogedev\BedrockEconomy\database\transaction\TransferTransaction;
use cooldogedev\BedrockEconomy\database\transaction\UpdateTransaction;
use cooldogedev\BedrockEconomy\event\transaction\TransactionFailEvent;
use cooldogedev\BedrockEconomy\event\transaction\TransactionSubmitEvent;
use cooldogedev\BedrockEconomy\event\transaction\TransactionSuccessEvent;
use function array_map;
use function explode;
use function number_format;

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
        QueryManager::BULK($list)->execute(
            onSuccess: fn (array $rows) => $onSuccess(array_map(fn (array $row) => [
                ...$row,
                ... $this->transformAmount($row["amount"]),
            ], $rows)),
            onFail: $onError,
        );
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
        QueryManager::RETRIEVE($xuid, $username)
            ->execute(
                onSuccess: fn (array $result) => $onSuccess([
                    ... $result,
                    ... $this->transformAmount($result["amount"]),
                ]),
                onFail: $onError,
            );
    }

    public function top(int $limit, int $offset, bool $ascending, Closure $onSuccess, Closure $onError): void
    {
        QueryManager::TOP($limit, $offset, $ascending)->execute(
            onSuccess: fn (array $rows) => $onSuccess(array_map(fn (array $row) => [
                ...$row,
                ... $this->transformAmount($row["amount"]),
            ], $rows)),
            onFail: $onError,
        );
    }

    public function transfer(array $source, array $target, int $amount, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        $transaction = new TransferTransaction($source, $target, $amount, $decimals);

        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            return;
        }

        QueryManager::TRANSFER($source, $target, $amount, $decimals)->execute(
            onSuccess: ClosureWrapper::combine($onSuccess, static fn() => (new TransactionSuccessEvent($transaction))->call()),
            onFail: ClosureWrapper::combine($onError, static fn() => (new TransactionFailEvent($transaction))->call()),
        );
    }

    public function add(string $xuid, string $username, int $amount, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        $transaction = new UpdateTransaction($username, $xuid, UpdateMode::ADD, $amount, $decimals);

        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            return;
        }

        QueryManager::UPDATE($xuid, $username, UpdateMode::ADD, $amount, $decimals)->execute(
            onSuccess: ClosureWrapper::combine($onSuccess, static fn() => (new TransactionSuccessEvent($transaction))->call()),
            onFail: ClosureWrapper::combine($onError, static fn() => (new TransactionFailEvent($transaction))->call()),
        );
    }

    public function subtract(string $xuid, string $username, int $amount, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        $transaction = new UpdateTransaction($username, $xuid, UpdateMode::SUBTRACT, $amount, $decimals);

        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            return;
        }

        QueryManager::UPDATE($xuid, $username, UpdateMode::SUBTRACT, $amount, $decimals)->execute(
            onSuccess: ClosureWrapper::combine($onSuccess, static fn() => (new TransactionSuccessEvent($transaction))->call()),
            onFail: ClosureWrapper::combine($onError, static fn() => (new TransactionFailEvent($transaction))->call()),
        );
    }

    public function set(string $xuid, string $username, int $amount, int $decimals, Closure $onSuccess, Closure $onError): void
    {
        $transaction = new UpdateTransaction($username, $xuid, UpdateMode::SET, $amount, $decimals);

        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            return;
        }

        QueryManager::UPDATE($xuid, $username, UpdateMode::SET, $amount, $decimals)->execute(
            onSuccess: ClosureWrapper::combine($onSuccess, static fn() => (new TransactionSuccessEvent($transaction))->call()),
            onFail: ClosureWrapper::combine($onError, static fn() => (new TransactionFailEvent($transaction))->call()),
        );
    }

    private function transformAmount(float $amount): array
    {
        $amount = number_format($amount, 2, ".", "");
        $amount = explode(".", $amount);
        return [
            "amount" => (int) $amount[0],
            "decimals" => (int) $amount[1],
        ];
    }
}