<?php

namespace core\particles;

use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\world\Position;

class BucheronParticle extends CustomParticle
{
    public static function getParticleNetworkId(): string
    {
        return 'goldrush:bucheron';
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
