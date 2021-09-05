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

namespace cooldogedev\BedrockEconomy\session;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\constant\SearchConstants;
use cooldogedev\BedrockEconomy\constant\SessionConstants;
use cooldogedev\BedrockEconomy\constant\TableConstants;
use cooldogedev\BedrockEconomy\event\session\SessionCreationEvent;
use cooldogedev\BedrockEconomy\event\session\SessionDeletionEvent;
use cooldogedev\BedrockEconomY\interfaces\BedrockEconomyOwned;

final class SessionManager extends BedrockEconomyOwned
{
    /**
     * @var Session[]
     */
    protected array $sessions;

    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);
        $this->sessions = [];

        $promise = $this->getPlugin()->getDatabaseManager()->getDatabaseConnector()->submitQuery(
            $this->getPlugin()
                ->getDatabaseManager()
                ->getQueryManager()
                ->getBulkPlayersRetrievalQuery(),
            TableConstants::DATA_TABLE_PLAYERS
        );

        $promise->onCompletion(
            function (array $players): void {
                foreach ($players as $player) {
                    $this->createSession($player["username"], $player["xuid"], $player["balance"]);
                    $this->getPlugin()->getLogger()->debug("Cached " . $player["username"] . "'s session.");
                }
            }
        );
    }

    public function createSession(string $xuid, string $username, ?int $balance = null): bool
    {
        if ($this->hasSession($xuid)) {
            return false;
        }
        $session = new Session($this->getPlugin(), $username, $xuid, $balance);

        $event = new SessionCreationEvent($session);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        $this->sessions[$xuid] = $session;
        return true;
    }

    public function hasSession(string $searchValue, int $searchMode = SearchConstants::SEARCH_MODE_XUID): bool
    {
        return match ($searchMode) {
            SearchConstants::SEARCH_MODE_XUID => isset($this->sessions[$searchValue]),
            SearchConstants::SEARCH_MODE_USERNAME => count(array_filter($this->sessions, fn(Session $session): bool => strtolower($session->getUsername()) === strtolower($searchValue))) > 0,
            default => false
        };
    }

    public function deleteSession(string $xuid): bool
    {
        if (!$this->hasSession($xuid)) {
            return false;
        }

        $session = $this->getSession($xuid);

        $event = new SessionDeletionEvent($session);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        $promise = $this->getPlugin()->getDatabaseManager()->getDatabaseConnector()->submitQuery(
            $this->getPlugin()
                ->getDatabaseManager()
                ->getQueryManager()
                ->getPlayerDeletionQuery($xuid),
            TableConstants::DATA_TABLE_PLAYERS
        );

        $promise->onCompletion(
            function () use ($xuid) {
                unset($this->sessions[$xuid]);
            }
        );

        return true;
    }

    public function getSession(string $searchValue, int $searchMode = SearchConstants::SEARCH_MODE_XUID): ?Session
    {
        if ($searchMode === SearchConstants::SEARCH_MODE_USERNAME) {
            if (!$this->hasSession($searchValue, SearchConstants::SEARCH_MODE_USERNAME)) {
                return null;
            }
            $sessions = array_filter($this->getSessions(), fn(Session $session): bool => strtolower($session->getUsername()) === strtolower($searchValue));
            return $sessions[array_key_first($sessions)];
        }
        return $this->sessions[$searchValue] ?? null;
    }

    /**
     * @param int|null $type
     * @return Session[]
     */
    public function getSessions(?int $type = null): array
    {
        return match ($type) {
            SessionConstants::SESSION_TYPE_DEFAULT => array_filter($this->sessions, fn(Session $session): bool => $session->getCache()->isAwaitingSave()),
            SessionConstants::SESSION_TYPE_AWAITING_SAVE => array_filter($this->sessions, fn(Session $session): bool => !$session->getCache()->isAwaitingSave()),
            default => $this->sessions
        };
    }
}
