<?php

namespace core\listeners\types\crafts;

use core\items\armors\others\HoodHelmet;
use core\listeners\BaseEvent;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\event\inventory\CraftItemEvent;

class CraftsBlock extends BaseEvent
{
    public function onCraft(CraftItemEvent $event): void {
        $player = $event->getPlayer();
        if (!$player instanceof CustomPlayer) return;
        $manager = $this->getPlugin()->getCraftManager();

        $items = $event->getOutputs();
        foreach ($items as $item) {
            if ($item instanceof HoodHelmet) return;
            if (method_exists(get_class($item), "getTextureString")) {
                if (in_array($item->getTextureString(), Main::getInstance()->getCraftManager()->blocked_items)) {
                    if (!$manager->isCraftUnlocked($player, $item->getTextureString())) {
                        $player->sendMessage(Messages::message("§cVous n'avez pas débloqué le craft !"));
                        $event->cancel();
                    }
                }
            } else {
                if (in_array($item->getName(), Main::getInstance()->getCraftManager()->blocked_items)) {
                    if (!$manager->isCraftUnlocked($player, $item->getName())) {
                        $player->sendMessage(Messages::message("§cVous n'avez pas débloqué le craft !"));
                        $event->cancel();
                    }
                }
            }
        }
    }
}