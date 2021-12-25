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

use cooldogedev\BedrockEconomy\constant\SearchConstants;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLiteBulkPlayersRetrievalQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerCreationQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerDeletionQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerFixQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerRetrievalQuery;
use cooldogedev\BedrockEconomy\database\query\player\sqlite\SQLitePlayerUpdateQuery;
use cooldogedev\BedrockEconomy\database\query\table\SQLiteTableCreationQuery;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\libSQL\query\SQLQuery;

final class SQLiteQueryManager extends QueryManager
{
    public function getBulkPlayersRetrievalQuery(?int $limit = null, ?int $offset = null): SQLQuery
    {
        return new SQLiteBulkPlayersRetrievalQuery($limit, $offset);
    }

    public function getPlayerCreationQuery(string $xuid, string $username, int $balance): SQLQuery
    {
        return new SQLitePlayerCreationQuery($xuid, strtolower($username), $balance);
    }

    public function getPlayerFixQuery(string $xuid, string $username): SQLQuery
    {
        return new SQLitePlayerFixQuery($xuid, strtolower($username));
    }

    public function getPlayerDeletionQuery(string $xuid): SQLQuery
    {
        return new SQLitePlayerDeletionQuery($xuid);
    }

    public function getPlayerRetrievalQuery(string $searchValue, int $searchMode = SearchConstants::SEARCH_MODE_XUID): SQLQuery
    {
        return new SQLitePlayerRetrievalQuery(strtolower($searchValue), $searchMode);
    }

    public function getPlayerSaveQuery(string $searchValue, Transaction $transaction, int $searchMode = SearchConstants::SEARCH_MODE_XUID): SQLQuery
    {
        return new SQLitePlayerUpdateQuery(strtolower($searchValue), $transaction, $searchMode);
    }

    public function getTableCreationQuery(int $defaultBalance): SQLQuery
    {
        return new SQLiteTableCreationQuery($defaultBalance);
    }
}
