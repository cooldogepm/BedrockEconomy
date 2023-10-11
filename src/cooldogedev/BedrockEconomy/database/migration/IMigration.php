<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\migration;

interface IMigration
{
    public function getName(): string;
    public function getMin(): string;
    public function getMax(): string;

    public function run(string $mode): void;
}
