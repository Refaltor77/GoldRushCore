<?php

namespace core\blocks\blocks\biomes\spectral;

use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Opaque;
use pocketmine\item\Item;

class SpectralOre extends Opaque
{
    public function getDrops(Item $item): array
    {
        return [
            CustomiesItemFactory::getInstance()->get("goldrush:raw_spectral")
        ];
    }
}