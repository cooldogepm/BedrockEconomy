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

namespace cooldogedev\BedrockEconomy\command;

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
use pocketmine\player\Player;

final class PayCommand extends BaseCommand
{
    protected const ARGUMENT_RECEIVER = "receiver";
    protected const ARGUMENT_AMOUNT = "amount";

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::COMMAND_PLAYER_ONLY));
            return;
        }

        $receiver = $args[PayCommand::ARGUMENT_RECEIVER];
        $amount = $args[PayCommand::ARGUMENT_AMOUNT];

        $session = $this->getOwningPlugin()->getSessionManager()->getSession($sender->getXuid());

        if (!$this->getOwningPlugin()->getSessionManager()->hasSession($receiver, SearchConstants::SEARCH_MODE_USERNAME)) {
            $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::PLAYER_NOT_FOUND, [
                    TranslationKeys::PLAYER => $receiver
                ]
            ));
            return;
        }

        if (!is_numeric($amount)) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        $amount = (int)floor($amount);

        if ($amount > $session->getCache()->getBalance()) {
            $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::BALANCE_INSUFFICIENT));
            return;
        }

        $receiver = $this->getOwningPlugin()->getSessionManager()->getSession($receiver, SearchConstants::SEARCH_MODE_USERNAME);

        $session->getCache()->subtractFromBalance($amount);
        $receiver->getCache()->addToBalance($amount);
        if ($receiver->getPlayer()) {
            $receiver->getPlayer()->sendMessage(LanguageManager::getTranslation(KnownTranslations::PAYMENT_RECEIVE, [
                    TranslationKeys::AMOUNT => $amount,
                    TranslationKeys::PAYER => $sender->getName(),
                    TranslationKeys::CURRENCY_NAME => $this->getOwningPlugin()->getCurrencyManager()->getName(),
                    TranslationKeys::CURRENCY_SYMBOL => $this->getOwningPlugin()->getCurrencyManager()->getSymbol()
                ]
            ));
        }

        $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::PAYMENT_SEND, [
                TranslationKeys::AMOUNT => $amount,
                TranslationKeys::RECEIVER => $receiver->getUsername(),
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
        $this->setPermission(BedrockEconomyPermissions::COMMAND_PAY_PERMISSION);
        try {
            $this->registerArgument(0, new RawStringArgument(PayCommand::ARGUMENT_RECEIVER));
            $this->registerArgument(1, new IntegerArgument(PayCommand::ARGUMENT_AMOUNT));
        } catch (Exception) {
        }
    }
}
