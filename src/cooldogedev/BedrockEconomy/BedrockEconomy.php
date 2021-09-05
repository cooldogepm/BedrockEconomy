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

namespace cooldogedev\BedrockEconomy;

use cooldogedev\BedrockEconomy\command\admin\AddBalanceCommand;
use cooldogedev\BedrockEconomy\command\admin\DeleteAccountCommand;
use cooldogedev\BedrockEconomy\command\admin\RemoveBalanceCommand;
use cooldogedev\BedrockEconomy\command\admin\SetBalanceCommand;
use cooldogedev\BedrockEconomy\command\BalanceCommand;
use cooldogedev\BedrockEconomy\command\PayCommand;
use cooldogedev\BedrockEconomy\config\ConfigManager;
use cooldogedev\BedrockEconomy\currency\CurrencyManager;
use cooldogedev\BedrockEconomy\database\DatabaseManager;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\listener\PlayerListener;
use cooldogedev\BedrockEconomy\session\SessionManager;
use CortexPE\Commando\BaseCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

final class BedrockEconomy extends PluginBase
{
    protected LanguageManager $languageManager;
    protected ConfigManager $configManager;
    protected CurrencyManager $currencyManager;
    protected DatabaseManager $databaseManager;
    protected SessionManager $sessionManager;

    public function getDatabaseManager(): DatabaseManager
    {
        return $this->databaseManager;
    }

    public function getSessionManager(): SessionManager
    {
        return $this->sessionManager;
    }

    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }

    public function getCurrencyManager(): CurrencyManager
    {
        return $this->currencyManager;
    }

    protected function onLoad(): void
    {
        foreach ($this->getResources() as $resource) {
            $this->saveResource($resource->getFilename());
        }
        LanguageManager::init($this, $this->getConfig()->get("language"));
    }

    protected function onEnable(): void
    {
        $this->configManager = new ConfigManager($this);
        $this->currencyManager = new CurrencyManager($this);
        $this->databaseManager = new DatabaseManager($this);
        $this->sessionManager = new SessionManager($this);

        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);

        $this->initializeCommands();
    }

    protected function initializeCommands(): void
    {
        $commandsConfig = LanguageManager::getArray("commands");

        $commands = array_map(
            function (array $data): array {
                $className = match ($data["name"]) {
                    "balance" => BalanceCommand::class,
                    "pay" => PayCommand::class,
                    "addbalance" => AddBalanceCommand::class,
                    "deleteaccount" => DeleteAccountCommand::class,
                    "removebalance" => RemoveBalanceCommand::class,
                    "setbalance" => SetBalanceCommand::class,
                };
                return [$data, $className];
            },
            $commandsConfig
        );

        $commands = array_map(function (array $commandData): BaseCommand {
            [$data, $className] = $commandData;
            /**
             * @var BaseCommand $command
             */
            $command = new $className($this, $data["name"], $data["description"], $data["aliases"]);
            $command->setUsage(TextFormat::colorize($data["usage"]));
            return $command;
        }, $commands);

        $this->getServer()->getCommandMap()->registerAll("bedrockeconomy", $commands);
    }
}
