<?php

namespace core\listeners\types\staff;

use core\cooldown\BasicCooldown;
use core\listeners\BaseEvent;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\event\player\PlayerMoveEvent;

class FreezeListeners extends BaseEvent
{
    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        if ($player instanceof CustomPlayer) {
            if ($player->hasFreeze()) {
                $event->cancel();
                if (BasicCooldown::validCustom($player, 20 * 2 )) {
                    $player->sendMessage(Messages::message("§cUn modérateur vous a gelé."));
                }
            }
        }
    }
}