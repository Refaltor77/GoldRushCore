<?php

namespace core\thread;

use core\api\timings\TimingsSystem;
use core\Main;
use core\sql\SQL;
use core\utils\Utils;
use pocketmine\Server;
use Socket;
use pmmp\thread\ThreadSafeArray;

class ThreadManager
{
    public LoggerAttachable $worker;
    public PigeonMessagerThread $messagerThread;
    public PigeonMessagerReceiverThread $receiverThread;
    public DataSyncThread $syncThread;
    public DataSyncSender $syncSenderThread;

    public array $msgBlacklist = [];

    public function __construct()
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS messages_faction_receiver (id INT PRIMARY KEY AUTO_INCREMENT, message TEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS messages_minage_receiver (id INT PRIMARY KEY AUTO_INCREMENT, message TEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS data_sync_faction (id INT PRIMARY KEY AUTO_INCREMENT, data LONGTEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS data_sync_minage (id INT PRIMARY KEY AUTO_INCREMENT, data LONGTEXT);");
        $db->close();
        $this->worker = new LoggerAttachable(Main::getInstance()->getDataFolder());
        $this->messagerThread = new PigeonMessagerThread();
        $this->receiverThread = new PigeonMessagerReceiverThread();
        $this->syncThread = new DataSyncThread();
        $this->syncSenderThread = new DataSyncSender();

        $this->messagerThread->start();
        $this->receiverThread->start();
        $this->syncThread->start();
        $this->syncSenderThread->start();

        $this->createTimingPigeonVoyager();
    }

    public function createTimingPigeonVoyager(): void
    {
        $timings = new TimingsSystem();
        $timings->createTiming(function (TimingsSystem $timingsSystem, int $seconds): void {
            if ($seconds >= PHP_INT_MAX - 10) {
                $this->createTimingPigeonVoyager();
                $timingsSystem->stopTiming();
            }
            while ($msg = $this->receiverThread->messagesEntry->shift()) {
                Server::getInstance()->broadcastMessage($msg);
            }
        }, 20);
    }


    public function createTimingDataSync(): void {
        $timings = new TimingsSystem();
        $timings->createTiming(function (TimingsSystem $timingsSystem, int $seconds): void {
            if ($seconds >= PHP_INT_MAX - 10) {
                $this->createTimingDataSync();
                $timingsSystem->stopTiming();
            }
            while ($data = $this->syncThread->data->shift()) {
                if ($data['port'] !== Server::getInstance()->getPort()) {
                    switch ($data['type']) {
                        case 'voteparty':
                            Main::getInstance()->getVotePartyManager()->add();
                            break;
                    }
                }
            }
        });
    }


    public function syncVoteParty(): void {

        $data = new ThreadSafeArray();
        $data['port'] = Server::getInstance()->getPort();
        $data['type'] = 'voteparty';

        $this->syncSenderThread->data[] = $data;
    }

    public function stop(){
        $this->worker->__destruct();
        $this->messagerThread->shutdown();
        $this->receiverThread->shutdown();
        $this->syncThread->shutdown();
        $this->syncSenderThread->shutdown();
    }
}