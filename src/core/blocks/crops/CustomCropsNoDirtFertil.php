<?php

namespace core\blocks\crops;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\Flowable;
use pocketmine\block\Melon;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\block\Wheat;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class CustomCropsNoDirtFertil extends Flowable
{
    public const STAGE_SAPLING = 0;
    public const STAGE_BUSH_NO_BERRIES = 1;
    public const STAGE_BUSH_SOME_BERRIES = 2;
    public const STAGE_MATURE = 3;

    protected int $age = self::STAGE_SAPLING;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
        $w->boundedInt(2, self::STAGE_SAPLING, self::STAGE_MATURE, $this->age);
    }

    public function getAge() : int{ return $this->age; }

    /** @return $this */
    public function setAge(int $age) : self{
        if($age < self::STAGE_SAPLING || $age > self::STAGE_MATURE){
            throw new \InvalidArgumentException("Age must be in range 0-3");
        }
        $this->age = $age;
        return $this;
    }

    public function getBerryDropAmount() : int{
        if($this->age === self::STAGE_MATURE){
            return mt_rand(2, 3);
        }elseif($this->age >= self::STAGE_BUSH_SOME_BERRIES){
            return mt_rand(1, 2);
        }
        return 0;
    }

    protected function canBeSupportedBy(Block $block) : bool{
        return $block->getTypeId() !== BlockTypeIds::FARMLAND && //bedrock-specific thing (bug?)
            ($block->hasTypeTag(BlockTypeTags::DIRT) || $block->hasTypeTag(BlockTypeTags::MUD));
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        if(!$this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN))){
            return false;
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        $world = $this->position->getWorld();
        if($this->age < self::STAGE_MATURE && $item instanceof Fertilizer){
            $block = clone $this;
            $block->age++;

            $ev = new BlockGrowEvent($this, $block, $player);
            $ev->call();

            if(!$ev->isCancelled()){

                $item->pop();
            }

        }elseif(($dropAmount = $this->getBerryDropAmount()) > 0){

            $world->dropItem($this->position, $this->asItem()->setCount($dropAmount));
        }

        return true;
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        $count = match($this->age){
            self::STAGE_MATURE => FortuneDropHelper::discrete($item, 2, 3),
            self::STAGE_BUSH_SOME_BERRIES => FortuneDropHelper::discrete($item, 1, 2),
            default => 0
        };
        return [
            $this->asItem()->setCount($count)
        ];
    }

    public function onNearbyBlockChange() : void{
        if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
            $this->position->getWorld()->useBreakOn($this->position);
        }
    }

    public function onScheduledUpdate(): void
    {

    }

    public function ticksRandomly() : bool{
        return true;
    }

    public function onRandomTick() : void{

    }

    public function hasEntityCollision() : bool{
        return true;
    }

    public function onEntityInside(Entity $entity) : bool{
        if($this->age >= self::STAGE_BUSH_NO_BERRIES && $entity instanceof Living){
            $entity->resetFallDistance();

            $entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageByBlockEvent::CAUSE_CONTACT, 1));
        }
        return true;
    }
}