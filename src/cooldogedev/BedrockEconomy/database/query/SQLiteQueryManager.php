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

namespace cooldogedev\BedrockEconomy\database\query;

use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLiteBulkPlayersRetrievalQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerCreationQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerDeletionQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerRetrievalQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerSaveQuery;
use cooldogedev\BedrockEconomy\database\query\table\SQLiteTableCreationQuery;
use cooldogedev\libSQL\query\SQLQuery;

final class SQLiteQueryManager extends QueryManager
{
    public function getBulkPlayersRetrievalQuery(): SQLQuery
    {
        return new SQLiteBulkPlayersRetrievalQuery();
    }

    public function getPlayerCreationQuery(string $xuid, string $username, int $balance): SQLQuery
    {
        return new SQLitePlayerCreationQuery($xuid, $username, $balance);
    }

    public function getPlayerDeletionQuery(string $xuid): SQLQuery
    {
        return new SQLitePlayerDeletionQuery($xuid);
    }

    public function getPlayerRetrievalQuery(string $xuid): SQLQuery
    {
        return new SQLitePlayerRetrievalQuery($xuid);
    }

    public function getPlayerSaveQuery(string $xuid, int $balance): SQLQuery
    {
        return new SQLitePlayerSaveQuery($xuid, $balance);
    }

    public function getTableCreationQuery(int $defaultBalance): SQLQuery
    {
        return new SQLiteTableCreationQuery($defaultBalance);
    }
}
