<?php

namespace core\blocks\tiles;

use core\inventory\AmethystChestInventory;
use core\inventory\DistillerieInventory;
use core\inventory\EmeraldChestInventory;
use pocketmine\block\inventory\ChestInventory;
use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class DistillerieTile extends Spawnable implements Container, Nameable{

    public int $distille = 0;

    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait {
        onBlockDestroyedHook as containerTraitBlockDestroyedHook;
    }


    protected DistillerieInventory $inventory;


    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);
        $this->inventory = new DistillerieInventory(50, "distillerie");
    }


    public function setAuthor(Player $player): void {
        $this->authorXuid = $player->getXuid();
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

    public function getInventory() : DistillerieInventory{
        return $this->inventory;
    }

    public function getRealInventory() : DistillerieInventory{
        return $this->inventory;
    }


    public function getDefaultName() : string{
        return "distillerie";
    }


    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        $this->addNameSpawnData($nbt);
    }
}