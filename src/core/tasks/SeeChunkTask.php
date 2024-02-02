<?php

namespace core\tasks;

use core\Main;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\world\format\Chunk;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\RedstoneParticle;

class SeeChunkTask extends Task
{
    public static array $seeChunk = [];

    public function onRun(): void
    {
        foreach (self::$seeChunk as $player) {
            $chunkX = $player->getPosition()->getFloorX() >> Chunk::COORD_BIT_SIZE;
            $chunkZ = $player->getPosition()->getFloorZ() >> Chunk::COORD_BIT_SIZE;

            $minX = (float)$chunkX * 16;
            $maxX = (float)$minX + 16;
            $minZ = (float)$chunkZ * 16;
            $maxZ = (float)$minZ + 16;

            for ($x = $minX; $x <= $maxX; $x += 0.5) {
                for ($z = $minZ; $z <= $maxZ; $z += 0.5) {
                    if ($x === $minX || $x === $maxX || $z === $minZ || $z === $maxZ) {
                        if (Main::getInstance()->getFactionManager()->isInFaction($player->getXuid())) {
                            if (Main::getInstance()->getFactionManager()->isInClaim($player->getPosition())) {
                                if (Main::getInstance()->getFactionManager()->getFactionNameInClaim($player->getPosition()) ===
                                    Main::getInstance()->getFactionManager()->getFactionName($player->getPosition())
                                ) {
                                    $player->getWorld()->addParticle(new Vector3($x, $player->getPosition()->y + 1.5, $z), new DustParticle(new Color(0, 255, 0)), [$player]);
                                } else {
                                    $player->getWorld()->addParticle(new Vector3($x, $player->getPosition()->y + 1.5, $z), new DustParticle(new Color(255, 0, 0)), [$player]);
                                }
                            } else $player->getWorld()->addParticle(new Vector3($x, $player->getPosition()->y + 1.5, $z), new DustParticle(new Color(0, 0, 0)), [$player]);
                        } else  $player->getWorld()->addParticle(new Vector3($x, $player->getPosition()->y + 1.5, $z), new DustParticle(new Color(0, 0, 0)), [$player]);
                    }
                }
            }
        }
    }
}