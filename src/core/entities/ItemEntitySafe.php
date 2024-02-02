<?php

namespace core\entities;

use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class ItemEntitySafe extends ItemEntity
{

    protected int $despawnDelay = -1;

    public function __construct(Location $location, Item $item, ?CompoundTag $nbt = null)
    {
        $item->setNamedTag(CompoundTag::create());
        parent::__construct($location, $item, $nbt);
    }

    public function initEntity(CompoundTag $nbt): void
    {

    }

    public function tryChangeMovement(): void
    {

    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        return true;
    }

    public function onCollideWithPlayer(Player $player): void
    {

    }

    public function onNearbyBlockChange(): void
    {

    }

    public function onUpdate(int $currentTick): bool
    {
        return true;
    }
}