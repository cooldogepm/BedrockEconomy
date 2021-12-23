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

use Closure;
use cooldogedev\BedrockEconomY\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\constant\TableConstants;
use cooldogedev\BedrockEconomy\constant\TransactionConstants;
use cooldogedev\BedrockEconomy\event\account\AccountCreationEvent;
use cooldogedev\BedrockEconomy\event\TransactionSubmitEvent;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\libPromise\thread\ThreadedPromise;
use pocketmine\player\Player;

final class Account extends BedrockEconomyOwned
{
    protected int $balance;
    protected bool $xuidIsInvalid;
    /**
     * @var Transaction[]
     */
    protected array $transactions;

    public function __construct(
        BedrockEconomy   $plugin,
        protected string $username,
        protected string $xuid,
        ?int             $balance = null,
        bool $submitForCreation = false
    )
    {
        parent::__construct($plugin);

        $this->plugin = $plugin;
        $this->balance = $balance ?? $this->getPlugin()->getCurrencyManager()->getDefaultBalance();
        $this->xuidIsInvalid = $xuid === $this->username;
        $this->transactions = [];

        // New account
        if ($balance === null || $submitForCreation) {

            $event = new AccountCreationEvent($this);
            $event->call();

            if ($event->isCancelled()) {
                return;
            }

            $this->getPlugin()->getDatabaseManager()->getConnector()->submitQuery(
                $this->getPlugin()
                    ->getDatabaseManager()
                    ->getQueryManager()
                    ->getPlayerCreationQuery($this->getXuid(), $this->getUsername(), $this->getBalance()),
                TableConstants::DATA_TABLE_PLAYERS
            );
        }

    }

    public function getXuid(): string
    {
        return $this->xuid;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function setBalance(int $balance, bool $ignoreCheck = false): bool
    {
        if (!$ignoreCheck && $balance === $this->getBalance()) {
            return false;
        }

        $transaction = new Transaction(TransactionConstants::TRANSACTION_TYPE_SET, $balance, time());

        $event = new TransactionSubmitEvent($this, $transaction);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        $this->addTransaction($transaction);
        $this->updateCacheBalance($balance);
        $this->processTransaction($transaction);

        return true;
    }

    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[spl_object_hash($transaction)] = $transaction;
    }

    protected function updateCacheBalance(int $balance): bool
    {
        if ($this->getBalance() === $balance) {
            return false;
        }
        $this->balance = $balance;
        return true;
    }

    public function processTransaction(Transaction $transaction): void
    {
        $this->getPlugin()->getDatabaseManager()->getConnector()->submitQuery(
            $this->getPlugin()
                ->getDatabaseManager()
                ->getQueryManager()
                ->getPlayerSaveQuery($this->getXuid(), $transaction),
            TableConstants::DATA_TABLE_PLAYERS,
            onSuccess: fn(): bool => $this->removeTransaction(spl_object_hash($transaction))
        );
    }

    public function removeTransaction(string $key): bool
    {
        if (!$this->hasTransaction($key)) {
            return false;
        }
        unset($this->transactions[$key]);
        return true;
    }

    public function hasTransaction(string $key): bool
    {
        return isset($this->transactions[$key]);
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function setTransactions(array $transactions): void
    {
        $this->transactions = $transactions;
    }

    public function incrementBalance(int $incrementation): bool
    {
        $transaction = new Transaction(TransactionConstants::TRANSACTION_TYPE_INCREMENT, $incrementation, time());

        $event = new TransactionSubmitEvent($this, $transaction);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        $this->addTransaction($transaction);
        $this->updateCacheBalance($this->getBalance() + $incrementation);
        $this->processTransaction($transaction);

        return true;
    }

    public function decrementBalance(int $decrementation): bool
    {
        $transaction = new Transaction(TransactionConstants::TRANSACTION_TYPE_DECREMENT, $decrementation, time());

        $event = new TransactionSubmitEvent($this, $transaction);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        $this->addTransaction($transaction);
        $this->updateCacheBalance($this->getBalance() - $decrementation);
        $this->processTransaction($transaction);

        return true;
    }

    public function getPlayer(): ?Player
    {
        return $this->getPlugin()->getServer()->getPlayerByPrefix($this->getUsername());
    }

    public function setXuid(string $xuid): void
    {
        $this->xuid = $xuid;
    }

    public function isXuidIsInvalid(): bool
    {
        return $this->xuidIsInvalid;
    }

    public function setXuidIsInvalid(bool $xuidIsInvalid): void
    {
        $this->xuidIsInvalid = $xuidIsInvalid;
    }

    public function getTransaction(string $key): ?Transaction
    {
        return $this->transactions[$key] ?? null;
    }

    public function attemptXuidFix(string $xuid): void
    {
        $this->getPlugin()->getDatabaseManager()->getConnector()->submitQuery(
            $this->getPlugin()
                ->getDatabaseManager()
                ->getQueryManager()
                ->getPlayerFixQuery($xuid, strtolower($this->getUsername())),
            TableConstants::DATA_TABLE_PLAYERS,
            onSuccess: function () use ($xuid): void {
                $this->getPlugin()->getAccountManager()->addAccount($xuid, $this->getUsername(), $this->getBalance());
                $this->getPlugin()->getAccountManager()->removeFromCache($this->getXuid());
            }
        );
    }

//    /**
//     * @return ThreadedPromise
//     */
//    public function batchTransactions(): IPromise
//    {
//        return $this->getBalanceFromDatabase()->then(
//            function (array $data): void {
//                $balance = $data["balance"];
//
//                foreach ($this->getTransactions() as $key => $transaction) {
//                    $balance = $transaction->call($balance);
//                    $this->removeTransaction($key);
//                }
//
//                $this->setBalance($balance);
//
//            }
//        );
//    }

//    public function submitTransactions(): bool
//    {
//        if (count($this->getTransactions()) <= 0) {
//            return false;
//        }
//
//        $this->getPlugin()->getDatabaseManager()->getConnector()->appendQueryToPool(
//            $this->batchTransactions()->onCompletion(
//                fn(int $balance): bool => $this->updateCacheBalance($balance)
//            )
//        );
//
//
//        return true;
//    }

    public function getBalanceFromDatabase(?Closure $onSuccess = null): ThreadedPromise
    {
        return $this->getPlugin()->getDatabaseManager()->getConnector()->generateQuery(
            $this->getPlugin()
                ->getDatabaseManager()
                ->getQueryManager()
                ->getPlayerRetrievalQuery($this->getXuid()),
            TableConstants::DATA_TABLE_PLAYERS,
            onSuccess: $onSuccess
        );
    }
}
