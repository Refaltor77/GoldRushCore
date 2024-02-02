<?php

namespace core\blocks\blocks\biomes\spectral;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Farmland;
use pocketmine\block\Opaque;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class SpectralFarmlandWet extends Farmland
{
    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        $this->setWetness(7);
        parent::__construct($idInfo, $name, $typeInfo);
    }

    protected function recalculateCollisionBoxes() : array{
        return [AxisAlignedBB::one()->trim(Facing::UP, 1 / 16)];
    }
}