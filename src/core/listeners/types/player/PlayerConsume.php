<?php

namespace core\listeners\types\player;

use core\items\foods\alcools\BottleJobs;
use core\listeners\BaseEvent;
use core\Main;
use core\messages\Messages;
use pocketmine\event\player\PlayerItemConsumeEvent;

class PlayerConsume extends BaseEvent
{
    public function onConsume(PlayerItemConsumeEvent $event): void {
        $player = $event->getPlayer();
        $foodItem = $event->getItem();

        if ($foodItem instanceof BottleJobs) {
            if (Main::getInstance()->getXpManager()->hasDobble($player)) {
                $player->sendErrorSound();
                $player->sendMessage(Messages::message("§cVous êtes encore en x2 xp."));
                $event->cancel();
                return;
            } else {
                Main::getInstance()->getXpManager()->setDobble($player);
            }
        }
    }
}