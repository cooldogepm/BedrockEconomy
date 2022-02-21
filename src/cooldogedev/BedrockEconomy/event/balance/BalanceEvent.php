<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\event\balance;

use cooldogedev\BedrockEconomy\event\account\AccountEvent;
use cooldogedev\BedrockEconomy\event\TransactionSubmitEvent;

/**
 * These events are called before TransactionSubmitEvent is called.
 * They also don't get fired if the balance was updated from the API because there's no need for that.
 * Keep in mind that the balance is not updated until the @link TransactionSubmitEvent is called,
 * and it also might NOT update at all due to invalid data and such things.
 */
abstract class BalanceEvent extends AccountEvent
{
}
