<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\api\util;

use Closure;

final class ClosureWrapper
{
    public static function combine(... $closures): Closure
    {
        return function (...$params) use ($closures): void {
            foreach ($closures as $closure) {
                $closure(... $params);
            }
        };
    }
}
