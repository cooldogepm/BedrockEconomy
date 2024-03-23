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

namespace cooldogedev\BedrockEconomy\database\cache;

final class Cache
{
    /**
     * @param CacheEntry[] $entries
     */
    public function __construct(private array $entries = []) {}

    public function sort(): void
    {
        uasort($this->entries, static fn (CacheEntry $a, CacheEntry $b) => [$b->amount, $b->decimals] <=> [$a->amount, $a->decimals]);

        $i = 1;

        foreach ($this->entries as $key => $entry) {
            $this->entries[$key] = new CacheEntry($entry->amount, $entry->decimals, $i);
            $i++;
        }
    }

    public function get(string $key): ?CacheEntry
    {
        $entry = $this->entries[$key] ?? null;

        if ($entry === null) {
            foreach ($this->entries as $_key => $value) {
                if (strtolower($_key) === strtolower($key)) {
                    $entry = $value;
                    break;
                }
            }
        }

        return $entry;
    }

    public function getAll(): array
    {
        return $this->entries;
    }

    public function set(string $key, CacheEntry $entry): void
    {
        $this->entries[$key] = $entry;
    }

    public function setAll(array $entries): void
    {
        $this->entries = $entries;
    }

    public function remove(string $key): void
    {
        if (!$this->exists($key)) {
            return;
        }

        unset($this->entries[$key]);
    }

    public function exists(string $key): bool
    {
        return isset($this->entries[$key]);
    }
}