<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\migration\economyapi;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\database\migration\IMigration;
use Generator;
use SOFe\AwaitGenerator\Await;

final class Migration implements IMigration
{
    public function getName(): string
    {
        return "EconomyAPI";
    }

    public function getMin(): string
    {
        return "0.0.1";
    }

    public function getMax(): string
    {
        return "0.0.1";
    }

    public function run(string $mode): bool
    {
        $economyAPI = BedrockEconomy::getInstance()->getServer()->getPluginManager()->getPlugin("EconomyAPI");

        if ($economyAPI === null) {
            return false;
        }

        Await::f2c(
            function () use ($economyAPI): Generator {
                foreach ($economyAPI->getAllMoney() as $username => $money) {
                    $money = explode(".", (string)$money);
                    $amount = $money[0] ?? 0;
                    $decimals = $money[1] ?? 0;

                    $success = yield from BedrockEconomyAPI::ASYNC()->insert($username, $username, $amount, $decimals);

                    if (!$success) {
                        BedrockEconomy::getInstance()->getLogger()->warning("Failed to migrate data for player " . $username . ". (" . $this->getName() . ")");
                    }
                }
            }
        );

        return true;
    }
}
