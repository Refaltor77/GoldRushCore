<?php

namespace core\blocks\tiles;

use core\inventory\AmethystChestInventory;
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

class AmethystChestTile extends Spawnable implements Container, Nameable{

    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait {
        onBlockDestroyedHook as containerTraitBlockDestroyedHook;
    }


    public bool $isLocked = false;
    private string $code = "";
    public string $authorXuid = "";
    private array $authorized = [];
    protected AmethystChestInventory $inventory;


    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);
        $this->inventory = new AmethystChestInventory(96, "amethyst_chest");
    }

    public function addAuthorized(Player $player): void {
        $this->authorized[] = $player->getXuid();
    }

    public function isAuthorized(Player $player): bool {
        return in_array($player->getXuid(), $this->authorized);
    }

    public function setAuthor(Player $player): void {
        $this->authorXuid = $player->getXuid();
    }

    public function setLocked(string $code): void {
        $this->code = $code;
        $this->isLocked = true;
        $this->authorized = [];
    }


    public function getCode(): string {
        return $this->code;
    }

    public function setLockedFalse(): void {
        $this->isLocked = false;
        $this->code = "";
    }

    public function readSaveData(CompoundTag $nbt) : void{
        if ($nbt->getString("is_locked", "none" !== "none")) {
            $this->isLocked = true;
            $this->code = $nbt->getString("is_locked");
        }

        if ($nbt->getTag('authorized') !== null) {
            $tag = $nbt->getListTag("authorized");
            foreach ($tag->getAllValues() as $xuid) {
                if ($xuid instanceof StringTag) {
                    $this->authorized[] = $xuid->getValue();
                }
            }
        }

        if ($nbt->getString('author', 'none') !== 'none') {
            $this->authorXuid = $nbt->getString('author');
        }
        $this->loadName($nbt);
        $this->loadItems($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        if ($this->isLocked) {
            $code = $this->code;
            $nbt->setString('is_locked', $code);
            $nbt->setString('author', $this->authorXuid);
            $listTag = new ListTag();
            foreach ($this->authorized as $xuid) {
                $listTag->push(new StringTag($xuid));
            }
            $nbt->setTag("authorized", $listTag);
        }
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

    public function getInventory() : AmethystChestInventory{
        return $this->inventory;
    }

    public function getRealInventory() : AmethystChestInventory{
        return $this->inventory;
    }


    public function getDefaultName() : string{
        return "amethyst_chest";
    }


    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        $this->addNameSpawnData($nbt);
    }
}