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

/*
 * EconomyAPI's supported providers.
 *
 * If you're using the YAML provider then place your players data file in the script's directory.
 */
const ECONOMYAPI_PROVIDER_YAML = 0;
const ECONOMYAPI_PROVIDER_MYSQL = 1;

/*
 * BedrockEconomy's supported providers, this is the output provider.
 */
const MIGRATION_TYPE_MYSQL = 0;
const MIGRATION_TYPE_SQLITE = 1;

/*
 * MySQL credentials, required if you use EconomyAPI's mysql provider or trying to output to mysql.
 */
$hostname = "135.148.27.160";
$username = "dev";
$schema = "development";
$password = "test$69666";
$port = 3306;

function migrateToMySQL(array $accounts): void {
    $connection = new mysqli($GLOBALS["hostname"], $GLOBALS["username"], $GLOBALS["password"], $GLOBALS["schema"], $GLOBALS["port"]);

    foreach ($accounts as $username => $balance) {
        $statement = $connection->prepare("INSERT IGNORE INTO bedrock_economy (xuid, username, balance) VALUES (?, ?, ?)");
        // EconomyAPI doesn't have xuids so we set the xuid to the username, when they login BedrockEconomy will fix it
        $statement->bind_param("sss", $username, $username, $balance);
        $statement->execute();
        $statement->close();
    }
    $connection->close();
}

function migrateToSQLite(array $accounts): void {
    $connection = new SQLite3(__DIR__ . DIRECTORY_SEPARATOR . "players.db");

    foreach ($accounts as $username => $balance) {
        $statement = $connection->prepare("INSERT OR IGNORE INTO bedrock_economy (xuid, username, balance) VALUES (:xuid, :username, :balance)");
        // EconomyAPI doesn't have xuids so we set the xuid to the username, when they login BedrockEconomy will fix it
        $statement->bindValue(":xuid", $username);
        $statement->bindValue(":username", $username);
        $statement->bindValue(":balance", $balance);
        $statement->execute();
        $statement->close();
    }
    $connection->close();
}

/**
 * @throws Exception
 */
function parseFromEconomyAPI(int $economyAPIProvider): ?array {
    switch ($economyAPIProvider) {
        case ECONOMYAPI_PROVIDER_YAML:
            return yaml_parse_file(__DIR__ . DIRECTORY_SEPARATOR . "Money.yml")["money"];
        case ECONOMYAPI_PROVIDER_MYSQL:
            $connection = new mysqli($GLOBALS["hostname"], $GLOBALS["username"], $GLOBALS["password"], $GLOBALS["schema"], $GLOBALS["port"]);
            $statement = $connection->query("SELECT * from user_money");

            $data = [];

            foreach($statement->fetch_all() as $value){
                $data[$value[0]] = $value[1];
            }

            $statement->free();

            return $data;
        default:
            throw new Exception("An unsupported EconomyAPI provider was provided");
    }
}

/**
 * @throws Exception
 */
function main(): void {
    // EDIT THIS ACCORDING TO YOUR ECONOMYAPI CONFIGURATIONS
    $economyAPIProvider = ECONOMYAPI_PROVIDER_MYSQL;

    // EDIT THIS ACCORDING TO YOUR BEDROCKECONOMY CONFIGURATIONS
    $migrationType = MIGRATION_TYPE_MYSQL;

    $parsedData = parseFromEconomyAPI($economyAPIProvider);

    switch ($migrationType) {
        case MIGRATION_TYPE_MYSQL:
            migrateToMySQL($parsedData);
            break;
        case MIGRATION_TYPE_SQLITE:
            migrateToSQLite($parsedData);
            break;
        default:
            throw new Exception("An unsupported BedrockEconomy provider was provided");
    }
}

main();
