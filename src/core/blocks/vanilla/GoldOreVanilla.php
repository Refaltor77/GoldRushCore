<?php

namespace core\blocks\vanilla;

use pocketmine\block\Opaque;
use pocketmine\item\Item;

class GoldOreVanilla extends Opaque
{
    public function getDrops(Item $item): array
    {
        return [];
    }
}