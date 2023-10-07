# BedrockEconomy
BedrockEconomy is an economy plugin made for PocketMine-MP focused on stability and simplicity.
<br>
## Table of Contents

- [License](#license)
- [Commands](#commands)
- [Features](#features)
- [Tools](#tools)
- [Examples](#examples)
  - [Retrieving a Player's Balance](#retrieving-a-players-balance)
  - [Adding Funds to a Player's Balance](#adding-funds-to-a-players-balance)
  - [Subtracting Funds from a Player's Balance](#subtracting-funds-from-a-players-balance)
  - [Transferring Funds Between Players](#transferring-funds-between-players)

## License

This API is released under the MIT License. For more information, please refer to the [LICENSE](LICENSE) file.

## Commands
| Name          | Description | Usage                                             | Permission                             |
|---------------| ----------- |---------------------------------------------------|----------------------------------------|
| balance       | Show your and others balance | `balance [player: string]`                        | `bedrockeconomy.command.balance`       |
| pay           | Pay others with your balance | `pay <player: string> <amount: number>`           | `bedrockeconomy.command.pay`           |
| rich          | View the top balances | `rich [page: number]`                             | `bedrockeconomy.command.rich`          |
| addbalance    | Add points to others balance | `addbalance <player: string> <amount: number>`    | `bedrockeconomy.command.addbalance`    |
| removebalance | Remove points from others balance | `removebalance <player: string> <amount: number>` | `bedrockeconomy.command.removebalance` |
| setbalance    | Set others balance | `setbalance <player: string> <balance: number>`   | `bedrockeconomy.command.setbalance`    |

## Features
- [x] MySQL Database
- [x] SQLite Database
- [x] Async API
- [x] Closure API
- [x] Customizable
- [x] Easy to use
- [x] Lightweight
- [x] Fast and efficient
- [x] Cache system

## Examples

### Retrieving a Player's Balance

You can retrieve a player's balance using the `get` method. Here's an example:

```php
BedrockEconomyAPI::CLOSURE()->get(
    xuid: "123456789",
    username: "Doge",
    onSuccess: static function (array $result): void {
        echo "Balance: " . $result["amount"] . " Decimals: " . $result["decimals"] . " Position: " . $result["position"];
    },
    onError: static function (SQLException $exception): void {
        if ($exception instanceof RecordNotFoundException) {
            echo "Record not found";
            return;
        }

        echo $exception->getMessage();
    }
);

// Using async-await
Await::f2c(
    function (): Generator {
        try {
            $result = yield from BedrockEconomyAPI::ASYNC()->get(
                xuid: "123456789",
                username: "Doge",
            );
        } catch (RecordNotFoundException) {
            echo "Account not found";
            return;
        } catch (SQLException) {
            echo "Database error";
            return;
        }
        
        echo "Balance: " . $result["amount"] . " Decimals: " . $result["decimals"] . " Position: " . $result["position"];
    }
);
```

### Adding Funds to a Player's Balance

You can add funds to a player's balance using the `add` method. Here's an example:

```php
BedrockEconomyAPI::CLOSURE()->add(
    xuid: "123456789",
    username: "Doge",
    amount: 55,
    decimals: 25,
    onSuccess: static function (): void {
        echo 'Balance updated successfully.';
    },
    onError: static function (SQLException $exception): void {
        if ($exception instanceof RecordNotFoundException) {
            echo 'Account not found';
            return;
        }

        echo 'An error occurred while updating the balance.';
    }
);

// Using async-await
Await::f2c(
    function () use ($player): Generator {
        try {
            yield from BedrockEconomyAPI::ASYNC()->add(
                xuid: "123456789",
                username: "Doge",
                amount: 55,
                decimals: 25,
            );
            echo 'Balance updated successfully.';
        } catch (RecordNotFoundException) {
            echo 'Account not found';
        } catch (SQLException) {
            echo 'An error occurred while updating the balance.';
        }
    }
);
```

### Subtracting Funds from a Player's Balance

You can subtract funds from a player's balance using the `subtract` method. Here's an example:

```php
BedrockEconomyAPI::CLOSURE()->subtract(
    xuid: "123456789",
    username: "Doge",
    amount: 55,
    decimals: 25,
    onSuccess: static function (): void {
        echo 'Balance updated successfully.';
    },
    onError: static function (SQLException $exception): void {
        if ($exception instanceof RecordNotFoundException) {
            echo 'Account not found';
            return;
        }

        if ($exception instanceof InsufficientFundsException) {
            echo 'Insufficient funds';
            return;
        }

        echo 'An error occurred while updating the balance.';
    }
);

// Using async-await
Await::f2c(
    function () use ($player): Generator {
        try {
            yield from BedrockEconomyAPI::ASYNC()->subtract(
                xuid: "123456789",
                username: "Doge",
                amount: 55,
                decimals: 25,
            );
            echo 'Balance updated successfully.';
        } catch (RecordNotFoundException) {
            echo 'Account not found';
        } catch (InsufficientFundsException) {
            echo 'Insufficient funds';
        } catch (SQLException) {
            echo 'An error occurred while updating the balance.';
        }
    }
);
```

### Transferring Funds Between Players

You can transfer funds from one player to another using the `transfer` method. Here's an example:

```php
$sourcePlayer = ['xuid' => 'source_xuid', 'username' => 'source_username'];
$targetPlayer = ['xuid' => 'target_xuid', 'username' => 'target_username'];

BedrockEconomyAPI::CLOSURE()->transfer(
    source: $sourcePlayer,
    target: $targetPlayer,
    amount: 55,
    decimals: 25,
    onSuccess: static function (): void {
        echo 'Balance transfer successful.';
    },
    onError: static function (SQLException $exception): void {
        if ($exception instanceof RecordNotFoundException) {
            echo 'Account not found';
            return;
        }
        
        if ($exception instanceof InsufficientFundsException) {
            echo 'Insufficient funds';
            return;
        }

        echo 'An error occurred during the balance transfer.';
    }
);

// Using async-await
Await::f2c(
    function () use ($sourcePlayer, $targetPlayer): Generator {
        try {
            yield from BedrockEconomyAPI::ASYNC()->transfer(
                source: $sourcePlayer,
                target: $targetPlayer,
                amount: 55,
                decimals: 25,
            );
            echo 'Balance transfer successful.';
        } catch (RecordNotFoundException) {
            echo 'Account not found';
        } catch (InsufficientFundsException) {
            echo 'Insufficient funds';
        } catch (SQLException) {
            echo 'An error occurred during the balance transfer.';
        }
    }
);
```

These examples demonstrate how to perform common operations using the BedrockEconomy API, such as retrieving player balances, adding and subtracting funds, and transferring funds between players.

## Tools

* [Migration from EconomyAPI](https://github.com/cooldogedev/EconAPIToBE)
