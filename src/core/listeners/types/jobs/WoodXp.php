<?php

namespace core\listeners\types\jobs;

use core\items\tools\lumberjack\AbstractWoodenAxe;
use core\listeners\BaseEvent;
use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\jobs\Jobs;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Wood;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

class WoodXp extends BaseEvent
{
    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();

        if (!$event->isCancelled() && Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($event->getBlock()->getPosition())['break']) {
            $block = $event->getBlock();
            if (isset(Jobs::BUCHERON_XP[$block->getTypeId()])) {
                $pos = $block->getPosition();
                $str = $pos->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
                if(!array_key_exists($str,Main::$ANTI_FREE_XP)) {
                    $xp = Jobs::BUCHERON_XP[$block->getTypeId()];
                    if ($player->getInventory()->getItemInHand() instanceof AbstractWoodenAxe) {
                        if (mt_rand(0, 10) === 0) {
                            $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::LUMBERJACK, $xp * 2);
                        } else {
                            $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::LUMBERJACK, $xp);
                        }
                    } else {
                        $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::LUMBERJACK, $xp);
                    }
                    if ($bool) {
                        Main::getInstance()->getJobsManager()->xpNotif($player, $block->getPosition(), $xp, JobsManager::LUMBERJACK);
                    }
                }
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event): void{
        if (!$event->isCancelled()) {
            foreach ($event->getTransaction()->getBlocks() as [$x,$y,$z,$block]){
                $b = $block;
                break;
            }
            if (isset(Jobs::BUCHERON_XP[$b->getTypeId()]) || $b->getTypeId() === BlockTypeIds::MELON || $b->getTypeId() === BlockTypeIds::PUMPKIN || $b->getTypeId() === BlockTypeIds::SUGARCANE || $b->getTypeId() === BlockTypeIds::CACTUS) {
                $pos = $b->getPosition();
                $str = $pos->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
                if (!isset(Main::$ANTI_FREE_XP[$str])) {
                    Main::$ANTI_FREE_XP[$str] = true;
                }
            }
        }
    }
}