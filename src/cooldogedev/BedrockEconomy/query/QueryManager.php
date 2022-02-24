<?php

/**
 *  Copyright (c) 2022 cooldogedev
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\query;

use cooldogedev\BedrockEconomy\query\mysql\MySQLTableCreationQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLBulkRetrieveQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLCreationQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLDeletionQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLRetrieveQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLTransferQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLUpdateQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLiteBulkRetrieveQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLiteCreationQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLiteDeletionQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLiteRetrievalQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLiteTransferQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLiteUpdateQuery;
use cooldogedev\BedrockEconomy\query\sqlite\SQLiteTableCreationQuery;
use cooldogedev\BedrockEconomy\transaction\types\TransferTransaction;
use cooldogedev\BedrockEconomy\transaction\types\UpdateTransaction;
use cooldogedev\libSQL\query\SQLQuery;

final class QueryManager
{
    public const DATA_TABLE_PLAYERS = "bedrock_economy";

    protected static bool $isMySQL;

    public static function getBulkPlayersQuery(?int $limit = null, ?int $offset = null): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLBulkRetrieveQuery::class : SQLiteBulkRetrieveQuery::class;

        return new $class($limit, $offset);
    }

    public static function isMySQL(): bool
    {
        return QueryManager::$isMySQL;
    }

    public static function setIsMySQL(bool $isMySQL): void
    {
        QueryManager::$isMySQL = $isMySQL;
    }

    public static function getPlayerCreationQuery(string $username, int $balance): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLCreationQuery::class : SQLiteCreationQuery::class;

        return new $class($username, $balance);
    }

    public static function getPlayerDeletionQuery(string $username): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLDeletionQuery::class : SQLiteDeletionQuery::class;

        return new $class($username);
    }

    public static function getPlayerQuery(string $username): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLRetrieveQuery::class : SQLiteRetrievalQuery::class;

        return new $class($username);
    }

    public static function getPlayerUpdateQuery(UpdateTransaction $transaction): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLUpdateQuery::class : SQLiteUpdateQuery::class;

        return new $class($transaction);
    }

    public static function getPlayerTransferQuery(TransferTransaction $transaction): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLTransferQuery::class : SQLiteTransferQuery::class;

        return new $class($transaction);
    }

    public static function getTableCreationQuery(int $defaultBalance): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLTableCreationQuery::class : SQLiteTableCreationQuery::class;

        return new $class($defaultBalance);
    }
}
