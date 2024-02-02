<?php

namespace core\listeners\types\player;

use core\listeners\BaseEvent;
use core\Main;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\scheduler\ClosureTask;

class PlayerDropsItem extends BaseEvent
{
    public function onDrops(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();
        if ($player->isInCinematic) {
            $event->cancel();
        }

        if ($player->hasFreeze()) {
            $event->cancel();
        }

        if ($player->hasNoClientPredictions()) $event->cancel();


        if (Main::getInstance()->getStaffManager()->isInStaffMode($player)) {
            $event->cancel();
        }

        if (Main::getInstance()->getExchangeManager()->hasSaveInventory($player)) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player) : void {
                if (Main::getInstance()->getExchangeManager()->hasSaveInventory($player)) {
                    $itemEntity = $player->getWorld()->getNearestEntity($player->getPosition(), 5, ItemEntity::class);
                    if (is_null($itemEntity)) return;

                    $salle = Main::getInstance()->getExchangeManager()->getSallePlayer($player);
                    if ($salle !== 0) {
                        Main::getInstance()->getExchangeManager()->addDrops($itemEntity, $salle);
                    }
                }
            }), 3);
        }
    }
}