<?php

namespace core\listeners\types\player;

use core\listeners\BaseEvent;
use core\player\CustomPlayer;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\VanillaEnchantments;

class PlayerFall extends BaseEvent
{
    public function onFall(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof CustomPlayer) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                $protectionFall = 0;
                foreach ($entity->getArmorInventory()->getContents() as $slot => $item) {
                    if ($item->hasEnchantment(VanillaEnchantments::PROTECTION())) {
                        $lvl = $item->getEnchantment(VanillaEnchantments::PROTECTION())->getLevel();
                        $protectionFall += $lvl;
                    }
                }
                $event->setModifier(-$event->getFinalDamage() * ($protectionFall * 0.04), EntityDamageEvent::MODIFIER_ARMOR);
            }
        }
    }
}