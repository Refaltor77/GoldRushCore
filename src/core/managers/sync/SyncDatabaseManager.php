<?php

namespace core\managers\sync;

use core\Main;
use core\managers\Manager;
use core\player\CustomPlayer;

class SyncDatabaseManager extends Manager
{
    private array $queueJobsPlayers = [];
    private array $queueInventoryPlayers = [];
    private array $queueRanksPlayers = [];

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
        $this->start();
    }

    public function addPlayerQueue(CustomPlayer $player, string $type): void {

    }

    public function start(): void {
        $this->getPlugin()->getScheduler()->scheduleRepeatingTask(new MaintenedTask(), 20 * 60);
    }

    public function stop(): void {

    }


    public function getQueueJobsPlayers(): array{return $this->queueJobsPlayers;}
    public function getQueueInventoryPlayers(): array{return $this->queueInventoryPlayers;}
    public function getQueueRanksPlayers(): array{return $this->queueRanksPlayers;}


    public function setQueueJobsPlayers(array $queueJobsPlayers): void {

    }

    public function setQueueInventoryPlayers(array $queueInventoryPlayers): void {

    }

    public function setQueueRanksPlayers(array $queueRanksPlayers): void {

    }
}