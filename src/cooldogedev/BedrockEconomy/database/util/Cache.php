<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2023 cooldogedev
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

namespace cooldogedev\BedrockEconomy\database\util;

final class Cache
{
    protected static array $cache = [];

    public static function getAll(): array
    {
        return Cache::$cache;
    }

    public static function get(string $key): ?string
    {
        return Cache::$cache[$key] ?? null;
    }

    public static function set(string $key, string $value): void
    {
        Cache::$cache[$key] = $value;
    }

    public static function setAll(array $data): void
    {
        Cache::$cache = $data;
    }

    public static function delete(string $key): void
    {
        if (!Cache::exists($key)) {
            return;
        }

        unset(Cache::$cache[$key]);
    }

    public static function exists(string $key): bool
    {
        return isset(Cache::$cache[$key]);
    }

    public static function clear(): void
    {
        Cache::$cache = [];
    }
}
