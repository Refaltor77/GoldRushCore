<?php

namespace core\blocks\vanilla;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\math\Facing;

class Farmland extends \pocketmine\block\Farmland
{

    public function onEntityLand(Entity $entity) : ?float{
        return null;
    }

    public function onNearbyBlockChange() : void{
        if($this->getSide(Facing::UP)->isSolid()){
            $this->position->getWorld()->setBlock($this->position, VanillaBlocks::DIRT());
        }
    }
}