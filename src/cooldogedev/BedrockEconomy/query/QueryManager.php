<?php

/**
 *  Copyright (c) 2021 cooldogedev
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
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLBulkPlayersRetrievalQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLPlayerCreationQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLPlayerDeletionQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLPlayerRetrievalQuery;
use cooldogedev\BedrockEconomy\query\mysql\player\MySQLPlayerUpdateQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLiteBulkPlayersRetrievalQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLitePlayerCreationQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLitePlayerDeletionQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLitePlayerRetrievalQuery;
use cooldogedev\BedrockEconomy\query\sqlite\player\SQLitePlayerUpdateQuery;
use cooldogedev\BedrockEconomy\query\sqlite\SQLiteTableCreationQuery;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\libSQL\query\SQLQuery;

final class QueryManager
{
    public const DATA_TABLE_PLAYERS = "bedrock_economy";

    protected static bool $isMySQL;

    public static function getBulkPlayersQuery(?int $limit = null, ?int $offset = null): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLBulkPlayersRetrievalQuery::class : SQLiteBulkPlayersRetrievalQuery::class;

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
        $class = QueryManager::isMySQL() ? MySQLPlayerCreationQuery::class : SQLitePlayerCreationQuery::class;

        return new $class($username, $balance);
    }

    public static function getPlayerDeletionQuery(string $username): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLPlayerDeletionQuery::class : SQLitePlayerDeletionQuery::class;

        return new $class($username);
    }

    public static function getPlayerQuery(string $username): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLPlayerRetrievalQuery::class : SQLitePlayerRetrievalQuery::class;

        return new $class($username);
    }

    public static function getPlayerUpdateQuery(string $username, Transaction $transaction): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLPlayerUpdateQuery::class : SQLitePlayerUpdateQuery::class;

        return new $class($username, $transaction);
    }

    public static function getTableCreationQuery(int $defaultBalance): SQLQuery
    {
        $class = QueryManager::isMySQL() ? MySQLTableCreationQuery::class : SQLiteTableCreationQuery::class;

        return new $class($defaultBalance);
    }
}
