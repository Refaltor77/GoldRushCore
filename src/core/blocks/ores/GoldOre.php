<?php

namespace core\blocks\ores;

use core\Main;
use core\managers\jobs\JobsManager;
use core\managers\stats\StatsManager;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\player\Player;

class GoldOre extends Opaque
{

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER)
        ];
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            Main::getInstance()->getTopLuckManager()->addOre($player);
            Main::getInstance()->getStatsManager()->addValue($player->getXuid(), StatsManager::GOLD_MINED);
            $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::MINOR, 100);
            if ($bool )Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), 100, JobsManager::MINOR);
            Main::getInstance()->getGrafanaManager()->addGoldMined($player->getXuid(), $this->getPosition());
        }
        return parent::onBreak($item, $player, $returnedItems);
    }

    public function isAffectedBySilkTouch() : bool{
        return false;
    }

    protected function getXpDropAmount() : int{
        return mt_rand(10, 20);
    }
}