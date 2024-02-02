<?php

namespace core\particles;

use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\world\Position;

class FarmerParticle extends CustomParticle
{
    public static function getParticleNetworkId(): string
    {
        return 'goldrush:farmer';
    }

    public static function spawn(Position $position): void
    {
        $position->getWorld()->broadcastPacketToViewers($position,  SpawnParticleEffectPacket::create(
            DimensionIds::OVERWORLD,
            -1,
            $position,
            self::getParticleNetworkId(),
            null
        ));
    }
}
