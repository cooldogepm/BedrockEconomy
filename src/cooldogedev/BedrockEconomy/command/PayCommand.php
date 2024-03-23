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

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\command\argument\FloatArgument;
use cooldogedev\BedrockEconomy\command\constant\PermissionList;
use cooldogedev\BedrockEconomy\currency\CurrencyFormatter;
use cooldogedev\BedrockEconomy\database\constant\Limits;
use cooldogedev\BedrockEconomy\database\exception\InsufficientFundsException;
use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\language\KnownMessages;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\language\TranslationKeys;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\exception\SQLException;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\CortexPE\Commando\args\IntegerArgument;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\CortexPE\Commando\args\RawStringArgument;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\CortexPE\Commando\BaseCommand;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\CortexPE\Commando\constraint\InGameRequiredConstraint;
use Generator;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\SOFe\AwaitGenerator\Await;

final class PayCommand extends BaseCommand
{
    private const ARGUMENT_TARGET = "target";
    private const ARGUMENT_AMOUNT = "amount";

    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));

        $this->setPermission(PermissionList::COMMAND_PAY_PERMISSION);

        $this->registerArgument(0, new RawStringArgument(PayCommand::ARGUMENT_TARGET));

        if ($this->getOwningPlugin()->getCurrency()->decimals) {
            $this->registerArgument(1, new FloatArgument(PayCommand::ARGUMENT_AMOUNT));
        } else {
            $this->registerArgument(1, new IntegerArgument(PayCommand::ARGUMENT_AMOUNT));
        }
    }

    /**
     * @param Player $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$this->getOwningPlugin()->isReady()) {
            return;
        }

        $player = $args[PayCommand::ARGUMENT_TARGET];
        $amount = $args[PayCommand::ARGUMENT_AMOUNT];

        $playerExact = $this->getOwningPlugin()->getServer()->getPlayerExact($player);

        if ($playerExact !== null) {
            $player = $playerExact->getName();
        }

        if (strtolower($sender->getName()) === strtolower($player)) {
            $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_PAY_SELF));
            return;
        }

        if (!is_numeric($amount)) {
            $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_AMOUNT_INVALID));
            return;
        }

        if ($amount <= 0) {
            $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_AMOUNT_SMALL));
            return;
        }

        if ($amount > Limits::INT63_MAX) {
            $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_AMOUNT_LARGE));
            return;
        }

        $amount = explode(".", (string)$amount);

        $balance = (int)$amount[0];
        $decimals = (int)($amount[1] ?? 0);
        if ($decimals >= 100) {
            $decimals = 99;
        }

        Await::f2c(
            function () use ($sender, $player, $balance, $decimals): Generator {
                try {
                    yield from BedrockEconomyAPI::ASYNC()->transfer(
                        source: [
                            "username" => $sender->getName(),
                            "xuid" => $sender->getXuid(),
                        ],
                        target: [
                            "username" => $player,
                            "xuid" => $player,
                        ],
                        amount: $balance,
                        decimals: $decimals,
                    );
                    $sender->sendMessage(LanguageManager::getString(KnownMessages::BALANCE_PAY, [
                        TranslationKeys::PLAYER => $player,
                        TranslationKeys::AMOUNT => $this->getOwningPlugin()->getCurrency()->formatter->format($balance, $decimals),
                    ]));

                    $target = $this->getOwningPlugin()->getServer()->getPlayerExact($player);

                    if ($target === null) {
                        return;
                    }

                    $target->sendMessage(LanguageManager::getString(KnownMessages::BALANCE_PAY_RECEIVE, [
                        TranslationKeys::PLAYER => $sender->getName(),
                        TranslationKeys::AMOUNT => $this->getOwningPlugin()->getCurrency()->formatter->format($balance, $decimals),
                    ]));
                } catch (RecordNotFoundException) {
                    $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_ACCOUNT_NONEXISTENT));
                } catch (InsufficientFundsException) {
                    $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_ACCOUNT_INSUFFICIENT));
                } catch (SQLException $exception) {
                    $sender->sendMessage(LanguageManager::getString(KnownMessages::ERROR_DATABASE));
                    $this->getOwningPlugin()->getLogger()->logException($exception);
                }
            }
        );
    }
}