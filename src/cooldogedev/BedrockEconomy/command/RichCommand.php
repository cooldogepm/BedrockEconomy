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

namespace cooldogedev\BedrockEconomy\command;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\command\constant\PermissionList;
use cooldogedev\BedrockEconomy\database\exception\NoRecordsException;
use cooldogedev\BedrockEconomy\language\KnownTranslations;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\language\TranslationKeys;
use cooldogedev\libSQL\exception\SQLException;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use Generator;
use pocketmine\command\CommandSender;
use SOFe\AwaitGenerator\Await;
use function max;

final class RichCommand extends BaseCommand
{
    protected const ARGUMENT_PAGE = "page";
    protected const DEFAULT_LIMIT = 10;

    protected function prepare(): void
    {
        $this->setPermission(PermissionList::COMMAND_RICH_PERMISSION);

        $this->registerArgument(0, new IntegerArgument(RichCommand::ARGUMENT_PAGE, true));
    }

    protected function handleData(array $entries, int $offset): array
    {
        $results = [];
        $position = 0;

        foreach ($entries as $entry) {
            $position++;
            $results[] = LanguageManager::getTranslation(KnownTranslations::RICH_ENTRY,
                [
                    TranslationKeys::PLAYER => $entry["username"],
                    TranslationKeys::POSITION => $position + $offset,
                    TranslationKeys::AMOUNT => $this->getOwningPlugin()->getCurrency()->formatter->format($entry["amount"], $entry["decimals"]),
                ]
            );
        }

        return $results;
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $offset = $args[RichCommand::ARGUMENT_PAGE] ?? 0;
        $offset = max($offset, 1);
        $offset = ($offset - 1) * RichCommand::DEFAULT_LIMIT;

        Await::f2c(
            function () use ($sender, $offset): Generator {
                try {
                    $entries = yield from BedrockEconomyAPI::ASYNC()->top(RichCommand::DEFAULT_LIMIT, $offset, false);
                    $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::RICH_HEADER));

                    foreach ($this->handleData($entries, $offset) as $entry) {
                        $sender->sendMessage($entry);
                    }

                    $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::RICH_FOOTER));
                } catch (NoRecordsException) {
                    $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::ERROR_RICH_NO_RECORDS));
                } catch (SQLException) {
                    $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::ERROR_DATABASE));
                }
            }
        );
    }
}
