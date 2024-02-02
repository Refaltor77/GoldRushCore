<?php

namespace core\entities;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\format\Chunk;

class GoldrushText extends Entity
{

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setScale(3);
        parent::initEntity($nbt);
    }

    public function onUpdate(int $currentTick): bool
    {

        return parent::onUpdate($currentTick);
    }




    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2, 1);
    }

    protected function getInitialDragMultiplier(): float
    {
       return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }

    public static function getNetworkTypeId(): string
    {
        return "goldrush:goldrush_text";
    }
}