<?php

namespace core\blocks;

use cosmicpe\blockdata\BlockData;
use pocketmine\nbt\tag\CompoundTag;

class BlockHistoryData implements BlockData{

    private int $age;

    public function __construct(int $age){
        $this->age = $age;
    }


    public static function nbtDeserialize(CompoundTag $nbt) : BlockData{
        return new BlockHistoryData(
            $nbt->getInt("age"),
        );
    }

    public function getAge() : string{
        return $this->age;
    }

    public function setAge(int $age): void {
        $this->age = $age;
    }


    public function nbtSerialize() : CompoundTag{
        return CompoundTag::create()->setInt("age", $this->age);
    }
}