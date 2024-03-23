<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2024 cooldogedev
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @auto-license
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\database;

use cooldogedev\BedrockEconomy\database\mysql\BulkQuery as MySQLBulkQuery;
use cooldogedev\BedrockEconomy\database\mysql\InsertionQuery as MySQLInsertionQuery;
use cooldogedev\BedrockEconomy\database\mysql\MigrationQuery as MySQLMigrationQuery;
use cooldogedev\BedrockEconomy\database\mysql\RetrieveQuery as MySQLRetrieveQuery;
use cooldogedev\BedrockEconomy\database\mysql\TableQuery as MySQLTableQuery;
use cooldogedev\BedrockEconomy\database\mysql\TopQuery as MySQLTopQuery;
use cooldogedev\BedrockEconomy\database\mysql\TransferQuery as MySQLTransferQuery;
use cooldogedev\BedrockEconomy\database\mysql\UpdateQuery as MySQLUpdateQuery;
use cooldogedev\BedrockEconomy\database\sqlite\BulkQuery as SQLiteBulkQuery;
use cooldogedev\BedrockEconomy\database\sqlite\InsertionQuery as SQLiteInsertionQuery;
use cooldogedev\BedrockEconomy\database\sqlite\MigrationQuery as SQLiteMigrationQuery;
use cooldogedev\BedrockEconomy\database\sqlite\RetrieveQuery as SQLiteRetrieveQuery;
use cooldogedev\BedrockEconomy\database\sqlite\TableQuery as SQLiteTableQuery;
use cooldogedev\BedrockEconomy\database\sqlite\TopQuery as SQLiteTopQuery;
use cooldogedev\BedrockEconomy\database\sqlite\TransferQuery as SQLiteTransferQuery;
use cooldogedev\BedrockEconomy\database\sqlite\UpdateQuery as SQLiteUpdateQuery;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\query\SQLQuery;

final class QueryManager
{
    private static string $table;
    private static bool $isMySQL;

    public static function init(string $table, bool $isMySQL): void
    {
        QueryManager::$table = $table;
        QueryManager::$isMySQL = $isMySQL;
    }

    public static function BULK(array $list): SQLQuery
    {
        $query = QueryManager::$isMySQL ? new MySQLBulkQuery($list) : new SQLiteBulkQuery($list);
        $query->setTable(QueryManager::$table);

        return $query;
    }

    public static function INSERT(string $xuid, string $username, int $amount, int $decimals): SQLQuery
    {
        $query = QueryManager::$isMySQL ? new MySQLInsertionQuery($amount, $decimals) : new SQLiteInsertionQuery($amount, $decimals);
        $query->setTable(QueryManager::$table);
        $query->setUsername($username);
        $query->setXuid($xuid);

        return $query;
    }

    public static function MIGRATE(string $xuid, string $username, string $newXuid, string $newUsername): SQLQuery
    {
        $query = QueryManager::$isMySQL ? new MySQLMigrationQuery($newXuid, $newUsername) : new SQLiteMigrationQuery($newXuid, $newUsername);
        $query->setTable(QueryManager::$table);
        $query->setUsername($username);
        $query->setXuid($xuid);

        return $query;
    }

    public static function RETRIEVE(string $xuid, string $username): SQLQuery
    {
        $query = QueryManager::$isMySQL ? new MySQLRetrieveQuery() : new SQLiteRetrieveQuery();
        $query->setTable(QueryManager::$table);
        $query->setUsername($username);
        $query->setXuid($xuid);

        return $query;
    }

    public static function TABLE(): SQLQuery
    {
        $query = QueryManager::$isMySQL ? new MySQLTableQuery() : new SQLiteTableQuery();
        $query->setTable(QueryManager::$table);

        return $query;
    }

    public static function TOP(int $limit, int $offset, bool $ascending): SQLQuery
    {
        $query = QueryManager::$isMySQL ? new MySQLTopQuery($limit, $offset, $ascending) : new SQLiteTopQuery($limit, $offset, $ascending);
        $query->setTable(QueryManager::$table);

        return $query;
    }

    public static function TRANSFER(array $source, array $target, int $amount, int $decimals): SQLQuery
    {
        $query = QueryManager::$isMySQL ? new MySQLTransferQuery($amount, $decimals, $target["username"], $target["xuid"]) : new SQLiteTransferQuery($amount, $decimals, $target["username"], $target["xuid"]);
        $query->setTable(QueryManager::$table);
        $query->setUsername($source["username"]);
        $query->setXuid($source["xuid"]);

        return $query;
    }

    public static function UPDATE(string $xuid, string $username, int $mode, int $amount, int $decimals): SQLQuery
    {
        $query = QueryManager::$isMySQL ? new MySQLUpdateQuery($mode, $amount, $decimals) : new SQLiteUpdateQuery($mode, $amount, $decimals);
        $query->setTable(QueryManager::$table);
        $query->setUsername($username);
        $query->setXuid($xuid);

        return $query;
    }
}