<?php

namespace core\blocks\ores;

use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\player\Player;

class SulfurOre extends Opaque
{

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            CustomiesItemFactory::getInstance()->get(Ids::SULFUR_POWDER, mt_rand(2, 5))
        ];
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            Main::getInstance()->getTopLuckManager()->addOre($player);
            $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::MINOR, 10);
            if ($bool )Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), 10, JobsManager::MINOR);
        }
        return parent::onBreak($item, $player, $returnedItems);
    }

    public function isAffectedBySilkTouch() : bool{
        return false;
    }

    protected function getXpDropAmount() : int{
        return mt_rand(3, 5);
    }
}