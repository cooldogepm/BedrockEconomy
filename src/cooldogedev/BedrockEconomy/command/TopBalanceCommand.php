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

use cooldogedev\BedrockEconomy\api\version\BetaBEAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\language\KnownTranslations;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\language\TranslationKeys;
use cooldogedev\BedrockEconomy\permission\BedrockEconomyPermissions;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use Exception;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

final class TopBalanceCommand extends BaseCommand
{
    protected const ARGUMENT_PAGE = "page";
    protected const DEFAULT_LIMIT = 10;

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $limit = $this->getOwningPlugin()->getConfigManager()->getUtilityConfig()["top-balance-accounts-limit"] ?? TopBalanceCommand::DEFAULT_LIMIT;

        $offset = $args[TopBalanceCommand::ARGUMENT_PAGE] ?? 0;
        $offset = $offset > 1 ? $offset : 1;
        $offset = $offset > 0 ? ($offset - 1) * $limit : $offset;

        BetaBEAPI::getInstance()->getSortedBalances($limit, $offset)->onCompletion(
            function (array $data) use ($sender, $offset): void {
                if ($sender instanceof Player && !$sender->isConnected()) {
                    return;
                }

                $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::TOP_BALANCE_HEADER));

                foreach ($this->handleData($data, $offset) as $datum) {
                    $sender->sendMessage($datum);
                }
            },
            function () use ($sender): void {
                if ($sender instanceof Player && !$sender->isConnected()) {
                    return;
                }

                $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::TOP_BALANCE_ERROR));
            }
        );
    }

    /**
     * @return BedrockEconomy
     */
    public function getOwningPlugin(): Plugin
    {
        return parent::getOwningPlugin();
    }

    public function handleData(array $result, int $offset): array
    {
        $newResult = [];
        $position = 0;

        foreach ($result as $account) {
            $position++;
            $newResult[] = LanguageManager::getTranslation(KnownTranslations::TOP_BALANCE_ROW_TEMPLATE,
                [
                    TranslationKeys::PLAYER => $account["username"],
                    TranslationKeys::POSITION => $position + $offset,
                    TranslationKeys::AMOUNT => number_format($account["balance"], 0, ".", $this->getOwningPlugin()->getCurrencyManager()->getNumberSeparator()),
                ]
            );
        }

        return $newResult;
    }

    protected function prepare(): void
    {
        $this->setPermission(BedrockEconomyPermissions::COMMAND_TOP_BALANCE_PERMISSION);
        try {
            $this->registerArgument(0, new IntegerArgument(TopBalanceCommand::ARGUMENT_PAGE, true));
        } catch (Exception) {
        }
    }
}
