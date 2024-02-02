<?php

namespace core\managers\chestRefill;


use core\managers\Manager;
use core\settings\BlockIds;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Chest;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\world\Position;

class ChestRefill extends Manager
{
    const CHEST_REFILL = [
        [195, 74, -278],
        [183, 75, -232],
        [199, 74, -231],
        [207, 74, -253],
    ];

    public function refill(): void {
        $itemLegend = [
            CustomiesItemFactory::getInstance()->get(Ids::FLOWER_PERCENT, 1),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER, 2),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SWORD),
            CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK, 16),
            CustomiesItemFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK, 32),
        ];




        $itemRare = [
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT, 64),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT, 32),
            CustomiesItemFactory::getInstance()->get(Ids::KEYPAD),
            CustomiesItemFactory::getInstance()->get(Ids::KEYPAD),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_DYNAMITE, 16),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE, 32),
            CustomiesItemFactory::getInstance()->get(Ids::WATER_DYNAMITE, 16),
            CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE, 32),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUR, 64),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HASTE, 2),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED, 2),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL, 2),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE, 2),
        ];



        $itemCommon = [
            VanillaItems::COMPASS(),
            VanillaItems::DIAMOND()->setCount(64),
            VanillaItems::IRON_INGOT()->setCount(64),
            CustomiesItemFactory::getInstance()->get(Ids::ENDER_PEARL, 32),
            CustomiesItemFactory::getInstance()->get(Ids::ENDER_PEARL, 32),
            CustomiesItemFactory::getInstance()->get(Ids::ENDER_PEARL, 32),
            CustomiesItemFactory::getInstance()->get(Ids::FREEZE_PEARL, 32),
            CustomiesItemFactory::getInstance()->get(Ids::FREEZE_PEARL, 32),
            CustomiesItemFactory::getInstance()->get(Ids::FREEZE_PEARL, 32),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUR, 64),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_HASTE, 2),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_SPEED, 2),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_HEAL, 2),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_FORCE, 2),
        ];


        for ($i = 0; $i !== 4; $i++) {
            $content = [];

            $content[] = $itemCommon[array_rand($itemCommon)];
            $content[] = $itemCommon[array_rand($itemCommon)];
            $content[] = $itemCommon[array_rand($itemCommon)];
            $content[] = $itemCommon[array_rand($itemCommon)];
            $content[] = $itemCommon[array_rand($itemCommon)];
            $content[] = $itemCommon[array_rand($itemCommon)];



            $content[] = $itemRare[array_rand($itemRare)];
            $content[] = $itemRare[array_rand($itemRare)];
            $content[] = $itemRare[array_rand($itemRare)];
            $content[] = $itemRare[array_rand($itemRare)];

            $content[] = $itemLegend[array_rand($itemLegend)];
            $content[] = $itemLegend[array_rand($itemLegend)];
            $content[] = $itemLegend[array_rand($itemLegend)];


            $arrayPos = self::CHEST_REFILL[$i];

            $pos = new Position($arrayPos[0], $arrayPos[1], $arrayPos[2], Server::getInstance()->getWorldManager()->getDefaultWorld());
            $block = $pos->getWorld()->getBlock($pos);
            if (!$block instanceof Chest) {
                $pos->getWorld()->setBlock($pos, VanillaBlocks::CHEST());
            }

            $tile = $pos->getWorld()->getTile($pos);
            if ($tile instanceof \pocketmine\block\tile\Chest) {
                $tile->getInventory()->setContents($content);
            }
        }
    }
}