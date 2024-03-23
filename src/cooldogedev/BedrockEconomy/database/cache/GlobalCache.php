<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2024 cooldogedev
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

namespace cooldogedev\BedrockEconomy\database\cache;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\event\CacheInvalidateEvent;
use Generator;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\SOFe\AwaitGenerator\Await;

final class GlobalCache
{
    private static Cache $online;
    private static Cache $top;

    public static function init(): void
    {
        GlobalCache::$online = new Cache();
        GlobalCache::$top = new Cache();
    }

    public static function invalidate(): void
    {
        Await::f2c(
            function (): Generator {
                $plugin = BedrockEconomy::getInstance();

                $onlineCache = [];

                if (count($plugin->getServer()->getOnlinePlayers()) > 0) {
                    try {
                        $onlineCache = yield from BedrockEconomyAPI::ASYNC()->bulk(
                            list: array_map(static fn($player) => $player->getXuid(), $plugin->getServer()->getOnlinePlayers())
                        );
                    } catch (RecordNotFoundException) {
                        $plugin->getLogger()->debug("No online records found");
                    }
                }

                try {
                    $topCache = yield from BedrockEconomyAPI::ASYNC()->top(
                        limit: $plugin->getConfig()->getNested("cache.rich-rows"),
                        offset: 0,
                        ascending: false,
                    );
                } catch (RecordNotFoundException) {
                    $topCache = [];
                    $plugin->getLogger()->debug("No top records found");
                }

                $event = new CacheInvalidateEvent($onlineCache, $topCache);
                $event->call();

                if ($event->isCancelled()) {
                    return;
                }

                foreach ($onlineCache as $entry) {
                    if (!GlobalCache::ONLINE()->exists($entry["username"])) {
                        continue;
                    }

                    GlobalCache::ONLINE()->set($entry["username"], new CacheEntry(
                        amount: $entry["amount"],
                        decimals: $entry["decimals"],
                        position: $entry["position"],
                    ));
                }

                $position = 1;

                foreach ($topCache as $entry) {
                    GlobalCache::TOP()->set($entry["username"], new CacheEntry(
                        amount: $entry["amount"],
                        decimals: $entry["decimals"],
                        position: $position,
                    ));

                    $position++;
                }

                $plugin->getLogger()->debug("Cache invalidated");
            }
        );
    }

    public static function ONLINE(): Cache
    {
        return GlobalCache::$online;
    }

    public static function TOP(): Cache
    {
        return GlobalCache::$top;
    }
}