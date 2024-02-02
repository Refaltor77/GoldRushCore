<?php

namespace core\managers\box;

use core\Main;
use core\managers\Manager;
use core\settings\BlockIds;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class BoxManager extends Manager
{
    const COMMON = "common";
    const BOOST = "boost";
    const RARE = "rare";
    const FORTUNE = "fortune";
    const MYTHICAL = "mythical";
    const LEGENDARY = "legendary";
    const BLACK_GOLD = "black_gold";
    const BOX_COSMETICS  = "cosmetics";


    public function getItemsWithBox(string $boxType): array {
        switch ($boxType) {
            default:
            case self::BOX_COSMETICS:
                return [

                ];
                break;
            case self::COMMON:
                return [
                    1001 => VanillaItems::IRON_INGOT()->setCount(42),
                    900 => VanillaItems::DIAMOND()->setCount(32),
                    850 => CustomiesItemFactory::getInstance()->get(BlockIds::OBSIDIAN_AMETHYST, 32),
                    800 => CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK, 16),
                    750 => CustomiesItemFactory::getInstance()->get(BlockIds::EMERALD_CHEST, 1),
                    700 => CustomiesItemFactory::getInstance()->get(Ids::RAISIN, 32),
                    650 => CustomiesItemFactory::getInstance()->get(Ids::BERRY_PINK, 32),
                    600 => CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLUE, 32),
                    550 => CustomiesItemFactory::getInstance()->get(Ids::BERRY_YELLOW, 32),
                    500 => CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLACK, 32),
                    450 => CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT, 32),
                    400 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD),
                    350 => CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HELMET),
                    300 => CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 1),
                    7 => CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_CHEST),
                    6 => CustomiesItemFactory::getInstance()->get(Ids::EGG_SKELETON),
                    5 => CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER),
                ];
            case self::RARE:
                return [
                    1001 =>CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT, 32),
                    900 =>CustomiesItemFactory::getInstance()->get(BlockIds::OBSIDIAN_EMERALD, 16),
                    850 =>CustomiesItemFactory::getInstance()->get(BlockIds::BARREL),
                    800 =>CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SWORD),
                    750 =>CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP, 64),
                    700 =>CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED, 4),
                    650 =>CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE, 64),
                    600 =>CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON, 4),
                    500 =>CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HAMMER),
                    450 =>CustomiesItemFactory::getInstance()->get(Ids::SEEDS_WHEAT_OBSIDIAN, 64),
                    400 =>CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE, 4),
                    8 =>CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HAMMER),
                    7 =>CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE, 64),
                    6 =>CustomiesItemFactory::getInstance()->get(Ids::KEY_FORTUNE, 2),
                    5 =>CustomiesItemFactory::getInstance()->get(Ids::EGG_ZOMBIE),
                ];
            case self::BOOST:
                return [
                    1001 =>CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT, 12),
                    900 =>CustomiesItemFactory::getInstance()->get(BlockIds::OBSIDIAN_AMETHYST, 64),
                    850 =>CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_CHEST, 4),
                    800 =>CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SWORD),
                    750 =>CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP, 64),
                    700 =>CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED, 16),
                    650 =>CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE, 64),
                    600 =>CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON, 8),
                    500 =>CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HAMMER),
                    450 =>CustomiesItemFactory::getInstance()->get(Ids::SEEDS_WHEAT_OBSIDIAN, 64),
                    400 =>CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE, 8),
                    350 =>CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HAMMER),
                    7 =>CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE, 64),
                    6 =>CustomiesItemFactory::getInstance()->get(Ids::KEY_MYTHICAL, 2),
                    2 =>CustomiesItemFactory::getInstance()->get(Ids::EGG_CHICKEN),
                ];
            case self::FORTUNE:
                return [
                    1001 => CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)->setCount(32),
                    900 => CustomiesItemFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->setCount(6),
                    850 => CustomiesItemFactory::getInstance()->get(BlockIds::OBSIDIAN_AMETHYST)->setCount(64),
                    800 => CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SWORD)->setCount(1),
                    750 => CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HAMMER)->setCount(1),
                    700 => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL)->setCount(4),
                    650 => CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE)->setCount(32),
                    600 => CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE)->setCount(6),
                    550 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HAMMER)->setCount(1),
                    500 => CustomiesItemFactory::getInstance()->get(Ids::SULFUR_POWDER)->setCount(64),
                    450 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_LEGGINGS)->setCount(1),
                    8 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HELMET)->setCount(1),
                    7 => CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_PLATINUM)->setCount(1),
                    6 => CustomiesItemFactory::getInstance()->get(Ids::KEY_MYTHICAL)->setCount(2),
                ];
            case self::MYTHICAL:
                return [
                    1001 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT)->setCount(32),
                    900 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD)->setCount(1),
                    850 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HAMMER)->setCount(1),
                    800 => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL)->setCount(32),
                    750 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_DYNAMITE)->setCount(16),
                    700 => CustomiesItemFactory::getInstance()->get(Ids::KEY_FORTUNE)->setCount(5),
                    550 => CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_PLATINUM)->setCount(1),
                    400 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT)->setCount(64),
                    350 => CustomiesItemFactory::getInstance()->get(Ids::WATER_DYNAMITE)->setCount(64),
                    8 => VanillaBlocks::MONSTER_SPAWNER()->asItem(),
                    4 => CustomiesItemFactory::getInstance()->get(Ids::KEY_FORTUNE)->setCount(8),
                    3 => CustomiesItemFactory::getInstance()->get(BlockIds::AMETHYST_CHEST)->setCount(6),
                    2 => CustomiesItemFactory::getInstance()->get(Ids::KEY_LEGENDARY)->setCount(2),
                ];
            case self::LEGENDARY:
                $helmet = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HELMET);
                $chestplate = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_CHESTPLATE);
                $leggings = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_LEGGINGS);
                $boots = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_BOOTS);

                $helmet->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $helmet->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));

                $chestplate->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $chestplate->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));

                $leggings->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $leggings->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));

                $boots->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                $boots->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));



                return [
                    1001 => CustomiesItemFactory::getInstance()->get(BlockIds::OBSIDIAN_PLATINUM)->setCount(64),
                    900 => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_DYNAMITE)->setCount(64),
                    850 => CustomiesItemFactory::getInstance()->get(Ids::WATER_DYNAMITE)->setCount(64),
                    800 => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_HEAL)->setCount(64),
                    600 => CustomiesItemFactory::getInstance()->get(Ids::KEY_MYTHICAL)->setCount(6),
                    550 => CustomiesItemFactory::getInstance()->get(Ids::EGG_CREEPER)->setCount(4),
                    400 => CustomiesItemFactory::getInstance()->get(Ids::EGG_SKELETON)->setCount(4),
                    200 => CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->setCount(64),
                    100 => $helmet,
                    58 => $chestplate,
                    54 => $leggings,
                    52 => $boots,
                    6 => VanillaBlocks::MONSTER_SPAWNER()->asItem()->setCount(3),
                    4 => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED)->setCount(64),
                    3 => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE)->setCount(64),
                    2 => CustomiesItemFactory::getInstance()->get(Ids::KEY_LEGENDARY)->setCount(12),
                ];
            case self::BLACK_GOLD:
                return [
                    1001 => CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT)->setCount(32),
                    900 => CustomiesItemFactory::getInstance()->get(BlockIds::GOLD_BLOCK)->setCount(16),
                    850 => CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_CHEST)->setCount(64),
                    800 => CustomiesItemFactory::getInstance()->get(Ids::GOLD_SWORD)->setCount(1),
                    750 => CustomiesItemFactory::getInstance()->get(Ids::GOLD_HAMMER)->setCount(1),
                    700 => CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL)->setCount(64),
                    650 => CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE)->setCount(64),
                    600 => CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE)->setCount(64),
                    550 => CustomiesItemFactory::getInstance()->get(Ids::GOLD_HAMMER)->setCount(1),
                    500 => CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT)->setCount(64),
                    450 => CustomiesItemFactory::getInstance()->get(Ids::BUCKET_GOLD_EMPTY)->setCount(1),
                    400 => CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_GOLD)->setCount(1),
                    350 => CustomiesItemFactory::getInstance()->get(Ids::KEY_LEGENDARY)->setCount(64),
                    300 => CustomiesItemFactory::getInstance()->get(Ids::KEY_MYTHICAL)->setCount(64),
                    4 => CustomiesItemFactory::getInstance()->get(Ids::EGG_CREEPER)->setCount(64),
                    3  => CustomiesItemFactory::getInstance()->get(Ids::EGG_COCHON)->setCount(64),
                    2 => CustomiesItemFactory::getInstance()->get(Ids::EGG_ZOMBIE)->setCount(64),
                ];
        }
    }
}