<?php

namespace core\thread;

use core\IpSettings;
use core\settings\Settings;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\thread\Thread;
use pmmp\thread\ThreadSafeArray;

class DataSyncThread extends Thread
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

            $query = $db->query("SELECT * FROM data_sync_faction;");
            $db->query("DELETE FROM data_sync_faction;");
            $all = $query->fetch_all(MYSQLI_ASSOC);
            foreach ($all as $index => $rowData) {
                $this->data = unserialize(base64_decode($rowData['data']));
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
