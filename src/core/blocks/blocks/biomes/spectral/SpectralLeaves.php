<?php

namespace core\blocks\blocks\biomes\spectral;

use core\particles\SpectralParticle;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Leaves;
use pocketmine\block\Transparent;
use pocketmine\block\utils\LeavesType;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\world\particle\DustParticle;

class SpectralLeaves extends Leaves
{
    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo, LeavesType::OAK());
    }

    public function onRandomTick(): void
    {

    }
}