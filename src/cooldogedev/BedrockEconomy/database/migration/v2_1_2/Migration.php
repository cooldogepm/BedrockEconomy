<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\migration\v2_1_2;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\database\migration\IMigration;
use Generator;
use SOFe\AwaitGenerator\Await;

final class Migration implements IMigration
{
    public function getName(): string
    {
        return "<=2.1.2";
    }

    public function getMin(): string
    {
        return "0.0.1";
    }

    public function getMax(): string
    {
        return "2.1.2";
    }

    public function run(string $mode): void
    {
        $query = $mode === "mysql" ? new MySQLHandler() : new SQLiteHandler();
        $query->execute(
            onSuccess: fn(array $records) => Await::f2c(
                function () use ($records): Generator {
                    foreach ($records as $record) {
                        $success = yield from BedrockEconomyAPI::ASYNC()->insert($record["username"], $record["username"], (int) $record["balance"], 0);

                        if (!$success) {
                            BedrockEconomy::getInstance()->getLogger()->warning("Failed to migrate data for player " . $record["username"] . ". (" . $this->getName() . ")");
                        }
                    }
                }
            ),
            onFail: function (): void {
                BedrockEconomy::getInstance()->getLogger()->warning("Failed to migrate data from old database. (" . $this->getName() . ")");
            }
        );
    }
}
