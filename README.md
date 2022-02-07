# BedrockEconomy
BedrockEconomy is an economy plugin made for PocketMine-MP focused on stability and simplicity

## Commands
| Name | Description | Usage | Permission |
| ------- | ----------- | ----- | ---------- |
| balance | Show your and others balance | `balance [player: string]` | `bedrockeconomy.command.balance` |
| pay | Pay others with your balance | `pay <player: string> <amount: number>`  | `bedrockeconomy.command.pay` |
| topbalance | View the top balances | `topbalance [page: number]` | `bedrockeconomy.command.topbalance` |
| addbalance | Add points to others balance | `addbalance <player: string> <amount: number>`  | `bedrockeconomy.command.addbalance` |
| removebalance | Remove points from others balance | `removebalance <player: string> <amount: number>`  | `bedrockeconomy.command.removebalance` |
| setbalance | Set others balance | `setbalance <player: string> <balance: number>`  | `bedrockeconomy.command.setbalance` |
| deleteaccount | Delete others account data | `deleteaccount <player: string>`  | `bedrockeconomy.command.deleteaccount` |

## API

### Get the balance of a player

```php
BedrockEconomyAPI::getInstance()->getPlayerBalance(
    "Steve",
    ClosureContext::create(
        function (?int $balance): void {
            var_dump($balance);
        },
    )
);
```

### Increment the balance of a player

```php
BedrockEconomyAPI::getInstance()->addToPlayerBalance(
    "Steve",
    1000,
    ClosureContext::create(
        function (bool $wasUpdated): void {
            var_dump($wasUpdated);
        },
    )
);
```

### Decrement the balance of a player

```php
BedrockEconomyAPI::getInstance()->subtractFromPlayerBalance(
    "Steve",
    1000,
    ClosureContext::create(
        function (bool $wasUpdated): void {
            var_dump($wasUpdated);
        },
    )
);
```

### Update the balance of a player

```php
BedrockEconomyAPI::getInstance()->setPlayerBalance(
    "Steve",
    1000,
    ClosureContext::create(
        function (bool $wasUpdated): void {
            var_dump($wasUpdated);
        },
    )
);
```

### Check if a player has an account

```php
BedrockEconomyAPI::getInstance()->isAccountExists(
    "Steve",
    ClosureContext::create(
        function (bool $hasAccount): void {
            var_dump($hasAccount);
        },
    )
);
```

### Delete a player's account

```php
BedrockEconomyAPI::getInstance()->deletePlayerAccount(
    "Steve",
    ClosureContext::create(
        function (bool $operationSuccessful): void {
            var_dump($operationSuccessful);
        },
    )
);
```

### Get highest balances

```php
BedrockEconomyAPI::getInstance()->getHighestBalances(
    limit: 10,
    context: ClosureContext::create(
        function (?array $accounts) use ($sender, $offset): void {
            if (!$accounts) {
                var_dump("The table is empty.");
                return;
            }

            foreach ($accounts as $account) {
                var_dump($account["username"] . ": " . $account["balance"]);
            }
        }
    ),
    /**
     * Offset is used to skip the first n * limited results.
     * By default is set to 0 to retrieve the top n results starting from 0
     */
    offset: 1,
);
```

## Tools

* [Migration from EconomyAPI](https://github.com/cooldogedev/EconAPIToBE)
