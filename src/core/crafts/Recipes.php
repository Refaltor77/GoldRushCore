<?php

namespace core\crafts;

use core\Main;
use core\settings\BlockIds;
use core\settings\Ids;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\CraftingManager;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\json\ShapelessRecipeData;
use pocketmine\crafting\MetaWildcardRecipeIngredient;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\item\Stick;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class Recipes
{
    public function init(): void
    {




        $this->registerAllTools(
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT),
            VanillaItems::STICK(),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_PICKAXE),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_SWORD),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_HOE),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_SHOVEL),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_AXE),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_HAMMER),
        );
        $this->registerAllArmors(
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_HELMET),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_CHESTPLATE),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_LEGGINGS),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_BOOTS),
        );
        $this->registerBlock(
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT),
            CustomiesBlockFactory::getInstance()->get(BlockIds::COPPER_BLOCK)
        );
        $this->RegisterAllNuggetAndPowder(
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_NUGGET),
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_POWDER)
        );




        $this->registerAllTools(
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT),
            VanillaItems::STICK(),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_PICKAXE),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HOE),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SHOVEL),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_AXE),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HAMMER),
        );
        $this->registerAllArmors(
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HELMET),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_CHESTPLATE),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_LEGGINGS),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_BOOTS),
        );
        $this->registerBlock(
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT),
            CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)
        );

        $this->RegisterAllNuggetAndPowder(
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_NUGGET),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_POWDER)
        );





        $this->registerAllTools(
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT),
            VanillaItems::STICK(),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_PICKAXE),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SWORD),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HOE),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SHOVEL),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_AXE),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HAMMER),
        );
        $this->registerAllArmors(
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HELMET),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_LEGGINGS),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_BOOTS),
        );
        $this->registerBlock(
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT),
            CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)
        );





        $this->registerAllTools(
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT),
            VanillaItems::STICK(),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_PICKAXE),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SWORD),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HOE),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SHOVEL),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_AXE),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HAMMER),
        );
        $this->registerAllArmors(
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HELMET),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_CHESTPLATE),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_LEGGINGS),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_BOOTS),
        );
        $this->registerBlock(
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT),
            CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)
        );





        $this->registerAllTools(
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT),
            VanillaItems::STICK(),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_PICKAXE),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_SWORD),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_HOE),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_SHOVEL),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_AXE),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_HAMMER),
        );
        $this->registerAllArmors(
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_HELMET),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_CHESTPLATE),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_LEGGINGS),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_BOOTS),
        );
        $this->registerBlock(
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT),
            CustomiesBlockFactory::getInstance()->get(BlockIds::GOLD_BLOCK)
        );

        $this->RegisterAllNuggetAndPowder(
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_NUGGET),
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER)
        );









        $air = VanillaBlocks::AIR()->asItem();
        $verre = VanillaBlocks::GLASS()->asItem();


        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT), CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT), CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT),  VanillaItems::COMPASS(), CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT),  CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT), CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_COPPER)]
        ]);


        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI), CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI), CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI)],
            [CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI),  CustomiesItemFactory::getInstance()->get(Ids::EMPTY_BOTTLE), CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI)],
            [CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI),  CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI), CustomiesItemFactory::getInstance()->get(Ids::RAISIN_MOISI)],
            [CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUR)]
        ]);

        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK), CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK), CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK)],
            [CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK),  VanillaItems::AIR(), CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK)],
            [CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK),  VanillaItems::FLINT_AND_STEEL(), CustomiesItemFactory::getInstance()->get(BlockIds::COPPER_BLOCK)],
            [CustomiesItemFactory::getInstance()->get(BlockIds::DISTILLERIE)]
        ]);

        $this->registerCraft([
            [VanillaItems::IRON_INGOT(), VanillaItems::IRON_INGOT(), VanillaItems::IRON_INGOT()],
            [VanillaItems::IRON_INGOT(),  VanillaBlocks::GLASS()->asItem(), VanillaItems::IRON_INGOT()],
            [VanillaItems::IRON_INGOT(),  VanillaItems::REDSTONE_DUST(), VanillaItems::IRON_INGOT()],
            [CustomiesItemFactory::getInstance()->get(Ids::KEYPAD)]
        ]);


        $woods = [
            VanillaBlocks::SPRUCE_LOG(),
            VanillaBlocks::OAK_LOG(),
            VanillaBlocks::MANGROVE_LOG(),
            VanillaBlocks::JUNGLE_LOG(),
            VanillaBlocks::DARK_OAK_LOG(),
            VanillaBlocks::CHERRY_LOG(),
            VanillaBlocks::BIRCH_LOG(),
            VanillaBlocks::ACACIA_LOG()
        ];


        foreach ($woods as $wood) {
            $wood = $wood->asItem();
            $this->registerCraft([
                [$wood, $wood, $wood],
                [$wood,  VanillaBlocks::CHEST()->asItem(), $wood],
                [$wood,  $wood, $wood],
                [CustomiesItemFactory::getInstance()->get(BlockIds::BARREL)]
            ]);
        }


        $this->registerCraft([
            [VanillaItems::IRON_INGOT(), VanillaItems::IRON_INGOT(), VanillaItems::IRON_INGOT()],
            [VanillaItems::IRON_INGOT(),  VanillaBlocks::GLASS()->asItem(), VanillaItems::IRON_INGOT()],
            [VanillaItems::IRON_INGOT(),  VanillaItems::REDSTONE_DUST(), VanillaItems::IRON_INGOT()],
            [CustomiesItemFactory::getInstance()->get(Ids::KEYPAD)]
        ]);


        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT),  VanillaItems::COMPASS(), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT),  CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_EMERALD)]
        ]);


        $this->registerCraft([
            [VanillaBlocks::WOOL()->setColor(DyeColor::BLACK())->asItem(), VanillaBlocks::WOOL()->setColor(DyeColor::BLACK())->asItem(), VanillaBlocks::WOOL()->setColor(DyeColor::BLACK())->asItem()],
            [VanillaBlocks::WOOL()->setColor(DyeColor::BLACK())->asItem(),  CustomiesItemFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK), VanillaBlocks::WOOL()->setColor(DyeColor::BLACK())->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [CustomiesItemFactory::getInstance()->get(Ids::HOOD_HELMET)]
        ]);


        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT),  VanillaItems::COMPASS(), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT),  CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_AMETHYST)]
        ]);



        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT), CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT), CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT),  VanillaItems::COMPASS(), CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT),  CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT), CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_PLATINUM)]
        ]);



        $this->registerCraft([
            [CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->asItem(),  VanillaBlocks::CHEST()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_CHEST)->asItem()]
        ]);




        $this->registerCraft([
            [CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->asItem(),  VanillaBlocks::CHEST()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_CHEST)->asItem()]
        ]);



        $this->registerCraft([
            [CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->asItem(),  VanillaBlocks::CHEST()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK)->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_CHEST)->asItem()]
        ]);



        $this->registerCraft([
            [VanillaBlocks::DIRT()->asItem(), VanillaBlocks::DIRT()->asItem(),  VanillaBlocks::DIRT()->asItem()],
            [VanillaBlocks::DIRT()->asItem(),  VanillaBlocks::DIRT()->asItem(), VanillaBlocks::DIRT()->asItem()],
            [VanillaBlocks::DIRT()->asItem(),  VanillaBlocks::DIRT()->asItem(), VanillaBlocks::DIRT()->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem()]
        ]);

        $this->registerCraft([
            [CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem()],
            [VanillaBlocks::DIRT()->asItem()->setCount(9)]
        ]);




        $this->registerCraft([
            [CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);

        $this->registerCraft([
            [VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), VanillaBlocks::AIR()->asItem()],
            [VanillaBlocks::AIR()->asItem(),  VanillaBlocks::AIR()->asItem(), CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem()],
            [VanillaBlocks::COBBLESTONE()->asItem()->setCount(9)]
        ]);


        $this->registerSmallCraft([
            "a ", "  "
        ], [
            "a" =>  $recipe = new ExactRecipeIngredient(CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem()),
        ], VanillaBlocks::COBBLESTONE()->asItem()->setCount(9));
        $this->registerSmallCraft([
            " a", "  "
        ], [
            "a" => $recipe = new ExactRecipeIngredient(CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem()),
        ], VanillaBlocks::COBBLESTONE()->asItem()->setCount(9));
        $this->registerSmallCraft([
            "  ", "a "
        ], [
            "a" => $recipe = new ExactRecipeIngredient(CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem()),
        ], VanillaBlocks::COBBLESTONE()->asItem()->setCount(9));
        $this->registerSmallCraft([
            "  ", " a"
        ], [
            "a" => $recipe = new ExactRecipeIngredient(CustomiesBlockFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)->asItem()),
        ], VanillaBlocks::COBBLESTONE()->asItem()->setCount(9));



        $this->registerSmallCraft([
            "a ", "  "
        ], [
            "a" =>  $recipe = new ExactRecipeIngredient(CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem()),
        ], VanillaBlocks::DIRT()->asItem()->setCount(9));
        $this->registerSmallCraft([
            " a", "  "
        ], [
            "a" => $recipe = new ExactRecipeIngredient(CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem()),
        ], VanillaBlocks::DIRT()->asItem()->setCount(9));
        $this->registerSmallCraft([
            "  ", "a "
        ], [
            "a" => $recipe = new ExactRecipeIngredient(CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem()),
        ], VanillaBlocks::DIRT()->asItem()->setCount(9));
        $this->registerSmallCraft([
            "  ", " a"
        ], [
            "a" => $recipe = new ExactRecipeIngredient(CustomiesBlockFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->asItem()),
        ], VanillaBlocks::DIRT()->asItem()->setCount(9));





        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT), $air, CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)],
            [$air,  VanillaItems::STICK(), $air],
            [CustomiesItemFactory::getInstance()->get(Ids::FARMTOOLS)]
        ]);




        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT), CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT), CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT),  VanillaItems::COMPASS(), CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT),  CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT), CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT)],
            [CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_GOLD)]
        ]);




        $this->registerCraft([
            [$air, $air, $air],
            [CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT),  $air, CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT)],
            [$air,  CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT), $air],
            [CustomiesItemFactory::getInstance()->get(Ids::BUCKET_COPPER_EMPTY)]
        ]);

        $this->registerCraft([
            [$air, $air, $air],
            [CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT),  $air, CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT)],
            [$air,  CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT), $air],
            [CustomiesItemFactory::getInstance()->get(Ids::BUCKET_PLATINUM_EMPTY)]
        ]);

        $this->registerCraft([
            [$air, $air, $air],
            [CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT),  $air, CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT)],
            [$air,  CustomiesItemFactory::getInstance()->get(Ids::GOLD_INGOT), $air],
            [CustomiesItemFactory::getInstance()->get(Ids::BUCKET_GOLD_EMPTY)]
        ]);


        $this->registerCraft([
            [$verre, $air, $verre],
            [$verre,  $air, $verre],
            [$air,  $verre, $air],
            [CustomiesItemFactory::getInstance()->get(Ids::EMPTY_BOTTLE)]
        ]);





        $air = VanillaBlocks::AIR()->asItem();
        $this->registerCraft([
            [VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem()],
            [VanillaBlocks::OBSIDIAN()->asItem(),  CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT), VanillaBlocks::OBSIDIAN()->asItem()],
            [VanillaBlocks::OBSIDIAN()->asItem(),  VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::OBSIDIAN_EMERALD)->asItem()]
        ]);

        $this->registerCraft([
            [VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem()],
            [VanillaBlocks::OBSIDIAN()->asItem(),  CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT), VanillaBlocks::OBSIDIAN()->asItem()],
            [VanillaBlocks::OBSIDIAN()->asItem(),  VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::OBSIDIAN_AMETHYST)->asItem()]
        ]);

        $this->registerCraft([
            [VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem()],
            [VanillaBlocks::OBSIDIAN()->asItem(),  CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT), VanillaBlocks::OBSIDIAN()->asItem()],
            [VanillaBlocks::OBSIDIAN()->asItem(),  VanillaBlocks::OBSIDIAN()->asItem(), VanillaBlocks::OBSIDIAN()->asItem()],
            [CustomiesBlockFactory::getInstance()->get(BlockIds::OBSIDIAN_PLATINUM)->asItem()]
        ]);

        $powder = CustomiesItemFactory::getInstance()->get(Ids::SULFUR_POWDER);
        $sable = VanillaBlocks::SAND()->asItem();
        $this->registerCraft([
            [$powder, $sable, $powder],
            [$sable,  $powder, $sable],
            [$powder,  $sable, $powder],
            [VanillaItems::GUNPOWDER()->setCount(8)]
        ]);

        $powder = VanillaItems::GUNPOWDER();
        $this->registerCraft([
            [$air, VanillaItems::PAPER(), $air],
            [VanillaItems::PAPER(),  $powder, VanillaItems::PAPER()],
            [VanillaItems::PAPER(),  $powder, VanillaItems::PAPER()],
            [CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)]
        ]);

        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE),  CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE),  CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE)]
        ]);


        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE),  VanillaItems::BUCKET(), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE),  CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::WATER_DYNAMITE)]
        ]);

        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE),  CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE),  CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE)]
        ]);

        $this->registerCraft([
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE),  CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE),  CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE), CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_DYNAMITE)],
            [CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_DYNAMITE)]
        ]);

        $cobblestone = VanillaBlocks::COBBLESTONE()->asItem()->setCount(1);
        $this->registerCraft([
            [$cobblestone,$cobblestone,$cobblestone],
            [$cobblestone,$cobblestone,$cobblestone],
            [$cobblestone,$cobblestone,$cobblestone],
            [CustomiesItemFactory::getInstance()->get(BlockIds::COBBLE_COMPRESSED)]
        ]);
    }


    public function RegisterAllNuggetAndPowder(Item $ingot, Item $nugget, Item $powder): void {
        $air = VanillaBlocks::AIR()->asItem();


        $this->registerCraft([
            [$powder, $powder, $powder],
            [$powder,  $powder, $powder],
            [$powder,  $powder, $powder],
            [$nugget]
        ]);


        $this->registerCraft([
            [$nugget, $nugget, $nugget],
            [$nugget,  $nugget, $nugget],
            [$nugget,  $nugget, $nugget],
            [$ingot]
        ]);

        $this->registerCraft([
            [$ingot, $air, $air],
            [$air, $air, $air],
            [$air, $air, $air],
            [$nugget->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $ingot, $air],
            [$air, $air, $air],
            [$air, $air, $air],
            [$nugget->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $ingot],
            [$air, $air, $air],
            [$air, $air, $air],
            [$nugget->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$ingot, $air, $air],
            [$air, $air, $air],
            [$nugget->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $ingot, $air],
            [$air, $air, $air],
            [$nugget->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $air, $ingot],
            [$air, $air, $air],
            [$nugget->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $air, $air],
            [$ingot, $air, $air],
            [$nugget->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $air, $air],
            [$air, $ingot, $air],
            [$nugget->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $air, $air],
            [$air, $air, $ingot],
            [$nugget->setCount(9)]
        ]);



        $this->registerSmallCraft([
            "a ", "  "
        ], [
            "a" =>  $recipe = new ExactRecipeIngredient($ingot),
        ], $nugget->setCount(9));
        $this->registerSmallCraft([
            " a", "  "
        ], [
            "a" => $recipe = new ExactRecipeIngredient($ingot),
        ], $nugget->setCount(9));
        $this->registerSmallCraft([
            "  ", "a "
        ], [
            "a" => $recipe = new ExactRecipeIngredient($ingot),
        ], $nugget->setCount(9));
        $this->registerSmallCraft([
            "  ", " a"
        ], [
            "a" => $recipe = new ExactRecipeIngredient($ingot),
        ], $nugget->setCount(9));




        $this->registerCraft([
            [$nugget, $air, $air],
            [$air, $air, $air],
            [$air, $air, $air],
            [$powder->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $nugget, $air],
            [$air, $air, $air],
            [$air, $air, $air],
            [$powder->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $nugget],
            [$air, $air, $air],
            [$air, $air, $air],
            [$powder->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$nugget, $air, $air],
            [$air, $air, $air],
            [$powder->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $nugget, $air],
            [$air, $air, $air],
            [$powder->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $air, $nugget],
            [$air, $air, $air],
            [$powder->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $air, $air],
            [$nugget, $air, $air],
            [$powder->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $air, $air],
            [$air, $nugget, $air],
            [$powder->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air, $air, $air],
            [$air, $air, $nugget],
            [$powder->setCount(9)]
        ]);



        $this->registerSmallCraft([
            "a ", "  "
        ], [
            "a" =>  $recipe = new ExactRecipeIngredient($nugget),
        ], $powder->setCount(9));
        $this->registerSmallCraft([
            " a", "  "
        ], [
            "a" => $recipe = new ExactRecipeIngredient($nugget),
        ], $powder->setCount(9));
        $this->registerSmallCraft([
            "  ", "a "
        ], [
            "a" => $recipe = new ExactRecipeIngredient($nugget),
        ], $powder->setCount(9));
        $this->registerSmallCraft([
            "  ", " a"
        ], [
            "a" => $recipe = new ExactRecipeIngredient($nugget),
        ], $powder->setCount(9));
    }


    public function registerBlock(Item $ingot, Block $block): void {

        $air = VanillaBlocks::AIR()->asItem();

        $this->registerCraft([
            [$ingot, $ingot, $ingot],
            [$ingot,  $ingot, $ingot],
            [$ingot,  $ingot, $ingot],
            [$block->asItem()]
        ]);


        $this->registerCraft([
            [$block->asItem(), $air, $air],
            [$air,  $air, $air],
            [$air,  $air, $air],
            [$ingot->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $block->asItem(), $air],
            [$air,  $air, $air],
            [$air,  $air, $air],
            [$ingot->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $block->asItem()],
            [$air,  $air, $air],
            [$air,  $air, $air],
            [$ingot->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$block->asItem(),  $air, $air],
            [$air,  $air, $air],
            [$ingot->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air,  $block->asItem(), $air],
            [$air,  $air, $air],
            [$ingot->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air,  $air, $block->asItem()],
            [$air,  $air, $air],
            [$ingot->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air,  $air, $air],
            [$block->asItem(),  $air, $air],
            [$ingot->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air,  $air, $air],
            [$air,  $block->asItem(), $air],
            [$ingot->setCount(9)]
        ]);
        $this->registerCraft([
            [$air, $air, $air],
            [$air,  $air, $air],
            [$air,  $air, $block->asItem()],
            [$ingot->setCount(9)]
        ]);





        $this->registerCraft(
            [
                [CustomiesItemFactory::getInstance()->get(Ids::GOLD_NUGGET), CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT), CustomiesItemFactory::getInstance()->get(Ids::GOLD_NUGGET)],
                [$air,  VanillaItems::STICK(), $air],
                [$air,  VanillaItems::STICK(), $air],
                [CustomiesItemFactory::getInstance()->get(Ids::PICKAXE_SPAWNER)]
            ]);




        $this->registerSmallCraft([
            "a ", "  "
        ], [
            "a" =>  $recipe = new ExactRecipeIngredient($block->asItem()),
        ], $ingot->setCount(9));
        $this->registerSmallCraft([
            " a", "  "
        ], [
            "a" => $recipe = new ExactRecipeIngredient($block->asItem()),
        ], $ingot->setCount(9));
        $this->registerSmallCraft([
            "  ", "a "
        ], [
            "a" => $recipe = new ExactRecipeIngredient($block->asItem()),
        ], $ingot->setCount(9));
        $this->registerSmallCraft([
            "  ", " a"
        ], [
            "a" => $recipe = new ExactRecipeIngredient($block->asItem()),
        ], $ingot->setCount(9));
    }



    public function registerAllArmors(Item $itemIngot, Item $helmet, Item $chestplate, Item $leggings, Item $boots): void {
        $air = VanillaBlocks::AIR()->asItem();


        $this->registerCraft([
            [$itemIngot, $itemIngot, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$air,  $air, $air],
            [$helmet]
        ]);


        $this->registerCraft([
            [$air, $air, $air],
            [$itemIngot,  $itemIngot, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$helmet]
        ]);



        $this->registerCraft([
            [$itemIngot, $air, $itemIngot],
            [$itemIngot,  $itemIngot, $itemIngot],
            [$itemIngot,  $itemIngot, $itemIngot],
            [$chestplate]
        ]);


        $this->registerCraft([
            [$itemIngot, $itemIngot, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$leggings]
        ]);


        $this->registerCraft([
            [$air, $air, $air],
            [$itemIngot,  $air, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$boots]
        ]);


        $this->registerCraft([
            [$itemIngot, $air, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$air,  $air, $air],
            [$boots]
        ]);
    }





    public function registerAllTools(Item $item, Item $stick, Item $pickaxe, Item $sword, Item $hoe, Item $shovel, Item $axe, Item $hammer): void {
    $air = VanillaBlocks::AIR()->asItem();

    //PICKAXE
    $this->registerCraft(
        [
            [$item, $item, $item],
            [$air,  $stick, $air],
            [$air,  $stick, $air],
            [$pickaxe]
        ]);

    //HAMMER
    $this->registerCraft(
        [
            [$item, $item, $item],
            [$item, $item, $item],
            [$air,  $stick, $air],
            [$hammer]
        ]);

    //HOE
    $this->registerCraft(
        [
            [$air, $item, $item],
            [$air,  $stick, $air],
            [$air,  $stick, $air],
            [$hoe]
        ]);
    $this->registerCraft(
        [
            [$item, $item, $air],
            [$air,  $stick, $air],
            [$air,  $stick, $air],
            [$hoe]
        ]);

    //AXE
    $this->registerCraft(
        [
            [$air, $item, $item],
            [$air, $stick, $item],
            [$air, $stick, $air],
            [$axe]
        ]);
    $this->registerCraft(
        [
            [$item, $item,  $air],
            [$item, $stick,  $air],
            [$air, $stick, $air],
            [$axe]
        ]);

    //SHOVEL
    $this->registerCraft(
        [
            [$air, $item, $air],
            [$air, $stick,  $air],
            [$air, $stick, $air],
            [$shovel]
        ]);

    //SWORD
    $this->registerCraft(
        [
            [$air, $item, $air],
            [$air, $item,  $air],
            [$air, $stick, $air],
            [$sword]
        ]);

    }


    public function registerSmallCraft(array $shape, array $craft, Item $result): void {

        $shape = new ShapedRecipe(
            $shape,
            $craft,
            [$result]
        );


        Server::getInstance()->getCraftingManager()->registerShapedRecipe($shape);
    }


    public function registerCraft(array $craft): void
    {

        # NE PAS SUPPRIMER | CODE POUR GENERER LES IMAGES DE CRAFT

        /*
        @mkdir(Main::getInstance()->getDataFolder() . "crafts/");
        Main::getInstance()->saveResource("crafts/template.png", true);
        $craftTable = imagecreatefrompng(Main::getInstance()->getDataFolder() . "crafts/template.png");


        $i = 0;
        $items = [
           $craft[0][0],
           $craft[0][1],
           $craft[0][2],
           $craft[1][0],
           $craft[1][1],
           $craft[1][2],
           $craft[2][0],
           $craft[2][1],
           $craft[2][2],
        ];





        $craftTable = imagecreatefrompng(Main::getInstance()->getDataFolder() . "crafts/template.png");
        foreach ($items as $item) {
            if ($item instanceof Item) {
                if (!$item->isNull()) {
                    if (method_exists(get_class($item), 'getTextureString') || $item instanceof Stick) {
                        switch ($i) {
                            case 0:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();
                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23, 19, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                            case 1:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();
                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23 + 18 * 1, 19, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                            case 2:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();
                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23 + 18 * 2, 19, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                            case 3:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();
                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23, 19 + 18, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                            case 4:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();
                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23 + 18 * 1, 19 + 18, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                            case 5:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();
                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23 + 18 * 2, 19 + 18, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                            case 6:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();
                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23, 19 + 18 * 2, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                            case 7:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();
                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23 + 18 * 1, 19 + 18 * 2, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                            case 8:
                                try {
                                    if ($item instanceof Stick) {
                                        $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/"  . "stick";
                                    } else $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $item->getTextureString();

                                    $itemImage = imagecreatefrompng($textureItemString . ".png");

                                    imagecopy($craftTable, $itemImage, 23 + 18 * 2, 19 + 18 * 2, 0, 0, imagesx($itemImage), imagesy($itemImage));
                                } catch (\Exception $exception) {
                                    var_dump($exception->getMessage());
                                }
                                break;
                        }
                    }
                }
            }
            $i++;
        }




        $result = $craft[3][0];
        if (method_exists(get_class($result), 'getTextureString')) {
            try {
                $textureItemString = Main::getInstance()->getDataFolder() . "crafts/items/" . $result->getTextureString();
                $itemImage = imagecreatefrompng($textureItemString . ".png");

                imagecopy($craftTable, $itemImage, 29 + 18 * 4, 19 + 18, 0, 0, imagesx($itemImage), imagesy($itemImage));




                imagepng($craftTable, Main::getInstance()->getDataFolder() . "crafts/" . $craft[3][0]->getTextureString() . ".png");
            } catch (\Exception $exception) {
                var_dump($exception->getMessage());
            }
        }


        */





        $shape = ["", "", ""];
        $y = "a";

        $ingredients = [];


        foreach ($craft[0] as $item) {
            if ($item instanceof Item) {
                if ($item->isNull()|| $item->getName() === 'Air') {
                    $shape[0] .= " ";
                } else {
                    $shape[0] .= $y;
                    $count = $item->getCount();
                    if ($count > 1) {
                        $item->setCount(1);
                        $recipe = new ExactRecipeIngredient($item);
                        $recipe->getItem()->setCount($count);
                    } else $recipe = new ExactRecipeIngredient($item);
                    $ingredients[strval($y)] = $recipe;
                    $y++;
                }
            }
        }

        foreach ($craft[1] as $item) {
            if ($item instanceof Item) {
                if ($item->isNull()|| $item->getName() === 'Air') {
                    $shape[1] .= " ";
                } else {
                    $shape[1] .= $y;
                    $count = $item->getCount();
                    if ($count > 1) {
                        $item->setCount(1);
                        $recipe = new ExactRecipeIngredient($item);
                        $recipe->getItem()->setCount($count);
                    } else $recipe = new ExactRecipeIngredient($item);
                    $ingredients[strval($y)] = $recipe;
                    $y++;
                }
            }
        }

        foreach ($craft[2] as $item) {
            if ($item instanceof Item) {
                if ($item->isNull() || $item->getName() === 'Air') {
                    $shape[2] .= " ";
                } else {
                    $shape[2] .= $y;
                    $count = $item->getCount();
                    if ($count > 1) {
                        $item->setCount(1);
                        $recipe = new ExactRecipeIngredient($item);
                        $recipe->getItem()->setCount($count);
                    } else $recipe = new ExactRecipeIngredient($item);
                    $ingredients[strval($y)] = $recipe;
                    $y++;
                }
            }
        }


        $shape = new ShapedRecipe(
            $shape,
            $ingredients,
            [$craft[3][0]]
        );




        Server::getInstance()->getCraftingManager()->registerShapedRecipe($shape);
    }
}