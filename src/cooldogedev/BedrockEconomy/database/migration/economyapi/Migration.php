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

namespace cooldogedev\BedrockEconomy\database\migration\economyapi;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\constant\MigrationVersion;
use cooldogedev\BedrockEconomy\database\exception\RecordAlreadyExistsException;
use cooldogedev\BedrockEconomy\database\migration\BaseMigration;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\exception\SQLException;
use Generator;
use pocketmine\promise\Promise;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\SOFe\AwaitGenerator\Await;

final class Migration extends BaseMigration
{
    public static function getName(): string
    {
        return "EconomyAPI";
    }

    public static function getMin(): string
    {
        return MigrationVersion::VERSION_ANY;
    }

    public static function getMax(): string
    {
        return MigrationVersion::VERSION_ANY;
    }

    public function run(string $mode): ?Promise
    {
        $economyAPI = $this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if ($economyAPI !== null) {
            Await::f2c(
                function () use ($economyAPI): Generator {
                    foreach ($economyAPI->getAllMoney() as $username => $money) {
                        $money = explode(".", (string)$money);
                        $amount = (int)($money[0] ?? 0);
                        $decimals = (int)($money[1] ?? 0);
                        try {
                            yield from BedrockEconomyAPI::ASYNC()->insert($username, $username, $amount, $decimals);
                            $this->logger->debug("Migrated data for player " . $username);
                        } catch (RecordAlreadyExistsException) {
                            $this->logger->warning("Attempted to migrate data for player " . $username . " but they already exist");
                        } catch (SQLException) {
                            $this->logger->warning("Failed to migrate data for player " . $username);
                        }
                    }
                }
            );
        }
        return null;
    }
}