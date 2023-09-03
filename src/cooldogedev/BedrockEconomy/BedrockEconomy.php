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

namespace cooldogedev\BedrockEconomy;

use cooldogedev\BedrockEconomy\account\AccountManager;
use cooldogedev\BedrockEconomy\addon\AddonManager;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\version\LegacyBEAPI;
use cooldogedev\BedrockEconomy\command\admin\AddBalanceCommand;
use cooldogedev\BedrockEconomy\command\admin\DeleteAccountCommand;
use cooldogedev\BedrockEconomy\command\admin\RemoveBalanceCommand;
use cooldogedev\BedrockEconomy\command\admin\SetBalanceCommand;
use cooldogedev\BedrockEconomy\command\BalanceCommand;
use cooldogedev\BedrockEconomy\command\PayCommand;
use cooldogedev\BedrockEconomy\command\TopBalanceCommand;
use cooldogedev\BedrockEconomy\config\ConfigManager;
use cooldogedev\BedrockEconomy\currency\CurrencyManager;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\listener\PlayerListener;
use cooldogedev\BedrockEconomy\query\QueryManager;
use cooldogedev\BedrockEconomy\transaction\TransactionManager;
use cooldogedev\libSQL\ConnectionPool;
use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class BedrockEconomy extends PluginBase
{
    use SingletonTrait {
        getInstance as protected _getInstance;
        setInstance as protected;
    }

    protected ConfigManager $configManager;
    protected CurrencyManager $currencyManager;
    protected ConnectionPool $connector;
    protected AccountManager $accountManager;
    protected TransactionManager $transactionManager;
    protected AddonManager $addonManager;

    public static function getInstance(): BedrockEconomy
    {
        return BedrockEconomy::_getInstance();
    }

    public function getAccountManager(): AccountManager
    {
        return $this->accountManager;
    }

    /**
     * @return LegacyBEAPI
     * @link BedrockEconomyAPI::beta() for the new API
     *
     * @deprecated used only for backwards compatibility.
     */
    public function getAPI(): LegacyBEAPI
    {
        return BedrockEconomyAPI::legacy();
    }

    public function getTransactionManager(): TransactionManager
    {
        return $this->transactionManager;
    }

    protected function onLoad(): void
    {
        class_alias(ClosureContext::class, \cooldogedev\libSQL\context\ClosureContext::class);

        foreach ($this->getResources() as $resource) {
            $this->saveResource($resource->getFilename());
        }
        LanguageManager::init($this, $this->getConfig()->get("language"));
        BedrockEconomy::setInstance($this);
    }

    protected function onEnable(): void
    {
        $this->configManager = new ConfigManager($this);
        $this->currencyManager = new CurrencyManager($this);
        $this->connector = new ConnectionPool($this, $this->getConfigManager()->getDatabaseConfig());
        $this->transactionManager = new TransactionManager($this);
        $this->accountManager = new AccountManager($this);
        $this->addonManager = new AddonManager($this);

        QueryManager::setIsMySQL($this->getConfigManager()->getDatabaseConfig()["provider"] === "mysql");

        $this->getConnector()->submit(QueryManager::getTableCreationQuery($this->getCurrencyManager()->getDefaultBalance()));

        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);

        $this->initializeCommands();
    }

    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }

    public function getConnector(): ConnectionPool
    {
        return $this->connector;
    }

    public function getCurrencyManager(): CurrencyManager
    {
        return $this->currencyManager;
    }

    protected function initializeCommands(): void
    {
        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $commands = [];

        $commandsData = LanguageManager::getArray("commands");

        $classMap = [
            "balance" => BalanceCommand::class,
            "pay" => PayCommand::class,
            "top-balance" => TopBalanceCommand::class,
            "add-balance" => AddBalanceCommand::class,
            "remove-balance" => RemoveBalanceCommand::class,
            "set-balance" => SetBalanceCommand::class,
            "delete-account" => DeleteAccountCommand::class
        ];

        foreach ($commandsData as $key => $commandData) {
            $className = $classMap[$key] ?? null;

            if ($className === null) {
                continue;
            }

            $command = new $className($this, $commandData["name"], $commandData["description"], $commandData["aliases"]);
            $command->setUsage(TextFormat::colorize($commandData["usage"]));

            $commands[] = $command;
        }

        $this->getServer()->getCommandMap()->registerAll("bedrockeconomy", $commands);
    }

    protected function onDisable(): void
    {
        foreach ($this->getAddonManager()->getAddons() as $addon) {
            $addon->setEnabled(false);
        }
    }

    public function getAddonManager(): AddonManager
    {
        return $this->addonManager;
    }
}
