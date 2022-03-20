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

namespace cooldogedev\BedrockEconomy\command;

use Closure;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\language\KnownTranslations;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\language\TranslationKeys;
use cooldogedev\BedrockEconomy\permission\BedrockEconomyPermissions;
use cooldogedev\BedrockEconomy\query\ErrorCodes;
use cooldogedev\libSQL\context\ClosureContext;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use Exception;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

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

        $onlinePlayer = $this->getOwningPlugin()->getServer()->getPlayerByPrefix($receiver);

        if ($onlinePlayer !== null) {
            $receiver = $onlinePlayer->getName();
            $onlinePlayer = null;
        }

        if (strtolower($receiver) === strtolower($sender->getName())) {
            $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::PAYMENT_SEND_SELF));
            return;
        }

        if (!is_numeric($amount)) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        $amount = (int)floor($amount);

        if ($amount < $this->getOwningPlugin()->getCurrencyManager()->getMinimumPayment()) {
            $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::PAYMENT_SEND_INSUFFICIENT, [
                    TranslationKeys::AMOUNT => $amount,
                    TranslationKeys::LIMIT => $this->getOwningPlugin()->getCurrencyManager()->getMinimumPayment(),
                    TranslationKeys::CURRENCY_NAME => $this->getOwningPlugin()->getCurrencyManager()->getName(),
                    TranslationKeys::CURRENCY_SYMBOL => $this->getOwningPlugin()->getCurrencyManager()->getSymbol()
                ]
            ));
            return;
        }

        if ($amount > $this->getOwningPlugin()->getCurrencyManager()->getMaximumPayment()) {
            $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::PAYMENT_SEND_EXCEED_LIMIT, [
                    TranslationKeys::AMOUNT => $amount,
                    TranslationKeys::LIMIT => $this->getOwningPlugin()->getCurrencyManager()->getMaximumPayment(),
                    TranslationKeys::CURRENCY_NAME => $this->getOwningPlugin()->getCurrencyManager()->getName(),
                    TranslationKeys::CURRENCY_SYMBOL => $this->getOwningPlugin()->getCurrencyManager()->getSymbol()
                ]
            ));
            return;
        }

        BedrockEconomyAPI::legacy()->transferFromPlayerBalance($sender->getName(), $receiver, $amount, ClosureContext::create(
            function (bool $success, Closure $_, ?string $error) use ($sender, $receiver, $amount) {
                if ($success) {
                    $receiverPlayer = $this->getOwningPlugin()->getServer()->getPlayerByPrefix($receiver);

                    $receiverPlayer?->sendMessage(LanguageManager::getTranslation(KnownTranslations::PAYMENT_RECEIVE, [
                            TranslationKeys::AMOUNT => $amount,
                            TranslationKeys::PAYER => $sender->getName(),
                            TranslationKeys::CURRENCY_NAME => $this->getOwningPlugin()->getCurrencyManager()->getName(),
                            TranslationKeys::CURRENCY_SYMBOL => $this->getOwningPlugin()->getCurrencyManager()->getSymbol()
                        ]
                    ));
                }

                if ($sender instanceof Player && !$sender->isConnected()) {
                    return;
                }

                if ($error !== null) {

                    $causer = ltrim(strstr($error, ':'), ':');
                    $error = substr($error, 0, strpos($error, ":"));

                    $translation = match ($error) {
                        ErrorCodes::ERROR_CODE_ACCOUNT_NOT_FOUND => KnownTranslations::PLAYER_NOT_FOUND,
                        ErrorCodes::ERROR_CODE_BALANCE_INSUFFICIENT => KnownTranslations::BALANCE_INSUFFICIENT,
                        ErrorCodes::ERROR_CODE_BALANCE_CAP_EXCEEDED => KnownTranslations::BALANCE_CAP,
                        ErrorCodes::ERROR_CODE_NO_CHANGES_MADE, ErrorCodes::ERROR_CODE_NEW_BALANCE_EXCEEDS_CAP => KnownTranslations::UPDATE_ERROR,
                    };

                    $sender->sendMessage(LanguageManager::getTranslation($translation, [
                            TranslationKeys::PAYER => $sender->getName(),
                            TranslationKeys::RECEIVER => $receiver,

                            TranslationKeys::PLAYER => $causer,
                            TranslationKeys::AMOUNT => number_format($amount, 0, ".", $this->getOwningPlugin()->getCurrencyManager()->getNumberSeparator()),
                            TranslationKeys::CURRENCY_NAME => $this->getOwningPlugin()->getCurrencyManager()->getName(),
                            TranslationKeys::CURRENCY_SYMBOL => $this->getOwningPlugin()->getCurrencyManager()->getSymbol(),
                            TranslationKeys::LIMIT => $this->getOwningPlugin()->getCurrencyManager()->getBalanceCap() ?? "N/A",
                        ]
                    ));
                    return;
                }

                $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::PAYMENT_SEND, [
                        TranslationKeys::AMOUNT => number_format($amount, 0, ".", $this->getOwningPlugin()->getCurrencyManager()->getNumberSeparator()),
                        TranslationKeys::RECEIVER => $receiver,
                        TranslationKeys::CURRENCY_NAME => $this->getOwningPlugin()->getCurrencyManager()->getName(),
                        TranslationKeys::CURRENCY_SYMBOL => $this->getOwningPlugin()->getCurrencyManager()->getSymbol()
                    ]
                ));
            }
        ));
    }

    /**
     * @return BedrockEconomy
     */
    public function getOwningPlugin(): Plugin
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
