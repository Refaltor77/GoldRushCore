<?php

namespace core\entities;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;

class DoorBox extends Entity
{

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $location->pitch = 0.0;
        $location->yaw = round($location->getYaw() / 90) * 90;
        $location->x = $location->getFloorX() + 0.5;
        $location->y = $location->getFloorY();
        $location->z = $location->getFloorZ() + 0.5;
        parent::__construct($location, $nbt);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.5, 1.0);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }

    public function onUpdate(int $currentTick): bool
    {
        return false;
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
    }

    protected function tryChangeMovement(): void
    {

    }

    public static function getNetworkTypeId(): string
    {
        return 'goldrush:door_box';
    }
}