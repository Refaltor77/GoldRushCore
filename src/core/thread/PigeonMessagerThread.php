<?php

namespace core\thread;

use core\IpSettings;
use core\settings\Settings;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\thread\Thread;
use pmmp\thread\ThreadSafeArray;


class PigeonMessagerThread extends Thread
{
    public ThreadSafeArray $messages;
    public bool $shutdown = false;

    public function __construct()
    {
        $this->messages = new ThreadSafeArray();
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

            foreach ($this->messages as $index => $line) {
                $line = $db->real_escape_string($line);
                $db->query("INSERT INTO messages_minage_receiver (message) VALUES ('$line');");
                $this->messages->shift();
            }

            sleep(1);
            $db->close();
        }
    }


    public function write(string $line) : void{
        $this->synchronized(function() use ($line) : void{
            $this->messages[] = $line;
            $this->notify();
        });
    }

    public function shutdown() : void{
        $this->synchronized(function() : void{
            $this->shutdown = true;
            $this->notify();
        });
        $this->join();
    }
}