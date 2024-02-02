<?php

namespace core\managers\sync;

use core\Main;
use core\player\CustomPlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class SyncTask extends Task
{
    private SyncDatabaseManager $syncManager;

    public function __construct(SyncDatabaseManager $syncManager)
    {
        $this->syncManager = $syncManager;
    }

    public function onRun(): void
    {
        if (!Server::getInstance()->isRunning()) return;


        $manager = $this->syncManager;
        $plugin = Main::getInstance();

        $inventoryQueue = $manager->getQueueInventoryPlayers();


        foreach ($inventoryQueue as $player) {
            if ($player instanceof CustomPlayer) {
                if ($player->isConnected()) {
                    $plugin->getInventoryManager()->saveInventory($player, true, false);
                    unset($inventoryQueue[array_search($player, $inventoryQueue)]);
                }
            }
        }
        $manager->setQueueInventoryPlayers($inventoryQueue);
    }
}