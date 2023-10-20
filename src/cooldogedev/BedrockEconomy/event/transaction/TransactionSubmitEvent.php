<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\event\transaction;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 * This event is called when a transaction is submitted.
 */
final class TransactionSubmitEvent extends TransactionEvent implements Cancellable
{
    use CancellableTrait;
}
