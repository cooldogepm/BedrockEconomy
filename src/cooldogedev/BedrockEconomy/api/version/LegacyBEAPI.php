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

use cooldogedev\BedrockEconomy\account\AccountManager;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\event\transaction\TransactionSubmitEvent;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\BedrockEconomy\transaction\types\TransferTransaction;
use cooldogedev\BedrockEconomy\transaction\types\UpdateTransaction;
use pocketmine\utils\SingletonTrait;

/**
 * @deprecated
 */
final class LegacyBEAPI implements IBedrockEconomyAPI
{
    use SingletonTrait {
        setInstance as protected;
        getInstance as protected _getInstance;
    }

    public static function getInstance(): LegacyBEAPI
    {
        return LegacyBEAPI::_getInstance();
    }

    public function setPlayerBalance(string $username, int $balance, ?ClosureContext $context = null, ?string $issuer = null): void
    {
        $transaction = new UpdateTransaction(
            Transaction::TRANSACTION_TYPE_SET,
            $issuer,
            $username,
            $balance,
        );

        $event = new TransactionSubmitEvent($transaction);
        $event->call();

        if ($event->isCancelled()) {
            $context->wrap(false, AccountManager::ERROR_EVENT_CANCELLED)();
            return;
        }

        $this->getPlugin()->getAccountManager()->updateBalance($transaction, $context ?? ClosureContext::create());
    }

    protected function getPlugin(): BedrockEconomy
    {
        return BedrockEconomy::getInstance();
    }

    public function subtractFromPlayerBalance(string $username, int $subtraction, ?ClosureContext $context = null, ?string $issuer = null): void
    {
        $transaction = new UpdateTransaction(
            Transaction::TRANSACTION_TYPE_DECREMENT,
            $issuer,
            $username,
            $subtraction,
        );

        $this->getPlugin()->getAccountManager()->updateBalance($transaction, $context ?? ClosureContext::create());
    }

    public function transferFromPlayerBalance(string $sender, string $receiver, int $amount, ?ClosureContext $context = null): void
    {
        $transaction = new TransferTransaction($sender, $receiver, $amount);

        $this->getPlugin()->getAccountManager()->transferFromBalance($transaction, $context ?? ClosureContext::create());
    }

    public function deletePlayerAccount(string $username, ?ClosureContext $context = null): void
    {
        $this->getPlugin()->getAccountManager()->deleteAccount($username, $context ?? ClosureContext::create());
    }

    public function addToPlayerBalance(string $username, int $addition, ?ClosureContext $context = null, ?string $issuer = null): void
    {
        $transaction = new UpdateTransaction(
            Transaction::TRANSACTION_TYPE_INCREMENT,
            $issuer,
            $username,
            $addition,
        );

        $this->getPlugin()->getAccountManager()->updateBalance($transaction, $context ?? ClosureContext::create());
    }

    public function isAccountExists(string $username, ?ClosureContext $context = null): void
    {
        $this->getPlugin()->getAccountManager()->hasAccount($username, $context ?? ClosureContext::create());
    }

    public function createAccount(string $username, ?int $balance = null, ?ClosureContext $context = null): void
    {
        $this->getPlugin()->getAccountManager()->createAccount($username, $context ?? ClosureContext::create(), $balance);
    }

    public function getHighestBalances(int $limit, ?ClosureContext $context = null, ?int $offset = null): void
    {
        $this->getPlugin()->getAccountManager()->getHighestBalances($limit, $context ?? ClosureContext::create(), $offset);
    }

    public function getPlayerBalance(string $username, ?ClosureContext $context = null): void
    {
        $this->getPlugin()->getAccountManager()->getBalance($username, $context ?? ClosureContext::create());
    }

    public function getVersion(): string
    {
        return "1.0.0";
    }

    public function getChannel(): string
    {
        return "legacy";
    }
}
