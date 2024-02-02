<?php

namespace core\blocks\vanilla;

use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\DoubleTallGrass;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class TallGrass extends DoubleTallGrass
{
    public function getDrops(Item $item): array
    {
        $return = [];
        if (mt_rand(0, 100) >= 98) $return[] = CustomiesItemFactory::getInstance()->get(Ids::SEEDS_WHEAT_OBSIDIAN);
        if (mt_rand(0, 100) >= 80) $return[] = VanillaItems::WHEAT_SEEDS();


        return parent::getDrops($item); // TODO: Change the autogenerated stub
    }
}