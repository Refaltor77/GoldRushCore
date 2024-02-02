<?php

namespace core\blocks\blocks\biomes\spectral;

use pocketmine\block\Block;
use pocketmine\block\Wood;
use pocketmine\item\Axe;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\ItemUseOnBlockSound;

abstract class CustomWood extends Wood
{
    abstract public function getStrippedWood(): Block;


    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($this->getStrippedWood()->asItem()->equals($this->asItem())) return false;
        if(!$this->isStripped() && $item instanceof Axe){
            $item->applyDamage(1);
            $this->setStripped(true);
            $this->position->getWorld()->setBlock($this->position, $this->getStrippedWood());
            $this->position->getWorld()->addSound($this->position, new ItemUseOnBlockSound($this));
            return true;
        }
        return false;
    }
}