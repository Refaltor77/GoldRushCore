<?php

namespace core\blocks\vanilla;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Fire;
use pocketmine\block\SoulFire;
use pocketmine\block\utils\SupportType;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\math\Facing;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class FireCustom extends Fire
{
    public const MAX_AGE = 15;

    protected int $age = 0;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
        $w->boundedInt(4, 0, self::MAX_AGE, $this->age);
    }

    public function getAge() : int{ return $this->age; }

    /** @return $this */
    public function setAge(int $age) : self{
        if($age < 0 || $age > self::MAX_AGE){
            throw new \InvalidArgumentException("Age must be in range 0 ... " . self::MAX_AGE);
        }
        $this->age = $age;
        return $this;
    }

    protected function getFireDamage() : int{
        return 1;
    }

    private function canBeSupportedBy(Block $block) : bool{
        return $block->getSupportType(Facing::UP)->equals(SupportType::FULL());
    }

    public function onNearbyBlockChange() : void{
        $world = $this->position->getWorld();
        $down = $this->getSide(Facing::DOWN);
        if(SoulFire::canBeSupportedBy($down)){
            $world->setBlock($this->position, VanillaBlocks::SOUL_FIRE());
        }elseif(!$this->canBeSupportedBy($this->getSide(Facing::DOWN)) && !$this->hasAdjacentFlammableBlocks()){

        }else{
            $world->scheduleDelayedBlockUpdate($this->position, mt_rand(30, 40));
        }
    }

    public function ticksRandomly() : bool{
        return true;
    }

    public function onRandomTick() : void{
        $down = $this->getSide(Facing::DOWN);

        $result = null;
        if($this->age < self::MAX_AGE && mt_rand(0, 2) === 0){
            $this->age++;
            $result = $this;
        }
        $canSpread = true;

        if(!$down->burnsForever()){
            if($this->age === self::MAX_AGE){
                if(!$down->isFlammable() && mt_rand(0, 3) === 3){ //1/4 chance to extinguish
                    $canSpread = false;
                    $result = VanillaBlocks::AIR();
                }
            }elseif(!$this->hasAdjacentFlammableBlocks()){
                $canSpread = false;
                if($down->isTransparent() || $this->age > 3){
                    $result = VanillaBlocks::AIR();
                }
            }
        }

        $world = $this->position->getWorld();

        $world->scheduleDelayedBlockUpdate($this->position, mt_rand(30, 40));

        if($canSpread){
            $this->burnBlocksAround();
            $this->spreadFire();
        }
    }

    public function onScheduledUpdate() : void{
        $this->onRandomTick();
    }

    private function hasAdjacentFlammableBlocks() : bool{
        foreach(Facing::ALL as $face){
            if($this->getSide($face)->isFlammable()){
                return true;
            }
        }

        return false;
    }

    private function burnBlocksAround() : void{

        foreach($this->getHorizontalSides() as $side){
            $this->burnBlock($side, 300);
        }

        //vanilla uses a 250 upper bound here, but I don't think they intended to increase the chance of incineration
        $this->burnBlock($this->getSide(Facing::UP), 350);
        $this->burnBlock($this->getSide(Facing::DOWN), 350);
    }

    private function burnBlock(Block $block, int $chanceBound) : void{
        if(mt_rand(0, $chanceBound) < $block->getFlammability()){
            $cancelled = false;
            if(BlockBurnEvent::hasHandlers()){
                $ev = new BlockBurnEvent($block, $this);
                $ev->call();
                $ev->cancel();
                $cancelled = $ev->isCancelled();
            }
            if(!$cancelled){
                $block->onIncinerate();

                $world = $this->position->getWorld();
                if($world->getBlock($block->getPosition())->isSameState($block)){
                    $spreadedFire = false;
                    if(mt_rand(0, $this->age + 9) < 5){
                        $fire = clone $this;
                        $fire->age = min(self::MAX_AGE, $fire->age + (mt_rand(0, 4) >> 2));
                        $spreadedFire = $this->spreadBlock($block, $fire);
                    }
                    if(!$spreadedFire){

                    }
                }
            }
        }
    }

    private function spreadFire() : void{
        $world = $this->position->getWorld();
        $difficultyChanceIncrease = $world->getDifficulty() * 7;
        $ageDivisor = $this->age + 30;

        for($y = -1; $y <= 4; ++$y){
            $targetY = $y + (int) $this->position->y;
            if($targetY < World::Y_MIN || $targetY >= World::Y_MAX){
                continue;
            }
            //Higher blocks have a lower chance of catching fire
            $randomBound = 100 + ($y > 1 ? ($y - 1) * 100 : 0);

            for($z = -1; $z <= 1; ++$z){
                $targetZ = $z + (int) $this->position->z;
                for($x = -1; $x <= 1; ++$x){
                    if($x === 0 && $y === 0 && $z === 0){
                        continue;
                    }
                    $targetX = $x + (int) $this->position->x;
                    if(!$world->isInWorld($targetX, $targetY, $targetZ)){
                        continue;
                    }

                    if(!$world->isChunkLoaded($targetX >> Chunk::COORD_BIT_SIZE, $targetZ >> Chunk::COORD_BIT_SIZE)){
                        continue;
                    }
                    $block = $world->getBlockAt($targetX, $targetY, $targetZ);
                    if($block->getTypeId() !== BlockTypeIds::AIR){
                        continue;
                    }


                    $encouragement = 0;
                    foreach($block->position->sides() as $vector3){
                        if($world->isInWorld($vector3->x, $vector3->y, $vector3->z)){
                            $encouragement = max($encouragement, $world->getBlockAt($vector3->x, $vector3->y, $vector3->z)->getFlameEncouragement());
                        }
                    }

                    if($encouragement <= 0){
                        continue;
                    }

                    $maxChance = intdiv($encouragement + 40 + $difficultyChanceIncrease, $ageDivisor);

                    if($maxChance > 0 && mt_rand(0, $randomBound - 1) <= $maxChance){
                        $new = clone $this;
                        $new->age = min(self::MAX_AGE, $this->age + (mt_rand(0, 4) >> 2));
                        $this->spreadBlock($block, $new);
                    }
                }
            }
        }
    }

    private function spreadBlock(Block $block, Block $newState) : bool{
        return false;
    }
}