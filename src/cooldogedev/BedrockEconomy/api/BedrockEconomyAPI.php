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

namespace cooldogedev\BedrockEconomy\api;

use cooldogedev\BedrockEconomy\account\Account;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\constant\SearchConstants;
use pocketmine\utils\SingletonTrait;

final class BedrockEconomyAPI
{
    use SingletonTrait {
        setInstance as protected;
        getInstance as protected _getInstance;
    }

    protected static bool $registered = false;
    protected static ?BedrockEconomy $bedrockEconomy = null;

    public function __construct()
    {
        BedrockEconomyAPI::setInstance($this);
    }

    public static function register(BedrockEconomy $bedrockEconomy): bool
    {
        if (BedrockEconomyAPI::isRegistered()) {
            return false;
        }
        BedrockEconomyAPI::setRegistered(true);
        BedrockEconomyAPI::setBedrockEconomy($bedrockEconomy);
        return true;
    }

    public static function isRegistered(): bool
    {
        return BedrockEconomyAPI::$registered;
    }

    protected static function setRegistered(bool $registered): void
    {
        BedrockEconomyAPI::$registered = $registered;
    }

    public static function getInstance(): BedrockEconomyAPI
    {
        return BedrockEconomyAPI::_getInstance();
    }

    public function getPlayerBalance(string $player, int $mode = SearchConstants::SEARCH_MODE_USERNAME): int
    {
        return $this->getPlayerSession($player, $mode)->getBalance();
    }

    public function getPlayerSession(string $player, int $mode = SearchConstants::SEARCH_MODE_USERNAME): Account
    {
        return BedrockEconomyAPI::getBedrockEconomy()->getAccountManager()->getAccount($player, $mode);
    }

    protected static function getBedrockEconomy(): ?BedrockEconomy
    {
        return BedrockEconomyAPI::$bedrockEconomy;
    }

    protected static function setBedrockEconomy(?BedrockEconomy $bedrockEconomy): void
    {
        BedrockEconomyAPI::$bedrockEconomy = $bedrockEconomy;
    }

    public function setPlayerBalance(string $player, int $newBalance, int $mode = SearchConstants::SEARCH_MODE_USERNAME): void
    {
        $this->getPlayerSession($player, $mode)->setBalance($newBalance);
    }

    public function addToPlayerBalance(string $player, int $amount, int $mode = SearchConstants::SEARCH_MODE_USERNAME): void
    {
        $this->getPlayerSession($player, $mode)->incrementBalance($amount);
    }

    public function subtractFromPlayerBalance(string $player, int $amount, int $mode = SearchConstants::SEARCH_MODE_USERNAME): void
    {
        $this->getPlayerSession($player, $mode)->decrementBalance($amount);
    }

    public function deletePlayerAccount(string $player, int $mode = SearchConstants::SEARCH_MODE_USERNAME): void
    {
        BedrockEconomyAPI::getBedrockEconomy()->getAccountManager()->deleteAccount($player, $mode);
    }
}
