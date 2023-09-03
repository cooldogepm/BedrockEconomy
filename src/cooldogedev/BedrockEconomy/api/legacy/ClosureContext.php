<?php

/**
 *  Copyright (c) 2022 cooldogedev
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

namespace cooldogedev\BedrockEconomy\api\legacy;

use Closure;
use function array_unshift;

final class ClosureContext
{
    /**
     * @phpstan-var array<int, Closure> $closures
     */
    public function __construct(protected array $closures = []) {}

    public function first(Closure $closure): self
    {
        array_unshift($this->closures, $closure);

        return $this;
    }

    public function push(Closure $closure): self
    {
        $this->closures[] = $closure;
        return $this;
    }

    public function wrap(mixed $result, ?string $error): Closure
    {
        return function () use ($result, $error): void {
            $running = true;

            foreach ($this->closures as $closure) {
                if (!$running) {
                    break;
                }

                $newResult = $closure(
                    $result,
                    function () use (&$running): void {
                        $running = false;
                    },
                    $error
                );

                if ($newResult !== null) {
                    $result = $newResult;
                }
            }
        };
    }

    public static function create(...$closures): self
    {
        return new self($closures);
    }
}
