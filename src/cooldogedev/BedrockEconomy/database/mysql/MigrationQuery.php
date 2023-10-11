<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database\mysql;

use cooldogedev\BedrockEconomy\database\helper\AccountHolder;
use cooldogedev\BedrockEconomy\database\helper\TableHolder;
use cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class MigrationQuery extends MySQLQuery
{
    use AccountHolder;
    use TableHolder;

    public function __construct(protected string $_xuid, protected string $_username) {}

    public function onRun(mysqli $connection): void
    {
        $statement = $connection->prepare("UPDATE " . $this->table . " SET xuid = ?, username = ? WHERE xuid = ? OR username = ?");
        $statement->bind_param("ss", $this->_xuid, $this->_username, $this->xuid, $this->username);
        $statement->execute();

        $this->setResult($connection->affected_rows > 0);

        $statement->close();
    }
}
