<?php

namespace core\blocks\ores\vanilla;

use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\jobs\Jobs;
use pocketmine\block\Coal;
use pocketmine\block\IronOre;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class IronCustom extends Opaque
{
    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            Main::getInstance()->getTopLuckManager()->addOre($player);
            $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::MINOR, Jobs::MINER_XP[$this->getTypeId()]);
            if ($bool )Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), Jobs::MINER_XP[$this->getTypeId()], JobsManager::MINOR);
        }
        return parent::onBreak($item, $player, $returnedItems);
    }


    public function getDropsForCompatibleTool(Item $item) : array{
        return [VanillaItems::RAW_IRON()];
    }

    public function isAffectedBySilkTouch() : bool{ return false; }
}