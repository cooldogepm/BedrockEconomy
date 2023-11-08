<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\helper;

use pocketmine\plugin\Plugin;

trait ReferenceHolder
{
    public function &getRef(mixed $var): mixed
    {
        return $var;
    }
}
