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
use cooldogedev\BedrockEconomy\currency\CurrencyManager;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use cooldogedev\BedrockEconomy\database\migration\MigrationRegistry;
use cooldogedev\BedrockEconomy\database\QueryManager;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\ConnectionPool;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\CortexPE\Commando\PacketHooker;
use JsonException;
use pocketmine\plugin\PluginBase;
use pocketmine\promise\Promise;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use RuntimeException;
use function class_alias;
use function count;
use function rename;

final class BedrockEconomy extends PluginBase
{
    use SingletonTrait;

    private ConnectionPool $connector;
    private Currency $currency;
    private CurrencyManager $currencyManager;

    /**
     * @var array<int, array{string, string}>|null
     */
    private ?array $migrationInfo;

    private bool $ready = false;

    protected function onLoad(): void
    {
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

        class_alias(ClosureContext::class, libs\cooldogedev\libSQL\context\ClosureContext::class);
        class_alias(ClosureContext::class, api\legacy\ClosureContext::class);

        $this->migrationInfo = [$oldVersion, $oldProvider];
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
        $this->currencyManager = new CurrencyManager($this->currency);

        GlobalCache::init();
        QueryManager::init($this->currency->code, $this->getConfig()->getNested("database.provider") === "mysql");
        QueryManager::TABLE()->execute();

        $this->getScheduler()->scheduleDelayedTask(
            task: new ClosureTask(
                function (): void {
                    [$oldVersion, $oldProvider] = $this->migrationInfo;
                    $promises = [];

                    foreach (MigrationRegistry::get($oldVersion) as $migrationClass) {
                        $migration = new $migrationClass($this);
                        $promise = $migration->run($oldProvider);
                        if ($promise !== null) {
                            $promises[] = $promise;
                        }
                    }

                    if (count($promises) === 0) {
                        $this->ready = true;
                        return;
                    }

                    Promise::all($promises)->onCompletion(fn () => $this->ready = true, fn () => $this->ready = true);
                }
            ),
            delay: 0
        );

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->registerCommands();

        if ($this->getConfig()->get("cache-invalidation") === 0) {
            return;
        }

        $this->getScheduler()->scheduleRepeatingTask(
            task: new ClosureTask(function (): void {
                if (!$this->ready) {
                    return;
                }

                GlobalCache::invalidate();
            }),
            period: $this->getConfig()->getNested("cache.invalidation") * 20
        );
    }

    private function checkConfig(): bool
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

    private function registerCommands(): void
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

    public function getCurrencyManager(): CurrencyManager
    {
        return $this->currencyManager;
    }

    public function isReady(): bool
    {
        return $this->ready;
    }
}