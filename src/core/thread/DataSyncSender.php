<?php

namespace core\thread;

use core\IpSettings;
use core\settings\Settings;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\thread\Thread;
use pmmp\thread\ThreadSafeArray;

class DataSyncSender extends Thread
{
    public ThreadSafeArray $data;
    public bool $shutdown = false;
    public ?\Socket $socket = null;

    public function __construct()
    {
        $this->data = new ThreadSafeArray();
    }

    protected function onRun(): void
    {
        $dbSettings = Settings::DB_SETTINGS;


        while (true) {
            $db  = new \mysqli(
                $dbSettings['hostname'],
                $dbSettings['username'],
                $dbSettings['password'],
                $dbSettings['database'],
            );
            if ($this->shutdown) {
                break;
            }

            foreach ($this->data as $dataRow) {
                $dataRow = base64_encode(serialize($dataRow));
                $db->query("INSERT INTO data_sync_minage (data) VALUES ('$dataRow');");
                $this->data->shift();
            }

            sleep(1);
            $db->close();
        }
    }

    public function shutdown(): void
    {
        $this->synchronized(function (): void {
            $this->shutdown = true;
            $this->notify();
        });
        $this->join();
    }
}
