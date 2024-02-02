<?php

namespace core\blocks;

use cosmicpe\blockdata\BlockData;
use pocketmine\nbt\tag\CompoundTag;

class DurabilityObsidian implements BlockData{

    private int $durability;

    public function __construct(int $durability){
        $this->durability = $durability;
    }


    public static function nbtDeserialize(CompoundTag $nbt) : BlockData{
        return new DurabilityObsidian(
            $nbt->getInt("durability"),
        );
    }

    public function getDurability() : int{
        return $this->durability;
    }

    public function setDurability(int $durability): void {
        $this->durability = $durability;
    }


    public function nbtSerialize() : CompoundTag{
        return CompoundTag::create()->setInt("durability", $this->durability);
    }
}