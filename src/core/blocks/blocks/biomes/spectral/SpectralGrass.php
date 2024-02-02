<?php

namespace core\blocks\blocks\biomes\spectral;

use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Grass;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Fertilizer;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Shovel;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\ItemUseOnBlockSound;

class SpectralGrass extends Grass
{
    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            CustomiesBlockFactory::getInstance()->get('goldrush:spectral_dirt')->asItem()
        ];
    }

    public function isAffectedBySilkTouch() : bool{
        return true;
    }

    public function ticksRandomly() : bool{
        return true;
    }

    public function onRandomTick() : void{
        $world = $this->position->getWorld();
        $lightAbove = $world->getFullLightAt($this->position->x, $this->position->y + 1, $this->position->z);
        if($lightAbove < 4 && $world->getBlockAt($this->position->x, $this->position->y + 1, $this->position->z)->getLightFilter() >= 2){
            //grass dies
            BlockEventHelper::spread($this, CustomiesBlockFactory::getInstance()->get('goldrush:spectral_dirt'), $this);
        }elseif($lightAbove >= 9){
            //try grass spread
            for($i = 0; $i < 4; ++$i){
                $x = mt_rand($this->position->x - 1, $this->position->x + 1);
                $y = mt_rand($this->position->y - 3, $this->position->y + 1);
                $z = mt_rand($this->position->z - 1, $this->position->z + 1);

                $b = $world->getBlockAt($x, $y, $z);
                if(
                    !($b instanceof SpectralDirt) ||
                    $b->getDirtType() !== DirtType::NORMAL ||
                    $world->getFullLightAt($x, $y + 1, $z) < 4 ||
                    $world->getBlockAt($x, $y + 1, $z)->getLightFilter() >= 2
                ){
                    continue;
                }

                BlockEventHelper::spread($b, $this, $this);
            }
        }
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        if($this->getSide(Facing::UP)->getTypeId() !== BlockTypeIds::AIR){
            return false;
        }
        $world = $this->position->getWorld();
        if($face !== Facing::DOWN){
            if($item instanceof Hoe){
                $item->applyDamage(1);
                $newBlock = CustomiesBlockFactory::getInstance()->get("spectral_dirt_farmland");
                $world->addSound($this->position->add(0.5, 0.5, 0.5), new ItemUseOnBlockSound($newBlock));
                $world->setBlock($this->position, $newBlock);

                return true;
            }elseif($item instanceof Shovel){
                $item->applyDamage(1);
                $newBlock = CustomiesBlockFactory::getInstance()->get("goldrush:spectral_grass_path");
                $world->addSound($this->position->add(0.5, 0.5, 0.5), new ItemUseOnBlockSound($newBlock));
                $world->setBlock($this->position, $newBlock);

                return true;
            }
        }

        return false;
    }
}