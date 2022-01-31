<?php

/**
 *  Copyright (c) 2021 cooldogedev
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

namespace cooldogedev\BedrockEconomy\api;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\event\TransactionSubmitEvent;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\libSQL\context\ClosureContext;
use cooldogedev\libSQL\query\SQLQuery;
use pocketmine\utils\SingletonTrait;

final class BedrockEconomyAPI
{
    use SingletonTrait {
        setInstance as protected;
        getInstance as protected _getInstance;
    }

    protected static bool $registered = false;
    protected static ?BedrockEconomy $plugin = null;

    public function __construct()
    {
        BedrockEconomyAPI::setInstance($this);
    }

    public static function register(BedrockEconomy $bedrockEconomy): bool
    {
        if (BedrockEconomyAPI::isRegistered()) {
            return false;
        }
        BedrockEconomyAPI::setRegistered(true);
        BedrockEconomyAPI::setPlugin($bedrockEconomy);
        return true;
    }

    public static function isRegistered(): bool
    {
        return BedrockEconomyAPI::$registered;
    }

    protected static function setRegistered(bool $registered): void
    {
        BedrockEconomyAPI::$registered = $registered;
    }

    public static function getInstance(): BedrockEconomyAPI
    {
        return BedrockEconomyAPI::_getInstance();
    }

    public function setPlayerBalance(string $username, int $balance, ?ClosureContext $context = null): ?SQLQuery
    {
        $transaction = new Transaction(Transaction::TRANSACTION_TYPE_SET, $balance, time());

        $event = new TransactionSubmitEvent($username, $transaction);
        $event->call();

        if ($event->isCancelled()) {
            return null;
        }

        return $this->getPlugin()->getAccountManager()->updateBalance($username, $transaction, $context ?? ClosureContext::create());
    }

    public static function getPlugin(): ?BedrockEconomy
    {
        return BedrockEconomyAPI::$plugin;
    }

    protected static function setPlugin(?BedrockEconomy $plugin): void
    {
        BedrockEconomyAPI::$plugin = $plugin;
    }

    public function subtractFromPlayerBalance(string $username, int $subtraction, ?ClosureContext $context = null): ?SQLQuery
    {
        $transaction = new Transaction(Transaction::TRANSACTION_TYPE_DECREMENT, $subtraction, time());

        $event = new TransactionSubmitEvent($username, $transaction);
        $event->call();

        if ($event->isCancelled()) {
            return null;
        }

        return $this->getPlugin()->getAccountManager()->updateBalance($username, $transaction, $context ?? ClosureContext::create());
    }

    public function deletePlayerAccount(string $username, ?ClosureContext $context = null): void
    {
        $this->getPlugin()->getAccountManager()->deleteAccount($username, $context ?? ClosureContext::create());
    }

    public function addToPlayerBalance(string $username, int $addition, ?ClosureContext $context = null): ?SQLQuery
    {
        $transaction = new Transaction(Transaction::TRANSACTION_TYPE_INCREMENT, $addition, time());

        $event = new TransactionSubmitEvent($username, $transaction);
        $event->call();

        if ($event->isCancelled()) {
            return null;
        }

        return $this->getPlugin()->getAccountManager()->updateBalance($username, $transaction, $context ?? ClosureContext::create());
    }

    public function getPlayerBalance(string $username, ?ClosureContext $context = null): SQLQuery
    {
        return $this->getPlugin()->getAccountManager()->getBalance($username, $context ?? ClosureContext::create());
    }
}
