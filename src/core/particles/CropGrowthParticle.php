<?php

namespace core\particles;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\world\particle\Particle;

class CropGrowthParticle implements Particle
{
    public function encode(Vector3 $pos): array
    {
        return [LevelEventPacket::standardParticle(
            particleId: 0,
            data: 0,
            position: $pos
        )];
    }
}