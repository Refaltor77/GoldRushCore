<?php

namespace core\blocks\blocks\biomes\spectral;

use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\block\GrassPath;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class SpectralGrassPath extends GrassPath
{
    /**
     * @return AxisAlignedBB[]
     */
    protected function recalculateCollisionBoxes() : array{
        return [AxisAlignedBB::one()->trim(Facing::UP, 1 / 16)];
    }

    public function onNearbyBlockChange() : void{
        if($this->getSide(Facing::UP)->isSolid()){
            $this->position->getWorld()->setBlock($this->position, CustomiesBlockFactory::getInstance()->get("goldrush:spectral_dirt"));
        }
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            CustomiesBlockFactory::getInstance()->get("goldrush:spectral_dirt")->asItem()
        ];
    }

    public function isAffectedBySilkTouch() : bool{
        return true;
    }
}