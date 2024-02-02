<?php

namespace core\inventory;

use core\items\crops\BerryBlack;
use core\items\crops\BerryBlue;
use core\items\crops\BerryPink;
use core\items\crops\BerryYellow;
use core\items\crops\Raisin;
use core\items\foods\alcools\AlcoolPur;
use pocketmine\item\Item;
use pocketmine\player\Player;
use tedo0627\inventoryui\CustomInventory;

class DistillerieInventory extends CustomInventory
{

    const SLOT_BERRIES = [
        0, 1, 2, 3, 4, 5, 6
    ];

    const SLOT_ALCOOL_PUR = 13;
    const SLOT_RESULT = 23;

    public function __construct(int $size = 50, string $title = "distillerie", ?int $verticalLength = null)
    {
        parent::__construct($size, $title, $verticalLength);
    }


    public function click(Player $player, int $slot, Item $sourceItem, Item $targetItem): bool
    {
        if ($slot === self::SLOT_RESULT || $slot === self::SLOT_ALCOOL_PUR || in_array($slot, self::SLOT_BERRIES)) {
            if ($slot === self::SLOT_RESULT && $sourceItem->isNull()) {
                return true;
            }

            $classBerries = [
                BerryBlack::class,
                BerryBlue::class,
                BerryYellow::class,
                BerryPink::class
            ];

            if (in_array($slot, self::SLOT_BERRIES)) {
                if (in_array($sourceItem::class, $classBerries) && $targetItem->isNull() || in_array($targetItem::class, $classBerries)) {

                } else return true;


                if ($sourceItem->isNull() && !in_array($targetItem::class, $classBerries)) {
                    return true;
                }
            }


            if ($slot === self::SLOT_ALCOOL_PUR) {
                if (in_array($sourceItem::class, [AlcoolPur::class]) && $targetItem->isNull() || in_array($targetItem::class, [AlcoolPur::class])) {

                } else return true;


                if ($sourceItem->isNull() && !in_array($targetItem::class, [AlcoolPur::class])) {
                    return true;
                }
            }


            return parent::click($player, $slot, $sourceItem, $targetItem);
        } else return true;
    }
}