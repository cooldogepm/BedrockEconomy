<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\api\type;

use Closure;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\constant\Search;

/**
 * @deprecated
 */
final class LegacyAPI extends BaseAPI
{
    public function getName(): string
    {
        return "Legacy";
    }

    public function getVersion(): string
    {
        return "1.0.0";
    }

    public function setPlayerBalance(string $username, int $amount, ?Closure $handler = null): void
    {
        BedrockEconomyAPI::CLOSURE()->set(Search::EMPTY, $username, $amount, 0, fn () => $handler(true), fn () => $handler(false));
    }

    public function getPlayerBalance(string $username, ?Closure $handler = null): void
    {
        BedrockEconomyAPI::CLOSURE()->get(Search::EMPTY, $username, fn (int $amount) => $handler($amount), fn () => $handler(null));
    }

    public function addToPlayerBalance(string $username, int $amount, ?Closure $handler = null): void
    {
        BedrockEconomyAPI::CLOSURE()->add(Search::EMPTY, $username, $amount, 0, fn () => $handler(true), fn () => $handler(false));
    }

    public function subtractFromPlayerBalance(string $username, int $amount, ?Closure $handler = null): void
    {
        BedrockEconomyAPI::CLOSURE()->subtract(Search::EMPTY, $username, $amount, 0, fn () => $handler(true), fn () => $handler(false));
    }

    public function transferFromPlayerBalance(string $source, string $target, int $amount, ?Closure $handler = null): void
    {
        BedrockEconomyAPI::CLOSURE()->transfer(
            source: [
                "xuid" => Search::EMPTY,
                "username" => $source
            ],
            target: [
                "xuid" => Search::EMPTY,
                "username" => $target
            ],
            amount: $amount,
            decimals: 0,
            onSuccess: fn () => $handler(true),
            onError: fn () => $handler(false)
        );
    }
}
