<?php

namespace core\blocks\blocks\biomes\spectral;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Planks;
use pocketmine\block\utils\WoodType;

class SpectralPlanks extends Planks
{
    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo, WoodType::OAK());
    }

    public function getFuelTime() : int{
        return 1000;
    }

    public function getFlameEncouragement() : int{
        return 5;
    }

    public function getFlammability() : int{
        return 60;
    }
}