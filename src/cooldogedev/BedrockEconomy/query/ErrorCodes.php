<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\query;

final class ErrorCodes
{
    public const ERROR_CODE_NO_CHANGES_MADE = "error.no_changes_made";
    public const ERROR_CODE_ACCOUNT_NOT_FOUND = "error.account_not_found";
    public const ERROR_CODE_BALANCE_INSUFFICIENT = "error.balance_insufficient";
    // TODO: Add a new translation for this error code
    public const ERROR_CODE_BALANCE_INSUFFICIENT_OTHER = "error.balance_insufficient_other";
    // TODO: Add a new translation for this error code
    public const ERROR_CODE_NEW_BALANCE_EXCEEDS_CAP = "error.new_balance_exceeds_cap";
    public const ERROR_CODE_BALANCE_CAP_EXCEEDED = "error.balance_cap_exceeded";
}
