<?php

namespace core\blocks\blocks;

use core\Main;
use core\player\CustomPlayer;
use core\tasks\BlockJumpTask;
use core\traits\UtilsTrait;
use pocketmine\block\Opaque;
use pocketmine\entity\Entity;
use pocketmine\scheduler\ClosureTask;

class BlockJump extends Opaque
{
    use UtilsTrait;

    public function onEntityLand(Entity $entity): ?float
    {
        if ($entity instanceof CustomPlayer) {
            if (!isset(BlockJumpTask::$posCache[$this->positionToStringPlayer($this->getPosition(), $entity)])) {
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new BlockJumpTask($this, $entity), 1);
            }
        }
        return null;
    }

    public function hasEntityCollision(): bool
    {
        return true;
    }
}