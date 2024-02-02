<?php

namespace core\entities;

use core\Main;
use core\player\CustomPlayer;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class Bank extends Entity
{

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setScale(2);
        parent::initEntity($nbt);
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
        return "goldrush:bank";
    }
}