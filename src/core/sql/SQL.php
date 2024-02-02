<?php

namespace core\sql;

use core\async\RequestAsync;
use core\settings\Settings;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class SQL implements Settings
{
    public static function query(string $query): \mysqli_result|bool
    {
        $db = self::connection();
        $result = $db->query($query);
        $db->close();
        return $result;
    }

    public static function connection(): \mysqli
    {
        $information = Settings::DB_SETTINGS;

        return new \mysqli(
            $information['hostname'],
            $information['username'],
            $information['password'],
            $information['database'],
            3306
        );
    }


    public static function grafanaConnection(): \mysqli
    {
        $information = Settings::DB_SETTINGS_GRAFANA;

        return new \mysqli(
            $information['hostname'],
            $information['username'],
            $information['password'],
            $information['database'],
            3306
        );
    }

    public static function informations(): array
    {
        return Settings::DB_SETTINGS;
    }


    public static function async(callable $async, ?callable $server = null): AsyncTask
    {
        $async = new RequestAsync($async, $server);
        Server::getInstance()->getAsyncPool()->submitTask($async);
        return $async;
    }
}