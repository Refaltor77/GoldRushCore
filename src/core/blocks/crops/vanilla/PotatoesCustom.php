<?php

namespace core\blocks\crops\vanilla;

use core\items\tools\FarmTools;
use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\jobs\Jobs;
use pocketmine\block\Potato;
use pocketmine\block\Wheat;
use pocketmine\item\Item;
use pocketmine\player\Player;

class PotatoesCustom extends Potato
{
    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if (is_null($player)) {
            $player = $this->getPosition()->getWorld()->getNearestEntity($this->getPosition(), 7, CustomPlayer::class);
        }
        if ($player instanceof CustomPlayer && $this->getAge() === self::MAX_AGE) {
            $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::FARMER, Jobs::FARMER_XP[$this->getTypeId()]);
            if ($bool)Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), Jobs::FARMER_XP[$this->getTypeId()], JobsManager::FARMER);
        }
        return parent::onBreak($item, $player, $returnedItems);
    }

    public function getDrops(Item $item): array
    {
        $player = $this->getPosition()->getWorld()->getNearestEntity($this->getPosition(), 7, CustomPlayer::class);
        if ($player instanceof Player && $player->getInventory()->getItemInHand() instanceof FarmTools && $this->getAge() < self::MAX_AGE) return [];
        return parent::getDrops($item);
    }
}