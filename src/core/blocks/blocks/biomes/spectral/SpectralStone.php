<?php

namespace core\blocks\blocks\biomes\spectral;

use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\block\Opaque;
use pocketmine\item\Item;

class SpectralStone extends Opaque
{
    public function getDrops(Item $item): array
    {
        return [
            CustomiesBlockFactory::getInstance()->get("goldrush:spectral_cobblestone")->asItem()
        ];
    }
}