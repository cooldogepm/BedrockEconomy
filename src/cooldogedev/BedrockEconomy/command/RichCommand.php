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

namespace cooldogedev\BedrockEconomy\command;

use cooldogedev\BedrockEconomy\command\constant\PermissionList;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use cooldogedev\BedrockEconomy\language\KnownMessages;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\language\TranslationKeys;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\CortexPE\Commando\args\IntegerArgument;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use function max;

final class RichCommand extends BaseCommand
{
    private const ARGUMENT_PAGE = "page";
    private const DEFAULT_LIMIT = 10;

    protected function prepare(): void
    {
        $this->setPermission(PermissionList::COMMAND_RICH_PERMISSION);

        $this->registerArgument(0, new IntegerArgument(RichCommand::ARGUMENT_PAGE, true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$this->getOwningPlugin()->isReady()) {
            return;
        }

        if (count(GlobalCache::TOP()->getAll()) === 0) {
            GlobalCache::invalidate();
            $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_RICH_NO_RECORDS));
            return;
        }

        $offset = $args[RichCommand::ARGUMENT_PAGE] ?? 0;
        $offset = max($offset, 1);
        $offset = ($offset - 1) * RichCommand::DEFAULT_LIMIT;

        $entries = GlobalCache::TOP()->getAll();
        $entries = array_slice($entries, $offset, RichCommand::DEFAULT_LIMIT);

        if (count($entries) === 0) {
            $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_RICH_NO_RECORDS));
            return;
        }

        $sender->sendMessage(LanguageManager::getString(KnownMessages::RICH_HEADER));

        foreach ($entries as $username => $entry) {
            $sender->sendMessage(LanguageManager::getString(KnownMessages::RICH_ENTRY,
                [
                    TranslationKeys::PLAYER => $username,
                    TranslationKeys::AMOUNT => $this->getOwningPlugin()->getCurrency()->formatter->format($entry->amount, $entry->decimals),
                    TranslationKeys::POSITION => $entry->position,
                ]
            ));
        }

        $sender->sendMessage(LanguageManager::getString(KnownMessages::RICH_FOOTER, [
            TranslationKeys::POSITION => GlobalCache::ONLINE()->get($sender->getName())->position ?? "N/A",
        ]));
    }
}