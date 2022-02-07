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

namespace cooldogedev\BedrockEconomy\account;

use cooldogedev\BedrockEconomY\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\event\account\AccountCreationEvent;
use cooldogedev\BedrockEconomy\event\account\AccountDeletionEvent;
use cooldogedev\BedrockEconomy\query\QueryManager;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\libSQL\context\ClosureContext;
use cooldogedev\libSQL\query\SQLQuery;

final class AccountManager extends BedrockEconomyOwned
{
    /**
     * @var Transaction[]
     */
    protected array $transactions;

    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);
        $this->transactions = [];
    }

    /**
     * @param string $username
     * @param int|null $balance
     * @return SQLQuery|null
     *
     * @internal This method is not meant to be used outside of the BedrockEconomy scope.
     */
    public function createAccount(string $username, ?int $balance = null): ?SQLQuery
    {
        if ($balance === null) {
            $balance = $this->getPlugin()->getCurrencyManager()->getDefaultBalance();
        }

        $event = new AccountCreationEvent($username, $balance);
        $event->call();

        if ($event->isCancelled()) {
            return null;
        }

        return $this->getPlugin()->getConnector()->submit(
            QueryManager::getPlayerCreationQuery($username, $balance),
            table: QueryManager::DATA_TABLE_PLAYERS,
        );
    }

    public function hasAccount(string $username, ClosureContext $context): ?SQLQuery
    {
        return $this->getBalance(
            $username,
            context: $context->first(fn(?int $balance) => $balance !== null)
        );
    }

    public function getBalance(string $username, ClosureContext $context): ?SQLQuery
    {
        return $this->getPlugin()->getConnector()->submit(
            QueryManager::getPlayerQuery($username),
            QueryManager::DATA_TABLE_PLAYERS,
            context: $context->first(fn(?array $data): ?int => $data["balance"] ?? null)
        );
    }

    public function updateBalance(string $username, Transaction $transaction, ClosureContext $context): ?SQLQuery
    {
        $this->addTransaction($transaction);

        return $this->getPlugin()->getConnector()->submit(
            QueryManager::getPlayerUpdateQuery(strtolower($username), $transaction),
            QueryManager::DATA_TABLE_PLAYERS,
            context: $context->first(
                function () use ($transaction): void {
                    $this->removeTransaction($transaction);
                }
            )
        );
    }

    protected function addTransaction(Transaction $transaction): bool
    {
        $objectHash = spl_object_hash($transaction);
        if ($this->hasTransaction($objectHash)) {
            return false;
        }
        $this->transactions[$objectHash] = $transaction;
        return true;
    }

    protected function hasTransaction(string $objectHash): bool
    {
        return isset($this->transactions[$objectHash]);
    }

    protected function removeTransaction(Transaction $transaction): bool
    {
        $objectHash = spl_object_hash($transaction);
        if (!$this->hasTransaction($objectHash)) {
            return false;
        }
        unset($this->transactions[$objectHash]);
        return true;
    }

    public function getHighestBalances(int $limit, ClosureContext $context, ?int $offset = null): ?SQLQuery
    {
        return $this->getPlugin()->getConnector()->submit(
            QueryManager::getBulkPlayersQuery($limit, $offset),
            table: QueryManager::DATA_TABLE_PLAYERS,
            context: $context
        );
    }

    public function deleteAccount(string $username, ClosureContext $context): ?SQLQuery
    {
        return $this->getBalance(
            $username,
            ClosureContext::create(
                function (?int $balance) use ($username, $context): void {
                    if ($balance === null) {
                        $context->invoke(false);
                        return;
                    }

                    $event = new AccountDeletionEvent($username, $balance);
                    $event->call();

                    if ($event->isCancelled()) {
                        $context->invoke(false);
                        return;
                    }

                    $this->getPlugin()->getConnector()->submit(
                        QueryManager::getPlayerDeletionQuery($username),
                        QueryManager::DATA_TABLE_PLAYERS,
                        context: $context
                    );
                }
            )
        );
    }

    /**
     * @return Transaction[]
     */
    protected function getTransactions(): array
    {
        return $this->transactions;
    }
}
