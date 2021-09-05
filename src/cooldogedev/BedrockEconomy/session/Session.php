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
use cooldogedev\BedrockEconomy\constant\TableConstants;
use cooldogedev\BedrockEconomy\event\balance\BalanceChangeEvent;
use cooldogedev\BedrockEconomY\interfaces\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\session\cache\SessionCache;
use pocketmine\player\Player;

final class Session extends BedrockEconomyOwned
{
    protected SessionCache $cache;

    public function __construct(
        BedrockEconomy   $plugin,
        protected string $username,
        protected string $xuid,
        ?int             $balance = null
    )
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->cache = new SessionCache(
            $this,
            time(),
            $balance ?? $this->getPlugin()->getCurrencyManager()->getDefaultBalance(),
            true,
            $balance === null,
            false,
        );
    }

    public function getPlayer(): ?Player
    {
        return $this->getPlugin()->getServer()->getPlayerByPrefix($this->getUsername());
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function onSave(): bool
    {
        if (!$this->getCache()->isNew() && !$this->getCache()->isAwaitingSave()) {
            return false;
        }

        $event = new BalanceChangeEvent($this, $this->getCache()->getBalance());
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        $this->getCache()->setLastUpdate(time());
        $this->getCache()->setAwaitingSave(false);

        if ($this->getCache()->isNew()) {
            $this->getPlugin()->getDatabaseManager()->getDatabaseConnector()->submitQuery(
                $this->getPlugin()
                    ->getDatabaseManager()
                    ->getQueryManager()
                    ->getPlayerCreationQuery($this->getXuid(), $this->getUsername(), $this->getCache()->getBalance()),
                TableConstants::DATA_TABLE_PLAYERS
            );
        } elseif ($this->getCache()->isAwaitingSave()) {
            $this->getPlugin()->getDatabaseManager()->getDatabaseConnector()->submitQuery(
                $this->getPlugin()
                    ->getDatabaseManager()
                    ->getQueryManager()
                    ->getPlayerSaveQuery($this->getXuid(), $this->getCache()->getBalance()),
                TableConstants::DATA_TABLE_PLAYERS
            );
        }

        return true;
    }

    public function getCache(): SessionCache
    {
        return $this->cache;
    }

    public function getXuid(): string
    {
        return $this->xuid;
    }
}
