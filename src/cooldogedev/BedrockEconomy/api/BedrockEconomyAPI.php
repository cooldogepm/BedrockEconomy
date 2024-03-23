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

namespace cooldogedev\BedrockEconomy\api;

use cooldogedev\BedrockEconomy\api\type\AsyncAPI;
use cooldogedev\BedrockEconomy\api\type\BetaAPI;
use cooldogedev\BedrockEconomy\api\type\ClosureAPI;
use cooldogedev\BedrockEconomy\api\type\LegacyAPI;

final class BedrockEconomyAPI
{
    private static AsyncAPI $async;
    private static ClosureAPI $closure;
    private static LegacyAPI $legacy;
    private static BetaAPI $beta;

    public static function init(): void
    {
        BedrockEconomyAPI::$async = new AsyncAPI();
        BedrockEconomyAPI::$closure = new ClosureAPI();
        BedrockEconomyAPI::$legacy = new LegacyAPI();
        BedrockEconomyAPI::$beta = new BetaAPI();
    }

    public static function ASYNC(): AsyncAPI
    {
        return BedrockEconomyAPI::$async;
    }

    public static function CLOSURE(): ClosureAPI
    {
        return BedrockEconomyAPI::$closure;
    }

    /**
     * @deprecated
     */
    public static function legacy(): LegacyAPI
    {
        return BedrockEconomyAPI::$legacy;
    }

    /**
     * @deprecated
     */
    public static function beta(): BetaAPI
    {
        return BedrockEconomyAPI::$beta;
    }

    /**
     * @deprecated
     */
    public static function getInstance(): LegacyAPI
    {
        return BedrockEconomyAPI::$legacy;
    }
}