<?php

namespace core\inventory;


use core\items\crops\Raisin;
use core\items\foods\RaisinMoisie;
use core\Main;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use tedo0627\inventoryui\CustomInventory;

class BarrelInventory extends CustomInventory {


    public function click(Player $player, int $slot, Item $sourceItem, Item $targetItem): bool
    {
        $slotsRaisin = [0, 1, 2, 10, 11, 12, 20, 21, 22];
        $slotsRaisinMur = [6, 7, 8, 16, 17, 18, 26, 27, 28];

        if (!in_array($slot, $slotsRaisin) && !in_array($slot, $slotsRaisinMur)) return true;



        if (in_array($slot, $slotsRaisin)) {
            if ($sourceItem instanceof Raisin && $targetItem->isNull() || $targetItem instanceof Raisin) {

            } else return true;


            if ($sourceItem->isNull() && !$targetItem instanceof Raisin) {
                return true;
            }
        }

        if (in_array($slot, $slotsRaisinMur)) {
            if (!$targetItem instanceof RaisinMoisie && $sourceItem->isNull()) {
                return true;
            }

            if (!$sourceItem instanceof RaisinMoisie && $targetItem->isNull()) {
                return true;
            }
        }


        if ($targetItem instanceof Raisin) {
            if (!in_array($slot, $slotsRaisin)) {
                return true;
            }
        }

        if ($targetItem instanceof RaisinMoisie) {
            if (!in_array($slot, $slotsRaisinMur)) {
                return true;
            }
        }

        return parent::click($player, $slot, $sourceItem, $targetItem);
    }

    public function canAddItemMoisie(Item $itemAdd) : bool{
        $slotsRaisinMur = [6, 7, 8, 16, 17, 18, 26, 27, 28];
        $iSize = 64 * 9;
        foreach ($slotsRaisinMur as $i) {
            $item = $this->getItem($i);
            $iSize -= $item->getCount();
        }

        if ($itemAdd->getCount() <= $iSize) return true;
        return false;
    }


    public function addItemMoisie(Item ...$slots): array
    {
        /** @var Item[] $itemSlots */
        /** @var Item[] $slots */
        $itemSlots = [];
        foreach($slots as $slot){
            if(!$slot->isNull()){
                $itemSlots[] = clone $slot;
            }
        }

        /** @var Item[] $returnSlots */
        $returnSlots = [];

        foreach($itemSlots as $item){
            $leftover = $this->interneMoisie($item);
            if(!$leftover->isNull()){
                $returnSlots[] = $leftover;
            }
        }

        return $returnSlots;
    }

    public function interneMoisie(Item $newItem): Item {
        $emptySlots = [];

        $maxStackSize = min($this->getMaxStackSize(), $newItem->getMaxStackSize());

        $slotsRaisinMur = [6, 7, 8, 16, 17, 18, 26, 27, 28];

        foreach ($slotsRaisinMur as $i) {
            if($this->isSlotEmpty($i)){
                $emptySlots[] = $i;
                continue;
            }
            $slotCount = $this->getMatchingItemCount($i, $newItem, true);
            if($slotCount === 0){
                continue;
            }

            if($slotCount < $maxStackSize){
                $amount = min($maxStackSize - $slotCount, $newItem->getCount());
                if($amount > 0){
                    $newItem->setCount($newItem->getCount() - $amount);
                    $slotItem = $this->getItem($i);
                    $slotItem->setCount($slotItem->getCount() + $amount);
                    $this->setItem($i, $slotItem);
                    if($newItem->getCount() <= 0){
                        break;
                    }
                }
            }
        }


        if(count($emptySlots) > 0){
            foreach($emptySlots as $slotIndex){
                $amount = min($maxStackSize, $newItem->getCount());
                $newItem->setCount($newItem->getCount() - $amount);
                $slotItem = clone $newItem;
                $slotItem->setCount($amount);
                $this->setItem($slotIndex, $slotItem);
                if($newItem->getCount() <= 0){
                    break;
                }
            }
        }

        return $newItem;
    }

    public function onClose(Player $who): void
    {
        if (!Main::getInstance()->getStaffManager()->isInStaffMode($who)) {
            $who->getWorld()->addSound($who->getEyePos(), new ChestCloseSound());
        }
        parent::onClose($who);
    }


    public function onOpen(Player $who): void
    {
        if (!Main::getInstance()->getStaffManager()->isInStaffMode($who)) {
            $who->getWorld()->addSound($who->getEyePos(), new ChestOpenSound());
        }
        parent::onOpen($who);
    }
}