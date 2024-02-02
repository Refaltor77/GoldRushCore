<?php

namespace core\listeners\types\inventory;

use core\listeners\BaseEvent;
use core\Main;
use pocketmine\block\inventory\CraftingTableInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;

class InventoryClose extends BaseEvent
{
    public function onClose(InventoryCloseEvent $event){

        foreach ($event->getViewers() as $player) {
            Main::getInstance()->getInventoryManager()->saveInventory($player, true, false);
        }


        if($event->getInventory() instanceof CraftingTableInventory){
            if(isset($this->getPlugin()::$inCraftingTableCommand[$event->getPlayer()->getName()])){
                unset(Main::$inCraftingTableCommand[$event->getPlayer()->getName()]);
                $sender = $event->getPlayer();
                $blockTranslator = TypeConverter::getInstance()->getBlockTranslator();
                $vector = $sender->getPosition()->add(0, 2, 0);
                $pk = new UpdateBlockPacket();
                $pk->flags = UpdateBlockPacket::FLAG_NOGRAPHIC;
                $pk->blockPosition = BlockPosition::fromVector3($vector->add(0, 1, 0));
                $pk->blockRuntimeId = $blockTranslator->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId());
                $sender->getNetworkSession()->sendDataPacket($pk);
            }
        }
    }

}