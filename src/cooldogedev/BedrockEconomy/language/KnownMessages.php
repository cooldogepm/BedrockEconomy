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

namespace cooldogedev\BedrockEconomy\language;

final class KnownMessages
{
    public const ERROR_DATABASE = "error.database";

    public const ERROR_ACCOUNT_NONEXISTENT = "error.account.nonexistent";
    public const ERROR_ACCOUNT_INSUFFICIENT = "error.account.insufficient";

    public const ERROR_AMOUNT_INVALID = "error.amount.invalid";
    public const ERROR_AMOUNT_SMALL = "error.amount.small";
    public const ERROR_AMOUNT_LARGE = "error.amount.large";

    public const ERROR_RICH_NO_RECORDS = "error.rich.no_records";

    public const ERROR_PAY_SELF = "error.pay.self";

    public const BALANCE_INFO = "balance.info";
    public const BALANCE_INFO_OTHER = "balance.info.other";

    public const BALANCE_PAY = "balance.pay";
    public const BALANCE_PAY_RECEIVE = "balance.pay.receive";

    public const BALANCE_ADD = "balance.add";
    public const BALANCE_REMOVE = "balance.remove";
    public const BALANCE_SET = "balance.set";

    public const RICH_HEADER = "rich.header";
    public const RICH_ENTRY = "rich.entry";
    public const RICH_FOOTER = "rich.footer";
}