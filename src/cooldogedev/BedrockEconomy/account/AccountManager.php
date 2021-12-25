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
use cooldogedev\BedrockEconomy\constant\SearchConstants;
use cooldogedev\BedrockEconomy\constant\TableConstants;
use cooldogedev\BedrockEconomy\event\account\AccountCacheEvent;
use cooldogedev\BedrockEconomy\event\account\AccountDeletionEvent;
use cooldogedev\libPromise\thread\ThreadedPromise;

final class AccountManager extends BedrockEconomyOwned
{
    protected int $lastSnapshotDate;
    /**
     * @var Account[]
     */
    protected array $accounts;

    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);
        $this->accounts = [];

        $this->getPlugin()->getDatabaseManager()->getConnector()->submitQuery(
            $this->getPlugin()
                ->getDatabaseManager()
                ->getQueryManager()
                ->getBulkPlayersRetrievalQuery(null, null),
            TableConstants::DATA_TABLE_PLAYERS,
            onSuccess: function (?array $players): void {
                if (!$players) {
                    return;
                }
                foreach ($players as $player) {
                    $this->addAccount($player["xuid"], $player["username"], $player["balance"]);
                    $this->getPlugin()->getLogger()->debug("Cached " . $player["username"] . "'s session.");
                }
            }
        );
    }

    public function addAccount(string $xuid, string $username, ?int $balance = null, bool $submitForCreation = false): bool
    {
        if ($this->hasAccount($xuid)) {
            return false;
        }
        $session = new Account($this->getPlugin(), $username, $xuid, $balance, $submitForCreation);

        $event = new AccountCacheEvent($session);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        $this->accounts[$xuid] = $session;

        return true;
    }

    public function hasAccount(string $searchValue, int $searchMode = SearchConstants::SEARCH_MODE_XUID): bool
    {
        return match ($searchMode) {
            SearchConstants::SEARCH_MODE_XUID => isset($this->accounts[$searchValue]),
            SearchConstants::SEARCH_MODE_USERNAME => count(array_filter($this->getAccounts(), fn(Account $session): bool => strtolower($session->getUsername()) === strtolower($searchValue))) > 0,
            default => false
        };
    }

    /**
     * @return Account[]
     */
    public function getAccounts(): array
    {
        return $this->accounts;
    }

    public function getHighestBalances(int $limit, ?int $offset = null, ?Closure $onSuccess = null, ?Closure $onError = null): ?ThreadedPromise
    {
        return $this->getPlugin()->getDatabaseManager()->getConnector()->submitQuery(
            $this->getPlugin()->getDatabaseManager()->getQueryManager()->getBulkPlayersRetrievalQuery($limit, $offset),
            table: TableConstants::DATA_TABLE_PLAYERS,
            onSuccess: $onSuccess,
            onError: $onError,
        );
    }

    public function removeFromCache(string $xuid): bool
    {
        if (!$this->hasAccount($xuid)) {
            return false;
        }
        unset($this->accounts[$xuid]);
        return true;
    }

    public function deleteAccount(string $xuid, int $mode = SearchConstants::SEARCH_MODE_XUID): bool
    {
        if (!$this->hasAccount($xuid, $mode)) {
            return false;
        }

        $session = $this->getAccount($xuid, $mode);

        $event = new AccountDeletionEvent($session);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        $this->getPlugin()->getDatabaseManager()->getConnector()->submitQuery(
            $this->getPlugin()
                ->getDatabaseManager()
                ->getQueryManager()
                ->getPlayerDeletionQuery($xuid),
            TableConstants::DATA_TABLE_PLAYERS,
            onSuccess: function () use ($xuid) {
                unset($this->accounts[$xuid]);
            }
        );

        return true;
    }

    public function getAccount(string $searchValue, int $searchMode = SearchConstants::SEARCH_MODE_XUID): ?Account
    {
        if ($searchMode === SearchConstants::SEARCH_MODE_USERNAME) {
            if (!$this->hasAccount($searchValue, SearchConstants::SEARCH_MODE_USERNAME)) {
                return null;
            }
            $sessions = array_filter($this->getAccounts(), fn(Account $session): bool => strtolower($session->getUsername()) === strtolower($searchValue));
            return $sessions[array_key_first($sessions)];
        }
        return $this->accounts[$searchValue] ?? null;
    }
}
