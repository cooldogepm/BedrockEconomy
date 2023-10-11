<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\api\util;

use Closure;

final class ClosureContext
{
    public static function create(... $closures): Closure
    {
        return ClosureWrapper::combine(... $closures);
    }
}
