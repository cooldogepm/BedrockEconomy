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

namespace cooldogedev\BedrockEconomy\account;

use cooldogedev\BedrockEconomY\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\event\account\AccountCreationEvent;
use cooldogedev\BedrockEconomy\event\account\AccountDeletionEvent;
use cooldogedev\BedrockEconomy\query\QueryManager;
use cooldogedev\BedrockEconomy\transaction\types\TransferTransaction;
use cooldogedev\BedrockEconomy\transaction\types\UpdateTransaction;
use cooldogedev\libSQL\exception\SQLException;

final class AccountManager extends BedrockEconomyOwned
{
    public const ERROR_EVENT_CANCELLED = "The event was cancelled";

    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);
    }

    /**
     * @internal This method is not meant to be used outside the BedrockEconomy scope.
     */
    public function createAccount(string $username, ClosureContext $context, ?int $balance = null): void
    {
        if ($balance === null) {
            $balance = $this->getPlugin()->getCurrencyManager()->getDefaultBalance();
        }

        $event = new AccountCreationEvent($username, $balance);
        $event->call();

        if ($event->isCancelled()) {
            $context->wrap(false, AccountManager::ERROR_EVENT_CANCELLED)();
            return;
        }

        $this->getPlugin()->getConnector()->submit(
            query: QueryManager::getPlayerCreationQuery($username, $event->getBalance()),

            onSuccess: static fn (mixed $result) => $context->wrap($result, null)(),
            onFail: static fn (SQLException $exception) => $context->wrap(false, $exception->_getMessage())()
        );
    }

    public function hasAccount(string $username, ClosureContext $context): void
    {
        $this->getBalance(
            $username,
            context: $context->first(fn(?int $balance) => $balance !== null)
        );
    }

    public function getBalance(string $username, ClosureContext $context): void
    {
        $this->getPlugin()->getConnector()->submit(
            query: QueryManager::getPlayerQuery($username),

            onSuccess: static fn (mixed $result) => $context->wrap($result, null)(),
            onFail: static fn (SQLException $exception) => $context->wrap(null, $exception->_getMessage())()
        );
    }

    public function updateBalance(UpdateTransaction $transaction, ClosureContext $context): void
    {
        if (!$this->getPlugin()->getTransactionManager()->submitTransaction($transaction)) {
            $context->wrap(false, AccountManager::ERROR_EVENT_CANCELLED)();
            return;
        }

        $this->getPlugin()->getConnector()->submit(
            query: QueryManager::getPlayerUpdateQuery($transaction),

            onSuccess: static fn (mixed $result) => $context->wrap($result, null)(),
            onFail: static fn (SQLException $exception) => $context->wrap(false, $exception->_getMessage())()
        );
    }

    public function transferFromBalance(TransferTransaction $transaction, ClosureContext $context): void
    {
        if (!$this->getPlugin()->getTransactionManager()->submitTransaction($transaction)) {
            $context->wrap(false, AccountManager::ERROR_EVENT_CANCELLED)();
            return;
        }

        $context->first(
            function (bool $successful) use ($transaction): void {
                $this->getPlugin()->getTransactionManager()->processTransaction($transaction, $successful);
            }
        );

        $this->getPlugin()->getConnector()->submit(
            query: QueryManager::getPlayerTransferQuery($transaction),

            onSuccess: static fn (mixed $result) => $context->wrap($result, null)(),
            onFail: static fn (SQLException $exception) => $context->wrap(false, $exception->_getMessage())()
        );
    }

    public function getHighestBalances(int $limit, ClosureContext $context, ?int $offset = null): void
    {
        $this->getPlugin()->getConnector()->submit(
            query: QueryManager::getBulkPlayersQuery($limit, $offset),

            onSuccess: static fn (mixed $result) => $context->wrap($result, null)(),
            onFail: static fn (SQLException $exception) => $context->wrap([], $exception->_getMessage())()
        );
    }

    public function deleteAccount(string $username, ClosureContext $context): void
    {
        $event = new AccountDeletionEvent($username);
        $event->call();

        if ($event->isCancelled()) {
            $context->wrap(false, AccountManager::ERROR_EVENT_CANCELLED)();
            return;
        }

        $this->getPlugin()->getConnector()->submit(
            query: QueryManager::getPlayerDeletionQuery($username),

            onSuccess: static fn (mixed $result) => $context->wrap($result, null)(),
            onFail: static fn (SQLException $exception) => $context->wrap(false, $exception->_getMessage())()
        );
    }
}
