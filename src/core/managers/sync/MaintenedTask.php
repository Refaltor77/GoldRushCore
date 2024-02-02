<?php

namespace core\managers\sync;

use core\Main;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class MaintenedTask extends Task
{
    public function onRun(): void
    {
        if (Server::getInstance()->isRunning()) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($player->isConnected() && $player->hasJobsLoaded) {
                    Main::getInstance()->getJobsManager()->safelySaveData($player);
                }
                if ($player->isConnected() && $player->hasHomeLoaded) {
                    Main::getInstance()->getHomeManager()->saveData($player, null, false);
                }
                if ($player->isConnected() && $player->hasInvLoaded) {
                    Main::getInstance()->getInventoryManager()->saveInventory($player, true, false);
                }
            }
        }
    }
}