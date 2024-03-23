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

namespace cooldogedev\BedrockEconomy\database\migration\v2_1_2;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
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
        return "<=2.1.2";
    }

    public static function getMin(): string
    {
        return "0.0.1";
    }

    public static function getMax(): string
    {
        return "2.1.2";
    }

    public function run(string $mode): ?Promise
    {
        $query = $mode === "mysql" ? new MySQLHandler() : new SQLiteHandler();
        $query->execute(
            onSuccess: fn(array $records) => Await::f2c(
                function () use ($records): Generator {
                    foreach ($records as $record) {
                        try {
                            yield from BedrockEconomyAPI::ASYNC()->insert($record["username"], $record["username"], (int)$record["balance"], 0);
                            $this->logger->debug("Migrated data for player " . $record["username"]);
                        } catch (RecordAlreadyExistsException) {
                            $this->logger->warning("Attempted to migrate data for player " . $record["username"] . " but they already exist");
                        } catch (SQLException) {
                            $this->logger->warning("Failed to migrate data for player " . $record["username"]);
                        }
                    }
                }
            ),
            onFail: fn (SQLException $exception) => $this->logger->logException($exception)
        );
        return null;
    }
}