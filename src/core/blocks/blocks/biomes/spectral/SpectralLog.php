<?php

namespace core\blocks\blocks\biomes\spectral;

use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Dirt;
use pocketmine\block\Opaque;
use pocketmine\block\utils\WoodType;
use pocketmine\block\Wood;

class SpectralLog extends CustomWood
{
    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo, WoodType::OAK());
    }

    public function getStrippedWood(): Block
    {
        return CustomiesBlockFactory::getInstance()->get("goldrush:stripped_spectral_log");
    }
}