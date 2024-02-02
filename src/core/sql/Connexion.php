<?php

namespace core\sql;

use core\async\Async;
use core\async\RequestAsync;
use core\settings\Settings;
use core\settings\traits\SettingsTrait;
use pocketmine\Server;

class Connexion implements Settings
{
    use SettingsTrait;


    public function processRequestSQL(callable $async, ?callable $result = null): void
    {
        $task = new RequestAsync($async, $result);
        Server::getInstance()->getAsyncPool()->submitTask($task);
    }

    public function processAsync(callable $async, ?callable $result = null): void
    {
        $task = new Async($async, $result);
        Server::getInstance()->getAsyncPool()->submitTask($task);
    }

    public function connect(): \mysqli
    {
        $information = $this->getDatabaseInformation();

        return new \mysqli(
            $information['hostname'],
            $information['username'],
            $information['password'],
            $information['database']
        );
    }
}