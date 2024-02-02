<?php

namespace core\blocks\blocks\luckyblock;

use core\traits\ManagerTrait;
use core\traits\UtilsTrait;
use pocketmine\block\Opaque;
use pocketmine\item\Item;

abstract class AbstractLuckyBlock extends Opaque{

    use UtilsTrait;
    use ManagerTrait;

    public function getDropsForCompatibleTool(Item $item): array
    {
        return [];
    }

    public function getDropsForIncompatibleTool(Item $item): array
    {
        return [];
    }

    public function getSilkTouchDrops(Item $item): array
    {
        return [];
    }
}