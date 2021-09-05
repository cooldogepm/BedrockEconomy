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

namespace cooldogedev\BedrockEconomy\command\admin;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\constant\SearchConstants;
use cooldogedev\BedrockEconomy\language\KnownTranslations;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\language\TranslationKeys;
use cooldogedev\BedrockEconomy\permission\BedrockEconomyPermissions;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use Exception;
use pocketmine\command\CommandSender;

final class AddBalanceCommand extends BaseCommand
{
    protected const ARGUMENT_PLAYER = "player";
    protected const ARGUMENT_AMOUNT = "amount";

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = $args[AddBalanceCommand::ARGUMENT_PLAYER];
        $amount = $args[AddBalanceCommand::ARGUMENT_AMOUNT];

        $session = $this->getOwningPlugin()->getSessionManager()->getSession($player, SearchConstants::SEARCH_MODE_USERNAME);

        if (!$session) {
            $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::PLAYER_NOT_FOUND, [
                    TranslationKeys::PLAYER => $player
                ]
            ));
            return;
        }

        if (!is_numeric($amount)) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        $amount = (int)floor($amount);

        $session->getCache()->addToBalance($amount);

        $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::BALANCE_ADD, [
                TranslationKeys::PLAYER => $session->getUsername(),
                TranslationKeys::AMOUNT => $amount,
                TranslationKeys::CURRENCY_NAME => $this->getOwningPlugin()->getCurrencyManager()->getName(),
                TranslationKeys::CURRENCY_SYMBOL => $this->getOwningPlugin()->getCurrencyManager()->getSymbol()
            ]
        ));
    }

    public function getOwningPlugin(): BedrockEconomy
    {
        return parent::getOwningPlugin();
    }

    protected function prepare(): void
    {
        $this->setPermission(BedrockEconomyPermissions::COMMAND_ADD_BALANCE_PERMISSION);
        try {
            $this->registerArgument(0, new RawStringArgument(AddBalanceCommand::ARGUMENT_PLAYER));
            $this->registerArgument(1, new IntegerArgument(AddBalanceCommand::ARGUMENT_AMOUNT));
        } catch (Exception) {
        }
    }
}
