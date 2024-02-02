<?php

namespace core\blocks\vanilla;

use core\inventory\EcInventoryCustom;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class EnderChest extends \pocketmine\block\EnderChest
{
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        if($player instanceof Player){
            $enderChest = $this->position->getWorld()->getTile($this->position);
            if($this->getSide(Facing::UP)->isTransparent()){
                $enderChest->setViewerCount($enderChest->getViewerCount() + 1);
                $player->setCurrentWindow(new EcInventoryCustom(who: $player));
            }
        }

        return true;
    }
}