<?php

namespace core\blocks\crops\vanilla;

use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\jobs\Jobs;
use pocketmine\block\Cactus;
use pocketmine\block\Carrot;
use pocketmine\block\Sugarcane;
use pocketmine\item\Item;
use pocketmine\player\Player;

class Sugarcanne extends Sugarcane
{
    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if (is_null($player)) {
            $player = $this->getPosition()->getWorld()->getNearestEntity($this->getPosition(), 7, CustomPlayer::class);
        }

        if ($player instanceof CustomPlayer) {
            $pos = $this->getPosition();
            $str = $pos->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
            if(!array_key_exists($str,Main::$ANTI_FREE_XP)) {
                $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::FARMER, Jobs::FARMER_XP[$this->getTypeId()]);
                if ($bool) Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), Jobs::FARMER_XP[$this->getTypeId()], JobsManager::FARMER);
            }
        }
        return parent::onBreak($item, $player, $returnedItems);
    }
}