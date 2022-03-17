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

namespace cooldogedev\BedrockEconomy\api\version;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\event\transaction\TransactionSubmitEvent;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\BedrockEconomy\transaction\types\TransferTransaction;
use cooldogedev\BedrockEconomy\transaction\types\UpdateTransaction;
use cooldogedev\libSQL\context\ClosureContext;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\utils\SingletonTrait;

/*
 * This API is subject to change.
 */

final class BetaBEAPI implements IBedrockEconomyAPI
{
    use SingletonTrait {
        setInstance as protected;
        getInstance as protected _getInstance;
    }

    public static function getInstance(): BetaBEAPI
    {
        return self::_getInstance();
    }

    public function getVersion(): string
    {
        return "Beta";
    }

    /**
     * Returns the current balance of the given account.
     *
     * @param string $player
     * @return Promise
     */
    public function get(string $player): Promise
    {
        $promise = new PromiseResolver();

        $this->getPlugin()->getAccountManager()->getBalance($player, ClosureContext::create(
            function (?int $balance) use ($promise) {
                $balance !== null ? $promise->resolve($balance) : $promise->reject();
            }
        ));

        return $promise->getPromise();
    }

    protected function getPlugin(): BedrockEconomy
    {
        return BedrockEconomy::getInstance();
    }

    /**
     * Sets the balance of a player to the specified amount.
     *
     * @param string $player
     * @param int $balance
     * @param string|null $issuer
     * @return Promise
     */
    public function set(string $player, int $balance, ?string $issuer = null): Promise
    {
        $promise = new PromiseResolver();

        $transaction = new UpdateTransaction(
            Transaction::TRANSACTION_TYPE_SET,
            $issuer,
            $player,
            $balance,
        );

        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            $promise->reject();

            return $promise->getPromise();
        }

        $this->getPlugin()->getAccountManager()->updateBalance($transaction, ClosureContext::create(
            function (bool $changed) use ($promise) {
                $changed === true ? $promise->resolve(null) : $promise->reject();
            }
        ));

        return $promise->getPromise();
    }

    /**
     * Deducts the specified amount from the specified player's balance.
     *
     * @param string $player
     * @param int $balance
     * @param string|null $issuer
     * @return Promise
     */
    public function deduct(string $player, int $balance, ?string $issuer = null): Promise
    {
        $promise = new PromiseResolver();

        $transaction = new UpdateTransaction(
            Transaction::TRANSACTION_TYPE_DECREMENT,
            $issuer,
            $player,
            $balance,
        );

        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            $promise->reject();

            return $promise->getPromise();
        }

        $this->getPlugin()->getAccountManager()->updateBalance($transaction, ClosureContext::create(
            function (bool $deducted) use ($promise) {
                $deducted === true ? $promise->resolve(null) : $promise->reject();
            }
        ));

        return $promise->getPromise();
    }

    /**
     * Adds the given amount to the balance of the given player.
     *
     * @param string $player
     * @param int $balance
     * @param string|null $issuer
     * @return Promise
     */
    public function add(string $player, int $balance, ?string $issuer = null): Promise
    {
        $promise = new PromiseResolver();

        $transaction = new UpdateTransaction(
            Transaction::TRANSACTION_TYPE_INCREMENT,
            $issuer,
            $player,
            $balance,
        );

        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            $promise->reject();

            return $promise->getPromise();
        }

        $this->getPlugin()->getAccountManager()->updateBalance($transaction, ClosureContext::create(
            function (bool $added) use ($promise) {
                $added === true ? $promise->resolve(null) : $promise->reject();
            }
        ));

        return $promise->getPromise();
    }

    /**
     * Deletes the account of the given player if it exists.
     *
     * @param string $player
     * @return Promise
     */
    public function delete(string $player): Promise
    {
        $promise = new PromiseResolver();

        $this->getPlugin()->getAccountManager()->deleteAccount($player, ClosureContext::create(
            function (bool $deleted) use ($promise) {
                $deleted === true ? $promise->resolve(null) : $promise->reject();
            }
        ));

        return $promise->getPromise();
    }

    /**
     * Checks if the given player has an account.
     *
     * @param string $username
     * @return Promise
     */
    public function isRegistered(string $username): Promise
    {
        $promise = new PromiseResolver();

        $this->getPlugin()->getAccountManager()->hasAccount($username, ClosureContext::create(
            function (bool $registered) use ($promise) {
                $registered === true ? $promise->resolve(null) : $promise->reject();
            }
        ));

        return $promise->getPromise();
    }

    /**
     * Returns a sorted list of X accounts starting from offset Y.
     *
     * @param int $limit
     * @param int|null $offset
     * @return Promise
     */
    public function getSortedBalances(int $limit, int $offset = null): Promise
    {
        $promise = new PromiseResolver();

        $this->getPlugin()->getAccountManager()->getHighestBalances($limit, ClosureContext::create(
            function (?array $balances) use ($promise) {
                $balances !== null ? $promise->resolve($balances) : $promise->reject();
            }
        ), $offset);

        return $promise->getPromise();
    }

    /**
     * Creates a new account for the given player.
     *
     * @param string $player
     * @param int|null $balance
     * @return Promise
     */
    public function create(string $player, ?int $balance = null): Promise
    {
        $promise = new PromiseResolver();

        $this->getPlugin()->getAccountManager()->createAccount($player, ClosureContext::create(
            function (bool $created) use ($promise) {
                $created === true ? $promise->resolve(null) : $promise->reject();
            }
        ), $balance);

        return $promise->getPromise();
    }


    /**
     * Transfers the given amount of money from the source account to the target account.
     *
     * @param string $source
     * @param string $target
     * @param int $amount
     * @return Promise
     */
    public function transfer(string $source, string $target, int $amount): Promise
    {
        $promise = new PromiseResolver();

        $transaction = new TransferTransaction($source, $target, $amount);

        $this->getPlugin()->getAccountManager()->transferFromBalance($transaction, ClosureContext::create(
            function (bool $transferred) use ($promise) {
                $transferred === true ? $promise->resolve(null) : $promise->reject();
            }
        ));

        return $promise->getPromise();
    }

    public function getChannel(): string
    {
        return "beta";
    }
}
