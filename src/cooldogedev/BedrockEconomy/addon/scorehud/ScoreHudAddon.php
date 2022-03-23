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

namespace cooldogedev\BedrockEconomy\addon\scorehud;

use cooldogedev\BedrockEconomy\addon\Addon;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\Server;

final class ScoreHudAddon extends Addon
{
    public const SCOREHUD_TAG_BALANCE = "bedrockeconomy.balance";
    public const SCOREHUD_TAG_BALANCE_CAP = "bedrockeconomy.balance_cap";
    public const SCOREHUD_TAG_CURRENCY_SYMBOL = "bedrockeconomy.currency_symbol";
    public const SCOREHUD_TAG_CURRENCY_NAME = "bedrockeconomy.currency_name";

    /**
     * @var int|null[]
     */
    protected array $cache;

    public function isLoadable(): bool
    {
        return Server::getInstance()->getPluginManager()->getPlugin("ScoreHud") !== null;
    }

    public function getPlayerCache(string $player): ?int
    {
        return $this->cache[strtolower($player)] ?? null;
    }

    public function initializePlayerCache(string $player): void
    {
        BedrockEconomyAPI::beta()->get($player)
            ->onCompletion(
                function (?int $balance) use ($player): void {
                    $this->cache[strtolower($player)] = $balance;
                },
                function () use ($player): void {
                    $this->getPlugin()->getLogger()->debug("ScoreHudAddon: Failed to initialize player cache for player '{$player}'");
                }
            );
    }

    public function updatePlayerCache(string $playerName): void
    {
        $player = Server::getInstance()->getPlayerByPrefix($playerName);

        if (!$player?->isConnected()) {
            return;
        }

        BedrockEconomyAPI::beta()->get($player->getName())
            ->onCompletion(
                function (?int $balance) use ($player): void {
                    if (!$player?->isConnected()) {
                        return;
                    }

                    $this->cache[strtolower($player->getName())] = $balance;

                    $event = new PlayerTagUpdateEvent($player, new ScoreTag(ScoreHudAddon::SCOREHUD_TAG_BALANCE, $balance !== null ? number_format($balance, 0, ".", $this->getPlugin()->getCurrencyManager()->getNumberSeparator()) : "N/A"));
                    $event->call();
                },
                function () use ($playerName): void {
                    $this->getPlugin()->getLogger()->debug("ScoreHudAddon: Failed to update player cache for player '{$playerName}'");
                }
            );
    }

    public function removePlayerCache(string $player): bool
    {
        if (!$this->isPlayerCached($player)) {
            return false;
        }

        unset($this->cache[strtolower($player)]);
        return true;
    }

    public function isPlayerCached(string $player): bool
    {
        return isset($this->cache[strtolower($player)]);
    }

    public function getName(): string
    {
        return "Scorehud";
    }

    public function getVersion(): string
    {
        return "1.0.0";
    }

    public function getMinimumSupportedBedrockEconomyVersion(): string
    {
        return "2.0.5";
    }

    protected function onEnable(): void
    {
        $this->cache = [];
        $this->getPlugin()->getServer()->getPluginManager()->registerEvents(new ScoreHudListener($this), $this->getPlugin());
    }
}
