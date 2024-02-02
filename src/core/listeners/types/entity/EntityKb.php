<?php

namespace core\listeners\types\entity;

use core\listeners\BaseEvent;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Sword;
use pocketmine\world\particle\BlockPunchParticle;

class EntityKb extends BaseEvent
{
    public array $cache = [];



    public function onKb(EntityDamageByEntityEvent $event): void {
        $event->setAttackCooldown(9);
        $event->setKnockBack(0.390);

        for ($i = 0; $i < 6; $i++) {
            $event->getEntity()->getWorld()->addParticle($event->getEntity()->getEyePos(), new BlockPunchParticle(VanillaBlocks::REDSTONE(), 0));
        }

        $damager = $event->getDamager();
        $entity = $event->getEntity();



        if ($damager instanceof CustomPlayer && $entity instanceof CustomPlayer) {

            if (($itemInHand = $damager->getInventory()->getItemInHand()) instanceof Sword or $itemInHand instanceof Axe) {
                if ($itemInHand->hasEnchantment(VanillaEnchantments::SHARPNESS())) {
                    $lvl = $itemInHand->getEnchantmentLevel(VanillaEnchantments::SHARPNESS());
                    $damageBonus = VanillaEnchantments::SHARPNESS()->getDamageBonus($lvl);
                    $event->setModifier($damageBonus , EntityDamageByEntityEvent::MODIFIER_WEAPON_ENCHANTMENTS);
                }
            }


        if (Main::getInstance()->getFactionManager()->isInFaction($damager->getXuid()) &&
            Main::getInstance()->getFactionManager()->isInFaction($entity->getXuid())) {
            if (Main::getInstance()->getFactionManager()->getFactionName($damager->getXuid()) ===
                Main::getInstance()->getFactionManager()->getFactionName($entity->getXuid())) {
                $event->cancel();
            } else {
                if (Main::getInstance()->getAreaManager()->isInArea($entity->getPosition())) {
                    if (Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($damager->getPosition())['pvp']) {
                        if (isset($this->cache[$entity->getXuid()])) {
                            if ($this->cache[$entity->getXuid()] <= time()) {
                                $entity->sendMessage(Messages::message("§cVous êtes en combat pendant 30 secondes."));
                                $this->cache[$entity->getXuid()] = time() + 30;
                            }
                        }else {
                            $entity->sendMessage(Messages::message("§cVous êtes en combat pendant 30 secondes."));
                            $this->cache[$entity->getXuid()] = time() + 30;
                        }

                        if (isset($this->cache[$damager->getXuid()])) {
                            if ($this->cache[$damager->getXuid()] <= time()) {
                                $damager->sendMessage(Messages::message("§cVous êtes en combat pendant 30 secondes."));
                                $this->cache[$damager->getXuid()] = time() + 30;
                            }
                        }else {
                            if ($this->cache[$entity->getXuid()] <= time()) {
                                $entity->sendMessage(Messages::message("§cVous êtes en combat pendant 30 secondes."));
                                $this->cache[$entity->getXuid()] = time() + 30;
                            }
                        }

                        $entity->setTagged(true);
                        $damager->setTagged(true);
                    }
                }
            }
        } else {
            if (Main::getInstance()->getAreaManager()->isInArea($entity->getPosition())) {
                if (Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($damager->getPosition())['pvp']) {
                    if (isset($this->cache[$entity->getXuid()])) {
                        if ($this->cache[$entity->getXuid()] <= time()) {
                            $entity->sendMessage(Messages::message("§cVous êtes en combat pendant 30 secondes."));
                            $this->cache[$entity->getXuid()] = time() + 30;
                        }
                    } else {
                        $entity->sendMessage(Messages::message("§cVous êtes en combat pendant 30 secondes."));
                        $this->cache[$entity->getXuid()] = time() + 30;
                    }

                    if (isset($this->cache[$damager->getXuid()])) {
                        if ($this->cache[$damager->getXuid()] <= time()) {
                            $damager->sendMessage(Messages::message("§cVous êtes en combat pendant 30 secondes."));
                            $this->cache[$damager->getXuid()] = time() + 30;
                        }
                    }else {
                        $entity->sendMessage(Messages::message("§cVous êtes en combat pendant 30 secondes."));
                        $this->cache[$entity->getXuid()] = time() + 30;
                    }

                    $entity->setTagged(true);
                    $damager->setTagged(true);
                }
            }
        }

        }
    }
}