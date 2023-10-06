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
use cooldogedev\BedrockEconomy\database\constant\Search;
use cooldogedev\BedrockEconomy\database\exception\AccountNotFoundException;
use cooldogedev\BedrockEconomy\language\KnownTranslations;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\language\TranslationKeys;
use cooldogedev\libSQL\exception\SQLException;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use Generator;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

final class BalanceCommand extends BaseCommand
{
    protected const ARGUMENT_PLAYER = "player";

    protected function prepare(): void
    {
        $this->setPermission(PermissionList::COMMAND_BALANCE_PERMISSION);

        $this->registerArgument(0, new RawStringArgument(BalanceCommand::ARGUMENT_PLAYER, true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = $args[BalanceCommand::ARGUMENT_PLAYER] ?? null;

        if (!$sender instanceof Player && $player === null) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        if ($player !== null) {
            $playerExact = $this->getOwningPlugin()->getServer()->getPlayerExact($player);

            if ($playerExact !== null) {
                $player = $playerExact->getName();
            }
        }

        $player ??= $sender->getName();

        $isSelf = $player === $sender->getName();

        Await::f2c(
            function () use ($sender, $player, $isSelf): Generator {
try {
    $result = yield from BedrockEconomyAPI::ASYNC()->get(Search::EMPTY, $player);
    $sender->sendMessage(LanguageManager::getTranslation($isSelf ? KnownTranslations::BALANCE_INFO : KnownTranslations::BALANCE_INFO_OTHER, [
        TranslationKeys::PLAYER => $player,
        TranslationKeys::AMOUNT => $this->getOwningPlugin()->getCurrency()->formatter->format($result["amount"], $result["decimals"]),
    ]));
} catch (AccountNotFoundException) {
    $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::ERROR_ACCOUNT_NONEXISTENT));
} catch (SQLException) {
    $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::ERROR_DATABASE));
}
            }
        );
    }
}
