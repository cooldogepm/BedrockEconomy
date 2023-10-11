<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\migration\v2_1_2;

use cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class SQLiteHandler extends SQLiteQuery
{
    public function onRun(SQLite3 $connection): void
    {
        $query = $connection->query("SELECT * FROM bedrock_economy");

        $rows = [];

        while (($row = $query->fetchArray(SQLITE3_ASSOC)) !== false) {
            $rows[] = $row;
        }

        $this->setResult($rows);

        $query->finalize();
    }
}
