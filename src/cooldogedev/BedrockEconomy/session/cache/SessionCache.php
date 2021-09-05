<?php

/**
 *  Copyright (c) 2021 cooldogedev
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\session\cache;

use cooldogedev\BedrockEconomy\event\balance\BalanceChangeEvent;
use cooldogedev\BedrockEconomy\session\Session;

final class SessionCache
{
    public function __construct(protected Session $session, protected int $lastUpdate, protected int $balance, protected bool $loaded, protected bool $new, protected bool $awaitingSave)
    {
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function setLoaded(bool $loaded): void
    {
        $this->loaded = $loaded;
    }

    public function addToBalance(int $addition): bool
    {
        return $this->setBalance($this->getBalance() + $addition);
    }

    public function setBalance(int $balance): bool
    {
        if ($balance === $this->getBalance()) {
            return false;
        }

        $event = new BalanceChangeEvent($this->getSession(), $balance);
        $event->call();
        if ($event->isCancelled()) {
            return false;
        }

        $this->balance = $balance;
        $this->setAwaitingSave(true);
        return true;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function setAwaitingSave(bool $awaitingSave): void
    {
        $this->awaitingSave = $awaitingSave;
    }

    public function subtractFromBalance(int $subtraction): bool
    {
        return $this->setBalance($this->getBalance() - $subtraction);
    }

    public function getLastUpdate(): int
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(int $lastUpdate): void
    {
        $this->lastUpdate = $lastUpdate;
    }

    public function isAwaitingSave(): bool
    {
        return $this->awaitingSave;
    }

    public function isNew(): bool
    {
        return $this->new;
    }

    public function setNew(bool $new): void
    {
        $this->new = $new;
    }
}
