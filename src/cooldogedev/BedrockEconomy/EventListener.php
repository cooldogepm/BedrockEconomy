<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2023 cooldogedev
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

namespace cooldogedev\BedrockEconomy;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\cache\CacheEntry;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use cooldogedev\BedrockEconomy\event\transaction\AddTransactionEvent;
use cooldogedev\BedrockEconomy\event\transaction\SetTransactionEvent;
use cooldogedev\BedrockEconomy\event\transaction\SubtractTransactionEvent;
use cooldogedev\BedrockEconomy\event\transaction\TransferTransactionEvent;
use cooldogedev\libSQL\exception\SQLException;
use Generator;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\player\Player;
use pocketmine\player\XboxLivePlayerInfo;
use RuntimeException;
use SOFe\AwaitGenerator\Await;

final class EventListener implements Listener
{
    public function __construct(protected BedrockEconomy $plugin) {}

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $origin = $event->getOrigin();
        $packet = $event->getPacket();

        $player = $origin->getPlayer();

        if (!$player instanceof Player) {
            return;
        }

        if (!$packet instanceof SetLocalPlayerAsInitializedPacket) {
            return;
        }

        if (GlobalCache::ONLINE()->exists($player->getName())) {
            return;
        }

        $event->cancel();
    }

    public function onPlayerCreation(PlayerCreationEvent $event): void
    {
        $networkSession = $event->getNetworkSession();
        $playerInfo = $networkSession->getPlayerInfo();

        if (!$playerInfo instanceof XboxLivePlayerInfo) {
            throw new RuntimeException("BedrockEconomy requires xbox authentication.");
        }

        Await::f2c(
            function () use ($networkSession, $playerInfo): Generator {
                try {
                    $data = yield from BedrockEconomyAPI::ASYNC()->get($playerInfo->getXuid(), $playerInfo->getUsername());
                } catch (SQLException) {
                    $data = null;
                }

                if ($data === null) {
                    try {
                        yield from BedrockEconomyAPI::ASYNC()->insert($playerInfo->getXuid(), $playerInfo->getUsername(), $this->plugin->getCurrency()->defaultAmount, $this->plugin->getCurrency()->defaultDecimals);

                        $data = [
                            "xuid" => $playerInfo->getXuid(),
                            "username" => $playerInfo->getUsername(),
                            "amount" => $this->plugin->getCurrency()->defaultAmount,
                            "decimals" => $this->plugin->getCurrency()->defaultDecimals,
                            "position" => 0,
                        ];

                        $this->plugin->getLogger()->debug("Created account for " . $playerInfo->getUsername() . " with balance of " . $data["amount"] . "." . $data["decimals"]);
                    } catch (SQLException $exception) {
                        $networkSession->disconnect("An error occurred while creating your account. Please try again later.");
                        $this->plugin->getLogger()->error("Failed to create account for " . $playerInfo->getUsername() . ": " . $exception->getMessage());
                    }
                }

                if ($data === null) {
                    return;
                }

                if ($data["xuid"] === $data["username"]) {
                    $migrated = yield from BedrockEconomyAPI::ASYNC()->migrate($playerInfo->getXuid(), $playerInfo->getUsername(), $playerInfo->getXuid(), $playerInfo->getUsername());

                    if (!$migrated) {
                        $networkSession->disconnect("An error occurred while migrating your account. Please try again later.");
                        $this->plugin->getLogger()->error("Failed to migrate account for " . $playerInfo->getUsername());
                    }
                }

                GlobalCache::ONLINE()->set($playerInfo->getUsername(), new CacheEntry(
                    amount: $data["amount"],
                    decimals: $data["decimals"],
                    position: $data["position"],
                ));
                $this->plugin->getLogger()->debug("Loaded account for " . $playerInfo->getUsername() . " with balance of " . $data["amount"] . " and decimals of " . $data["decimals"]);
            }
        );
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        GlobalCache::ONLINE()->remove($player->getName());
    }

    /**
     * @priority LOWEST
     */
    public function onAddTransaction(AddTransactionEvent $event): void
    {
        $online = GlobalCache::ONLINE()->get($event->username);
        $top = GlobalCache::TOP()->get($event->username);

        if ($top !== null) {
            GlobalCache::TOP()->set($event->username, new CacheEntry(
                amount: $top->amount + $event->amount,
                decimals: $top->decimals + $event->decimals,
                position: $top->position,
            ));

            GlobalCache::TOP()->sort();
        }

        if ($online !== null) {
            GlobalCache::ONLINE()->set($event->username, new CacheEntry(
                amount: $online->amount + $event->amount,
                decimals: $online->decimals + $event->decimals,
                position: GlobalCache::TOP()->get($event->username)?->position ?? $online->position,
            ));
        }
    }

    /**
     * @priority LOWEST
     */
    public function onSubtractTransaction(SubtractTransactionEvent $event): void
    {
        $online = GlobalCache::ONLINE()->get($event->username);
        $top = GlobalCache::TOP()->get($event->username);

        if ($top !== null) {
            GlobalCache::TOP()->set($event->username, new CacheEntry(
                amount: $top->amount - $event->amount,
                decimals: $top->decimals - $event->decimals,
                position: $top->position,
            ));

            GlobalCache::TOP()->sort();
        }

        if ($online !== null) {
            GlobalCache::ONLINE()->set($event->username, new CacheEntry(
                amount: $online->amount - $event->amount,
                decimals: $online->decimals - $event->decimals,
                position: GlobalCache::TOP()->get($event->username)?->position ?? $online->position,
            ));
        }
    }

    /**
     * @priority LOWEST
     */
    public function onSetTransaction(SetTransactionEvent $event): void
    {
        $online = GlobalCache::ONLINE()->get($event->username);
        $top = GlobalCache::TOP()->get($event->username);

        if ($top !== null) {
            GlobalCache::TOP()->set($event->username, new CacheEntry(
                amount: $event->amount,
                decimals: $event->decimals,
                position: $top->position,
            ));

            GlobalCache::TOP()->sort();
        }

        if ($online !== null) {
            GlobalCache::ONLINE()->set($event->username, new CacheEntry(
                amount: $event->amount,
                decimals: $event->decimals,
                position: GlobalCache::TOP()->get($event->username)?->position ?? $online->position,
            ));
        }
    }

    /**
     * @priority LOWEST
     */
    public function onTransferTransaction(TransferTransactionEvent $event): void
    {
        $this->onAddTransaction(new AddTransactionEvent(
            xuid: $event->target["xuid"],
            username: $event->target["username"],
            amount: $event->amount,
            decimals: $event->decimals,
        ));
        $this->onSubtractTransaction(new SubtractTransactionEvent(
            xuid: $event->source["xuid"],
            username: $event->source["username"],
            amount: $event->amount,
            decimals: $event->decimals,
        ));
    }
}