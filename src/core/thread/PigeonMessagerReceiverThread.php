<?php

namespace core\thread;

use core\IpSettings;
use core\settings\Settings;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\thread\Thread;
use pmmp\thread\ThreadSafeArray;

class PigeonMessagerReceiverThread extends Thread
{
    public ThreadSafeArray $messagesEntry;
    public bool $shutdown = false;
    public ?\Socket $socket = null;

    public function __construct()
    {
        $this->messagesEntry = new ThreadSafeArray();
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

            $query = $db->query("SELECT * FROM messages_faction_receiver;");
            $db->query("DELETE FROM messages_faction_receiver;");
            $fetchAll = $query->fetch_all(MYSQLI_ASSOC);
            foreach ($fetchAll as $row => $values) {
                $this->messagesEntry[] = $values['message'];
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
