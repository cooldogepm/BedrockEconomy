<?php

/**
 *  Copyright (c) 2021-2023 cooldogedev
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

namespace cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\thread;

use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\query\SQLQuery;
use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\thread\Thread;

abstract class SQLThread extends Thread
{
    protected SleeperHandlerEntry $sleeperHandlerEntry;

    protected ThreadSafeArray $queries;
    protected ThreadSafeArray $completeQueries;

    protected bool $running = false;

    public function __construct()
    {
        $this->queries = new ThreadSafeArray();
        $this->completeQueries = new ThreadSafeArray();
    }

    protected function onRun(): void
    {
        $this->running = true;

        $notifier = $this->sleeperHandlerEntry->createNotifier();

        while ($this->running) {
            $this->synchronized(
                function (): void {
                    if ($this->running && $this->queries->count() === 0 && $this->completeQueries->count() === 0) {
                        $this->wait();
                    }
                }
            );

            if ($this->completeQueries->count() !== 0) {
                $notifier->wakeupSleeper();
            }

            /**
             * @var SQLQuery|null $query
             */
            $query = $this->queries->shift();

            if ($query === null) {
                continue;
            }

            $query->run();

            $this->completeQueries[] = $query;
        }
    }

    public function quit(): void
    {
        $this->synchronized(
            function (): void {
                $this->running = false;
                $this->notify();
            }
        );

        parent::quit();
    }

    public function setSleeperHandlerEntry(SleeperHandlerEntry $sleeperHandlerEntry): void
    {
        $this->sleeperHandlerEntry = $sleeperHandlerEntry;
    }

    public function addQuery(SQLQuery $query): void
    {
        $this->synchronized(
            function () use ($query): void {
                $this->queries[] = $query;
                $this->notify();
            }
        );
    }

    /**
     * @return ThreadSafeArray<SQLQuery>
     */
    public function getQueries(): ThreadSafeArray
    {
        return $this->queries;
    }

    /**
     * @return ThreadSafeArray<SQLQuery>
     */
    public function getCompleteQueries(): ThreadSafeArray
    {
        return $this->completeQueries;
    }
}