<?php


namespace core\tasks;

use core\entities\ItemEntitySafe;
use core\entities\OrbEntity;
use core\Main;
use core\traits\UtilsTrait;
use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ClearlaggTask extends Task
{

    public int $time = 10;
    public static int $timee = 0;
    public array $prevention = [30, 20, 10, 5, 4, 3, 2, 1];

    public bool $cancel;
    public int $countItem = 0;


    use UtilsTrait;

    public function __construct($cancel = false)
    {
        $this->cancel = $cancel;
        $this->time = 600;
    }

    public function onRun(): void
    {
        self::$timee = $this->time;

        $ramTime = $this->time;
        if ($ramTime <= 0) {
            $this->countItem = 0;
            $worlds = Server::getInstance()->getWorldManager()->getWorlds();
            foreach ($worlds as $world) {
                foreach ($world->getEntities() as $entity) {
                    if ($entity instanceof ItemEntity || $entity instanceof OrbEntity) {
                        if (!$entity instanceof ItemEntitySafe) {
                            $close = true;
                            foreach (Main::getInstance()->getExchangeManager()->droppedItem as $index => $array) {
                                foreach ($array as $droppedItem) {
                                    if ($droppedItem instanceof ItemEntity ){
                                        if ($droppedItem->getId() === $entity->getId()) {
                                            $close = false;
                                        }
                                    }
                                }
                            }
                            if ($close) {
                                if (!$entity->isFlaggedForDespawn()) $entity->flagForDespawn();
                                $this->countItem++;
                            }
                        }
                    }
                }
            }
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendTip("§l§6» §r§c{$this->countItem}§f item(s) ont été clear(s)§6§l «");
            }
        } elseif (array_search($ramTime, $this->prevention) !== false) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendTip("§l§6» §r§fClearlag dans §c{$ramTime}§f seconde(s)§6§l «");
            }
        }
        $this->time = $ramTime <= 0 ? 600 : $ramTime - 1;
    }
}