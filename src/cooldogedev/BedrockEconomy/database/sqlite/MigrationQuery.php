<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\sqlite;

use cooldogedev\BedrockEconomy\database\helper\AccountHolder;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\libSQL\query\SQLiteQuery;
use SQLite3;

final class MigrationQuery extends SQLiteQuery
{
    use AccountHolder;
    use TableHolder;

    public function __construct(protected string $_xuid, protected string $_username) {}

    public function onRun(SQLite3 $connection): void
    {
        $statement = $connection->prepare("UPDATE " . $this->table . " SET xuid = ?, username = ? WHERE xuid = ? OR username = ?");
        $statement->bindValue(1, $this->_xuid);
        $statement->bindValue(2, $this->_username);
        $statement->bindValue(3, $this->xuid);
        $statement->bindValue(4, $this->username);

        $result = $statement->execute();

        $this->setResult($connection->changes() > 0);

        $result->finalize();
        $statement->close();
    }
}
