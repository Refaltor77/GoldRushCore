<?php

namespace core\blocks\blocks;

use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\BlockIds;
use core\settings\Ids;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\player\Player;

class PlatinumBlock extends Opaque
{

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)
        ];
    }

    public function isAffectedBySilkTouch() : bool{
        return false;
    }

    protected function getXpDropAmount() : int{
        return 0;
    }
}