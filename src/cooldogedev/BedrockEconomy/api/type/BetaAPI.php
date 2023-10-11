<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\api\type;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\constant\Search;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

/**
 * @deprecated This API is deprecated and will be removed in the future.
 * Use the new APIs instead. (Async, Closure) @see BedrockEconomyAPI
 */
final class BetaAPI extends BaseAPI
{
    public function getName(): string
    {
        return "Beta";
    }

    public function getVersion(): string
    {
        return "1.0.0";
    }

    public function get(string $player): Promise
    {
        $promise = new PromiseResolver();

        BedrockEconomyAPI::CLOSURE()->get(Search::EMPTY, $player, fn(int $amount) => $promise->resolve($amount), fn() => $promise->reject());

        return $promise->getPromise();
    }

    public function set(string $player, int $amount): Promise
    {
        $promise = new PromiseResolver();

        BedrockEconomyAPI::CLOSURE()->set(Search::EMPTY, $player, $amount, 0, fn(bool $success) => $promise->resolve($success), fn() => $promise->reject());

        return $promise->getPromise();
    }

    public function add(string $player, int $amount): Promise
    {
        $promise = new PromiseResolver();

        BedrockEconomyAPI::CLOSURE()->add(Search::EMPTY, $player, $amount, 0, fn(bool $success) => $promise->resolve($success), fn() => $promise->reject());

        return $promise->getPromise();
    }

    public function deduct(string $player, int $amount): Promise
    {
        $promise = new PromiseResolver();

        BedrockEconomyAPI::CLOSURE()->subtract(Search::EMPTY, $player, $amount, 0, fn(bool $success) => $promise->resolve($success), fn() => $promise->reject());

        return $promise->getPromise();
    }

    public function transfer(string $source, string $target, int $amount): Promise
    {
        $promise = new PromiseResolver();

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
            onSuccess: fn() => $promise->resolve(true),
            onError: fn() => $promise->reject()
        );

        return $promise->getPromise();
    }
}
