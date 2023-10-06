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
use cooldogedev\BedrockEconomy\database\util\Cache;
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

        if (Cache::exists($player->getXuid())) {
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
                    $balance = yield from BedrockEconomyAPI::ASYNC()->get($playerInfo->getXuid(), $playerInfo->getUsername());
                } catch (SQLException) {
                    $balance = null;
                }

                if ($balance === null) {
                    try {
                        yield from BedrockEconomyAPI::ASYNC()->insert($playerInfo->getXuid(), $playerInfo->getUsername(), $this->plugin->getCurrency()->defaultAmount, $this->plugin->getCurrency()->defaultDecimals);

                        $balance = [
                            "amount" => $this->plugin->getCurrency()->defaultAmount,
                            "decimals" => $this->plugin->getCurrency()->defaultDecimals
                        ];

                        $this->plugin->getLogger()->debug("Created account for " . $playerInfo->getUsername() . " with balance of " . $balance["amount"] . "." . $balance["decimals"]);
                    } catch (SQLException $exception) {
                        $networkSession->disconnect("An error occurred while creating your account. Please try again later.");
                        $this->plugin->getLogger()->error("Failed to create account for " . $playerInfo->getUsername() . ": " . $exception->getMessage());
                    }
                }

                if ($balance === null) {
                    return;
                }

                $balance = $balance["amount"] . ($this->plugin->getCurrency()->decimals ? "." . $balance["decimals"] : "");

                Cache::set($playerInfo->getXuid(), $balance);
                $this->plugin->getLogger()->debug("Loaded account for " . $playerInfo->getUsername() . " with balance of " . $balance);
            }
        );
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        Cache::delete($player->getXuid());
    }
}
