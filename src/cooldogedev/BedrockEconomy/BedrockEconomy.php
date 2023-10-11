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
use cooldogedev\BedrockEconomy\api\util\ClosureContext;
use cooldogedev\BedrockEconomy\command\admin\AddBalanceCommand;
use cooldogedev\BedrockEconomy\command\admin\RemoveBalanceCommand;
use cooldogedev\BedrockEconomy\command\admin\SetBalanceCommand;
use cooldogedev\BedrockEconomy\command\BalanceCommand;
use cooldogedev\BedrockEconomy\command\PayCommand;
use cooldogedev\BedrockEconomy\command\RichCommand;
use cooldogedev\BedrockEconomy\currency\Currency;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use cooldogedev\BedrockEconomy\database\migration\MigrationRegistry;
use cooldogedev\BedrockEconomy\database\QueryManager;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\libSQL\ConnectionPool;
use CortexPE\Commando\PacketHooker;
use JsonException;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use RuntimeException;

final class BedrockEconomy extends PluginBase
{
    protected ConnectionPool $connector;
    protected Currency $currency;

    /**
     * @var array<int, array{string, string}>|null
     */
    protected ?array $migrationInfo;

    use SingletonTrait;

    protected function checkConfig(): bool
    {
        if ($this->getDescription()->getVersion() === $this->getConfig()->get("config-version")) {
            return true;
        }

        $this->getLogger()->warning("An outdated config was found, attempting to generate a new one...");

        if (!rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.old.yml")) {
            $this->getLogger()->critical("An unknown error occurred while attempting to generate the new config");
            return false;
        }

        $this->reloadConfig();

        try {
            $this->getConfig()->set("config-version", $this->getDescription()->getVersion());
            $this->getConfig()->save();
        } catch (JsonException $e) {
            $this->getLogger()->critical("An error occurred while attempting to generate the new config, " . $e->getMessage());
            return false;
        }

        return true;
    }

    protected function onLoad(): void
    {
        class_alias(ClosureContext::class, \cooldogedev\libSQL\context\ClosureContext::class);

        foreach ($this->getResources() as $resource) {
            $this->saveResource($resource->getFilename());
        }

        $oldVersion = $this->getConfig()->get("config-version");
        $oldProvider = $this->getConfig()->getNested("database.provider");

        if (!$this->checkConfig()) {
            throw new RuntimeException("Failed to generate a new config");
        }

        BedrockEconomy::setInstance($this);
        BedrockEconomyAPI::init();
        LanguageManager::init($this, $this->getConfig()->get("language"));
        MigrationRegistry::init();

        class_alias(ClosureContext::class, \cooldogedev\libSQL\context\ClosureContext::class);
        class_alias(ClosureContext::class, api\legacy\ClosureContext::class);

        if ($oldVersion !== $this->getDescription()->getVersion()) {
            $this->migrationInfo = [$oldVersion, $oldProvider];
        } else {
            $this->migrationInfo = null;
        }
    }

    protected function onEnable(): void
    {
        $this->connector = new ConnectionPool($this, $this->getConfig()->get("database"));
        $this->currency = new Currency(
            name: $this->getConfig()->getNested("currency.name"),
            code: $this->getConfig()->getNested("currency.code"),
            symbol: $this->getConfig()->getNested("currency.symbol"),
            format: $this->getConfig()->getNested("currency.formatter"),
            defaultAmount: $this->getConfig()->getNested("currency.default.amount"),
            defaultDecimals: $this->getConfig()->getNested("currency.default.decimals"),
            decimals: $this->getConfig()->getNested("currency.decimals")
        );

        GlobalCache::init();

        QueryManager::init($this->currency->code, $this->getConfig()->getNested("database.provider") === "mysql");
        QueryManager::TABLE()->execute();

        if ($this->migrationInfo !== null) {
            [$oldVersion, $oldProvider] = $this->migrationInfo;

            $migration = MigrationRegistry::get($oldVersion);

            if ($migration === null) {
                $this->getLogger()->debug("No migration found for version " . $oldVersion);
                return;
            }

            $migration->run($oldProvider);
            $this->getLogger()->notice("Migrating data from version " . $oldVersion . " to " . $this->getDescription()->getVersion());
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->registerCommands();

        if ($this->getConfig()->get("cache-invalidation") === 0) {
            return;
        }

        $this->getScheduler()->scheduleRepeatingTask(
            task: new ClosureTask(static fn() => GlobalCache::invalidate()),
            period: $this->getConfig()->getNested("cache.invalidation") * 20
        );
    }

    protected function registerCommands(): void
    {
        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $commands = [];

        $commandsData = LanguageManager::getArray("commands");

        $classMap = [
            "balance" => BalanceCommand::class,
            "pay" => PayCommand::class,
            "rich" => RichCommand::class,
            "add-balance" => AddBalanceCommand::class,
            "remove-balance" => RemoveBalanceCommand::class,
            "set-balance" => SetBalanceCommand::class,
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

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
