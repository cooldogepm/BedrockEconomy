<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\command\argument;

use CortexPE\Commando\args\RawStringArgument;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

final class FloatArgument extends RawStringArgument
{
    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_FLOAT;
    }
}
