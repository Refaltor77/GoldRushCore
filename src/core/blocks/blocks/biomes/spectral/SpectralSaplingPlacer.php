<?php

namespace core\blocks\blocks\biomes\spectral;

use core\blocks\blocks\biomes\spectral\three\SpectralThree;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\Sapling;
use pocketmine\block\utils\SaplingType;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\generator\object\TreeFactory;

class SpectralSaplingPlacer extends Sapling
{
    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo, SaplingType::OAK());
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        if($blockReplace instanceof SpectralGrass || $blockReplace instanceof SpectralDirt){
            return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        }

        return false;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        if($item instanceof Fertilizer && $this->grow($player)){
            $item->pop();

            return true;
        }

        return false;
    }

    private function grow(?Player $player) : bool{
        $random = new Random(mt_rand());
        $tree = new SpectralThree();
        $transaction = $tree?->getBlockTransaction($this->position->getWorld(), $this->position->getFloorX(), $this->position->getFloorY(), $this->position->getFloorZ(), $random);
        if($transaction === null){
            return false;
        }

        $ev = new StructureGrowEvent($this, $transaction, $player);
        $ev->call();
        if(!$ev->isCancelled()){
            return $transaction->apply();
        }
        return false;
    }
}