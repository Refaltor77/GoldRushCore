<?php

namespace core\listeners\types\player;

use core\entities\BossSouls;
use core\entities\Peste;
use core\entities\TrollBoss;
use core\items\horse\HorseArmorAmethyst;
use core\items\horse\HorseArmorCopper;
use core\items\horse\HorseArmorEmerald;
use core\items\horse\HorseArmorGold;
use core\items\horse\HorseArmorPlatinum;
use core\listeners\BaseEvent;
use core\Main;
use core\managers\jobs\JobsManager;
use core\managers\stats\StatsManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\jobs\Jobs;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\network\mcpe\protocol\PlayerFogPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;

class PlayerDeath extends BaseEvent
{

    public static array $lastDeath = [];
    public static array $assists = [];



    public function onTape(EntityDamageByEntityEvent $event): void {
        $player = $event->getEntity();
        $damager = $event->getDamager();

        if ($player instanceof CustomPlayer && $damager instanceof CustomPlayer) {
            self::$assists[$player->getXuid()] = $damager->getXuid();
        }
    }


    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();



        if (in_array($player, Peste::$isInBoss)) {
            unset(Peste::$isInBoss[array_search($player, Peste::$isInBoss)]);
        }

        if (in_array($player, TrollBoss::$isInBoss)) {
            unset(Peste::$isInBoss[array_search($player, TrollBoss::$isInBoss)]);
        }

        if (in_array($player, BossSouls::$playersInBoss)) {
            unset(Peste::$isInBoss[array_search($player, BossSouls::$playersInBoss)]);
        }

        $pk = StopSoundPacket::create("", true);
        $player->getNetworkSession()->sendDataPacket($pk);

        $pk = PlayerFogPacket::create([
            "minecraft:fog_default"
        ]);
        $player->getNetworkSession()->sendDataPacket($pk);



        Main::getInstance()->getPrimeManager()->resetCombo($player);
        $player->hasTagged = false;
        $player->taggedTime = 0;

        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof CustomPlayer) {
                if (!isset(self::$lastDeath[$player->getXuid()]) || self::$lastDeath[$player->getXuid()] !== $damager->getXuid()) {
                    Main::getInstance()->getJobsManager()->addXp($damager, JobsManager::HUNTER, Jobs::ASSASSIN_XP['kill']);
                    Main::getInstance()->getFactionManager()->addPower("", 5, $damager);
                    Main::getInstance()->getPrimeManager()->addKill($damager);
                    Main::getInstance()->getStatsManager()->addValue($damager->getXuid(), StatsManager::KILL);
                }

                if (isset(self::$assists[$player->getXuid()])) {
                    $xuidPlayerAssist = self::$assists[$player->getXuid()];
                    $playerAssist = Main::getInstance()->getDataManager()->getPlayerXuid($xuidPlayerAssist);
                    if ($playerAssist instanceof CustomPlayer) {
                        Main::getInstance()->getJobsManager()->addXp($playerAssist, JobsManager::HUNTER, Jobs::ASSASSIN_XP['assist']);
                        $playerAssist->sendMessage(Messages::message("§fu as assisté à la mort du joueur §6" . $player->getName()));
                        Main::getInstance()->getFactionManager()->addPower("", 2, $playerAssist);
                    }
                }

                self::$lastDeath[$player->getXuid()] = $damager->getXuid();

                if ($damager->getInventory()->getItemInHand()->hasCustomName()) {
                    $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort tué par §f" . $damager->getInventory()->getItemInHand()->getCustomName());
                } else $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort tué par §f" . $damager->getName());
            }
            if ($damager instanceof BossSouls)  {
                $event->setDeathScreenMessage("Désormais, ton âme appartient au vengeur des âmes");
                $event->setDeathMessage("§c§l[§rMort§l§c]§r§7 Le §dvengeur des âmes§r§7 a pris l'âme de §f" . $player->getName());


                if (isset($damager->hasSendIdle[$player->getXuid()])) {
                    unset($damager->hasSendIdle[$player->getXuid()]);
                }
                if (isset($damager->hasSendWalk[$player->getXuid()])) {
                    unset($damager->hasSendWalk[$player->getXuid()]);
                }
                if (isset($damager->hasSendMusic[$player->getXuid()])) {
                    unset($damager->hasSendMusic[$player->getXuid()]);
                }
            }
        }

        switch ($cause?->getCause()) {
            case EntityDamageEvent::CAUSE_FALL:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort d'une chute fatal");
                break;
            case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
            case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort d'une explosion");
                break;
            case EntityDamageEvent::CAUSE_CUSTOM:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort d'une manière étrange");
                break;
            case EntityDamageEvent::CAUSE_LAVA:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort dans la lave");
                break;
            case EntityDamageEvent::CAUSE_FIRE:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort dans le feu comme Jeanne d'Arc");
                break;
            case EntityDamageEvent::CAUSE_SUICIDE:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7a mis fin à ses jours par suicide");
                break;
            case EntityDamageEvent::CAUSE_DROWNING:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort noyé");
                break;
            case EntityDamageEvent::CAUSE_MAGIC:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort par une sorcellerie");
                break;
            case EntityDamageEvent::CAUSE_PROJECTILE:
                $event->setDeathMessage("§c§l[§r§7Mort§l§c]§r§f " . $player->getName() . " §7est mort par un projectile");
                break;
        }
    }
}