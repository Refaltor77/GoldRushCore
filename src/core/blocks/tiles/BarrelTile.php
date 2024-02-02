<?php

namespace core\blocks\tiles;

use core\inventory\AmethystChestInventory;
use core\inventory\BarrelInventory;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class BarrelTile extends Spawnable implements Container, Nameable{

    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait {
        onBlockDestroyedHook as containerTraitBlockDestroyedHook;
    }


    protected BarrelInventory $inventory;


    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);
        $this->inventory = new BarrelInventory(29, "barrel");
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->loadName($nbt);
        $this->loadItems($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $this->saveName($nbt);
        $this->saveItems($nbt);
    }

    public function getCleanedNBT() : ?CompoundTag{
        $tag = parent::getCleanedNBT();
        return $tag;
    }

    public function close() : void{
        if(!$this->closed){
            $this->inventory->removeAllViewers();

            parent::close();
        }
    }

    protected function onBlockDestroyedHook() : void{
        $this->containerTraitBlockDestroyedHook();
    }

    public function getInventory() : BarrelInventory{
        return $this->inventory;
    }

    public function getRealInventory() : BarrelInventory{
        return $this->inventory;
    }


    public function getDefaultName() : string{
        return "amethyst_chest";
    }





    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        $this->addNameSpawnData($nbt);
    }
}