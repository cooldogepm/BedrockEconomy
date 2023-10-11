<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\migration\v2_1_2;

use cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class MySQLHandler extends MySQLQuery
{
    public function onRun(mysqli $connection): void
    {
        $query = $connection->query("SELECT * FROM bedrock_economy");

        $this->setResult($query?->fetch_all(MYSQLI_ASSOC) ?? []);

        $query->close();
    }
}
