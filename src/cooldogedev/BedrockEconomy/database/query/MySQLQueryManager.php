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

use cooldogedev\BedrockEconomy\database\query\player\mysql\MySQLBulkPlayersRetrievalQuery;
use cooldogedev\BedrockEconomy\database\query\player\mysql\MySQLPlayerCreationQuery;
use cooldogedev\BedrockEconomy\database\query\player\mysql\MySQLPlayerDeletionQuery;
use cooldogedev\BedrockEconomy\database\query\player\mysql\MySQLPlayerRetrievalQuery;
use cooldogedev\BedrockEconomy\database\query\player\mysql\MySQLPlayerSaveQuery;
use cooldogedev\BedrockEconomy\database\query\table\MySQLTableCreationQuery;
use cooldogedev\libSQL\query\SQLQuery;

final class MySQLQueryManager extends QueryManager
{
    public function getBulkPlayersRetrievalQuery(): SQLQuery
    {
        return new MySQLBulkPlayersRetrievalQuery();
    }

    public function getPlayerCreationQuery(string $xuid, string $username, int $balance): SQLQuery
    {
        return new MySQLPlayerCreationQuery($xuid, $username, $balance);
    }

    public function getPlayerDeletionQuery(string $xuid): SQLQuery
    {
        return new MySQLPlayerDeletionQuery($xuid);
    }

    public function getPlayerRetrievalQuery(string $xuid): SQLQuery
    {
        return new MySQLPlayerRetrievalQuery($xuid);
    }

    public function getPlayerSaveQuery(string $xuid, int $balance): SQLQuery
    {
        return new MySQLPlayerSaveQuery($xuid, $balance);
    }

    public function getTableCreationQuery(int $defaultBalance): SQLQuery
    {
        return new MySQLTableCreationQuery($defaultBalance);
    }
}
