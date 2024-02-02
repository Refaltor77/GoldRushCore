<?php

namespace core\blocks\blocks\chest;

use core\settings\BlockIds;
use customiesdevs\customies\block\permutations\Permutable;
use customiesdevs\customies\block\permutations\RotatableTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\Item;

class EmeraldChestLocked extends EmeraldChest implements Permutable
{
    use RotatableTrait;

    public function getDrops(Item $item): array
    {
        return [
            CustomiesItemFactory::getInstance()->get(BlockIds::EMERALD_CHEST)
        ];
    }
}