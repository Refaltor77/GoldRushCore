<?php

namespace core\blocks;

use core\blocks\alcool\Distillerie;
use core\blocks\blocks\AlambicCustom;
use core\blocks\blocks\AmethystBlock;
use core\blocks\blocks\AnvilBlock;
use core\blocks\blocks\biomes\spectral\SpectralBlockOre;
use core\blocks\blocks\biomes\spectral\SpectralCobblestone;
use core\blocks\blocks\biomes\spectral\SpectralDirt;
use core\blocks\blocks\biomes\spectral\SpectralFarmland;
use core\blocks\blocks\biomes\spectral\SpectralFarmlandWet;
use core\blocks\blocks\biomes\spectral\SpectralGrass;
use core\blocks\blocks\biomes\spectral\SpectralGrassPath;
use core\blocks\blocks\biomes\spectral\SpectralLeaves;
use core\blocks\blocks\biomes\spectral\SpectralLog;
use core\blocks\blocks\biomes\spectral\SpectralOre;
use core\blocks\blocks\biomes\spectral\SpectralPlanks;
use core\blocks\blocks\biomes\spectral\SpectralSaplingPlacer;
use core\blocks\blocks\biomes\spectral\SpectralStone;
use core\blocks\blocks\biomes\spectral\StrippedSpectralLog;
use core\blocks\blocks\BlockJump;
use core\blocks\blocks\chest\AmethystChest;
use core\blocks\blocks\chest\AmethystChestLocked;
use core\blocks\blocks\chest\EmeraldChest;
use core\blocks\blocks\chest\EmeraldChestLocked;
use core\blocks\blocks\chest\PlatinumChest;
use core\blocks\blocks\chest\PlatinumChestLocked;
use core\blocks\blocks\CobbleCompressed;
use core\blocks\blocks\CopperBlock;
use core\blocks\blocks\DirtCompressed;
use core\blocks\blocks\EasterEgg;
use core\blocks\blocks\EmeraldBlock;
use core\blocks\blocks\GoldBlock;
use core\blocks\blocks\head\AchedonHead;
use core\blocks\blocks\head\FlolmHead;
use core\blocks\blocks\head\GuridoHead;
use core\blocks\blocks\head\kiganeHead;
use core\blocks\blocks\head\KyruuHead;
use core\blocks\blocks\head\OneupHead;
use core\blocks\blocks\head\RefaltorHead;
use core\blocks\blocks\head\TheoHead;
use core\blocks\blocks\luckyblock\Luckyblock;
use core\blocks\blocks\MonsterSpawner;
use core\blocks\blocks\obsidian\ObsidianAmethyst;
use core\blocks\blocks\obsidian\ObsidianBasic;
use core\blocks\blocks\obsidian\ObsidianEmerald;
use core\blocks\blocks\obsidian\ObsidianPlatinum;
use core\blocks\blocks\PlatinumBlock;
use core\blocks\containers\Barrel;
use core\blocks\crops\BerryBlack;
use core\blocks\crops\BerryBlue;
use core\blocks\crops\BerryPink;
use core\blocks\crops\BerryYellow;
use core\blocks\crops\FlowerPercent;
use core\blocks\crops\ObsidianCrops;
use core\blocks\crops\RaisinCrops;
use core\blocks\crops\vanilla\BeetrootsCustom;
use core\blocks\crops\vanilla\CactusCustom;
use core\blocks\crops\vanilla\CarrotsCustom;
use core\blocks\crops\vanilla\MelonCustom;
use core\blocks\crops\vanilla\PotatoesCustom;
use core\blocks\crops\vanilla\PumpkinCustom;
use core\blocks\crops\vanilla\Sugarcanne;
use core\blocks\crops\vanilla\WheatCustom;
use core\blocks\ores\AmethystOre;
use core\blocks\ores\CopperOre;
use core\blocks\ores\deepslates\DeepslateAmethystOre;
use core\blocks\ores\deepslates\DeepslateCoal;
use core\blocks\ores\deepslates\DeepslateCopperOre;
use core\blocks\ores\deepslates\DeepslateDiamond;
use core\blocks\ores\deepslates\DeepslateEmeraldOre;
use core\blocks\ores\deepslates\DeepslateGoldOre;
use core\blocks\ores\deepslates\DeepslateIron;
use core\blocks\ores\deepslates\DeepslateLapis;
use core\blocks\ores\deepslates\DeepslatePlatinum;
use core\blocks\ores\deepslates\DeepslateRedstone;
use core\blocks\ores\EmeraldOre;
use core\blocks\ores\FossilOre;
use core\blocks\ores\GoldOre;
use core\blocks\ores\PlatinumOre;
use core\blocks\ores\SulfurOre;
use core\blocks\ores\vanilla\CoalCustom;
use core\blocks\ores\vanilla\DiamondCustom;
use core\blocks\ores\vanilla\IronCustom;
use core\blocks\ores\vanilla\LapisCustom;
use core\blocks\ores\vanilla\RedstoneCustom;
use core\blocks\tiles\AlambicTile;
use core\blocks\tiles\AmethystChestTile;
use core\blocks\tiles\BarrelTile;
use core\blocks\tiles\DistillerieTile;
use core\blocks\tiles\EmeraldChestTile;
use core\blocks\tiles\FlowerPercentTile;
use core\blocks\tiles\MobSpawnerTile;
use core\blocks\tiles\PlatinumChestTile;
use core\blocks\vanilla\AnvilCustom;
use core\blocks\vanilla\Bed;
use core\blocks\vanilla\EnchentableTableCustom;
use core\blocks\vanilla\EnderChest;
use core\blocks\vanilla\Farmland;
use core\blocks\vanilla\FireCustom;
use core\blocks\vanilla\GoldOreVanilla;
use core\settings\Ids;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\block\Material;
use customiesdevs\customies\block\Model;
use customiesdevs\customies\item\CreativeInventoryInfo;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\tile\MobHead;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\Transparent;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;

class BlockManager
{
    public function __construct()
    {
        $this->spectralsBlocks();


        $chestInv = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, CreativeInventoryInfo::GROUP_CHEST);



        $hardness = VanillaBlocks::DIAMOND_ORE()->getBreakInfo()->getHardness();
        $hardnessDeepslate = VanillaBlocks::DEEPSLATE()->getBreakInfo()->getHardness();
        $creativeInfoOre = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_NATURE, CreativeInventoryInfo::GROUP_ORE);


        TileFactory::getInstance()->register(BarrelTile::class);
        TileFactory::getInstance()->register(MobSpawnerTile::class);
        TileFactory::getInstance()->register(FlowerPercentTile::class);
        TileFactory::getInstance()->register(EmeraldChestTile::class);
        TileFactory::getInstance()->register(AmethystChestTile::class);
        TileFactory::getInstance()->register(PlatinumChestTile::class);
        TileFactory::getInstance()->register(DistillerieTile::class);

        RuntimeBlockStateRegistry::getInstance()->register(new EnchentableTableCustom(VanillaBlocks::ENCHANTING_TABLE()->getIdInfo(), "Table d'enchant", new BlockTypeInfo(VanillaBlocks::ENCHANTING_TABLE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new EnderChest(VanillaBlocks::ENDER_CHEST()->getIdInfo(), "Ender Chest", new BlockTypeInfo(VanillaBlocks::ENDER_CHEST()->getBreakInfo())));

        RuntimeBlockStateRegistry::getInstance()->register(new ObsidianBasic(VanillaBlocks::OBSIDIAN()->getIdInfo(), "Obsidienne", new BlockTypeInfo(new BlockBreakInfo(VanillaBlocks::OBSIDIAN()->getBreakInfo()->getHardness(), BlockToolType::PICKAXE))));
        RuntimeBlockStateRegistry::getInstance()->register(new BeetrootsCustom(VanillaBlocks::BEETROOTS()->getIdInfo(), "Beterave", new BlockTypeInfo(VanillaBlocks::BEETROOTS()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new CarrotsCustom(VanillaBlocks::CARROTS()->getIdInfo(), "Carottes", new BlockTypeInfo(VanillaBlocks::CARROTS()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new MelonCustom(VanillaBlocks::MELON()->getIdInfo(), "Melon", new BlockTypeInfo(VanillaBlocks::MELON()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new PotatoesCustom(VanillaBlocks::POTATOES()->getIdInfo(), "Potato", new BlockTypeInfo(VanillaBlocks::POTATOES()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new PumpkinCustom(VanillaBlocks::PUMPKIN()->getIdInfo(), "Pumpkin", new BlockTypeInfo(VanillaBlocks::PUMPKIN()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new WheatCustom(VanillaBlocks::WHEAT()->getIdInfo(), "Wheat", new BlockTypeInfo(VanillaBlocks::WHEAT()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new CactusCustom(VanillaBlocks::CACTUS()->getIdInfo(), "Cactus", new BlockTypeInfo(VanillaBlocks::CACTUS()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new Sugarcanne(VanillaBlocks::SUGARCANE()->getIdInfo(), "Sugarcane", new BlockTypeInfo(VanillaBlocks::SUGARCANE()->getBreakInfo())));

        RuntimeBlockStateRegistry::getInstance()->register(new GoldOreVanilla(VanillaBlocks::GOLD_ORE()->getIdInfo(), "Gold Ore", new BlockTypeInfo(VanillaBlocks::GOLD_ORE()->getBreakInfo())));




        RuntimeBlockStateRegistry::getInstance()->register(new Bed(VanillaBlocks::BED()->getIdInfo(), "Bed", new BlockTypeInfo(VanillaBlocks::BED()->getBreakInfo())));


        RuntimeBlockStateRegistry::getInstance()->register(new CoalCustom(VanillaBlocks::COAL_ORE()->getIdInfo(), "Charbon", new BlockTypeInfo(VanillaBlocks::COAL_ORE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new DiamondCustom(VanillaBlocks::DIAMOND_ORE()->getIdInfo(), "Diamant", new BlockTypeInfo(VanillaBlocks::DIAMOND_ORE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new IronCustom(VanillaBlocks::IRON_ORE()->getIdInfo(), "Fer", new BlockTypeInfo(VanillaBlocks::IRON_ORE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new LapisCustom(VanillaBlocks::LAPIS_LAZULI_ORE()->getIdInfo(), "Lapis", new BlockTypeInfo(VanillaBlocks::LAPIS_LAZULI_ORE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new RedstoneCustom(VanillaBlocks::REDSTONE_ORE()->getIdInfo(), "Redstone", new BlockTypeInfo(VanillaBlocks::REDSTONE_ORE()->getBreakInfo())));



        RuntimeBlockStateRegistry::getInstance()->register(new DeepslateCoal(VanillaBlocks::DEEPSLATE_COAL_ORE()->getIdInfo(), "Charbon", new BlockTypeInfo(VanillaBlocks::DEEPSLATE_COAL_ORE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new DeepslateDiamond(VanillaBlocks::DEEPSLATE_DIAMOND_ORE()->getIdInfo(), "Diamant", new BlockTypeInfo(VanillaBlocks::DEEPSLATE_DIAMOND_ORE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new DeepslateIron(VanillaBlocks::DEEPSLATE_IRON_ORE()->getIdInfo(), "Fer", new BlockTypeInfo(VanillaBlocks::DEEPSLATE_IRON_ORE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new DeepslateLapis(VanillaBlocks::DEEPSLATE_LAPIS_LAZULI_ORE()->getIdInfo(), "Lapis", new BlockTypeInfo(VanillaBlocks::DEEPSLATE_LAPIS_LAZULI_ORE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new DeepslateRedstone(VanillaBlocks::DEEPSLATE_REDSTONE_ORE()->getIdInfo(), "Redstone", new BlockTypeInfo(VanillaBlocks::DEEPSLATE_REDSTONE_ORE()->getBreakInfo())));



        $amethystMaterial = new Material(Material::TARGET_ALL, "amethyst_ore", Material::RENDER_METHOD_OPAQUE, true, true);
        $amethystModel = new Model([$amethystMaterial], "geometry.amethyst_ore", new Vector3(-8, 0, -8), new Vector3(16, 16, 16), true);


        $emeraldMaterial = new Material(Material::TARGET_ALL, "emerald_ore_custom", Material::RENDER_METHOD_OPAQUE, true, true);
        $emeraldModel = new Model([$emeraldMaterial], "geometry.emerald_ore", new Vector3(-8, 0, -8), new Vector3(16, 16, 16), true);

        $goldMaterial = new Material(Material::TARGET_ALL, "gold_ore_custom", Material::RENDER_METHOD_OPAQUE, true, true);
        $goldModel = new Model([$goldMaterial], "geometry.gold_ore", new Vector3(-8, 0, -8), new Vector3(16, 16, 16), true);


        $platinumMaterial = new Material(Material::TARGET_ALL, "platinum_ore", Material::RENDER_METHOD_OPAQUE, true, true);
        $platinumModel = new Model([$platinumMaterial], "geometry.platinum_ore", new Vector3(-8, 0, -8), new Vector3(16, 16, 16), true);


        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SulfurOre(new BlockIdentifier(BlockTypeIds::newId()), "sulfur_ore", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:sulfur_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new CopperOre(new BlockIdentifier(BlockTypeIds::newId()), "copper_ore", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:copper_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new AmethystOre(new BlockIdentifier(BlockTypeIds::newId()), "amethyst_ore", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:amethyst_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new EmeraldOre(new BlockIdentifier(BlockTypeIds::newId()), "emerald_ore", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:emerald_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new GoldOre(new BlockIdentifier(BlockTypeIds::newId()), "gold_ore", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:gold_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new PlatinumOre(new BlockIdentifier(BlockTypeIds::newId()), "platinum_ore", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:platinum_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new FossilOre(new BlockIdentifier(BlockTypeIds::newId()), "fossil_ore", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:fossil_ore", null, $creativeInfoOre);







        $amethystMaterial = new Material(Material::TARGET_ALL, "amethyst_ore", Material::RENDER_METHOD_OPAQUE, true, true);
        $amethystModel = new Model([$amethystMaterial], "geometry.amethyst_ore", new Vector3(-8, 0, -8), new Vector3(16, 16, 16), true);


        $emeraldMaterial = new Material(Material::TARGET_ALL, "emerald_ore", Material::RENDER_METHOD_OPAQUE, true, true);
        $emeraldModel = new Model([$emeraldMaterial], "geometry.emerald_ore", new Vector3(-8, 0, -8), new Vector3(16, 16, 16), true);


        $goldMaterial = new Material(Material::TARGET_ALL, "gold_ore", Material::RENDER_METHOD_OPAQUE, true, true);
        $goldModel = new Model([$goldMaterial], "geometry.gold_ore", new Vector3(-8, 0, -8), new Vector3(16, 16, 16), true);


        $platinumMaterial = new Material(Material::TARGET_ALL, "platinum_ore", Material::RENDER_METHOD_OPAQUE, true, true);
        $platinumModel = new Model([$platinumMaterial], "geometry.platinum_ore", new Vector3(-8, 0, -8), new Vector3(16, 16, 16), true);


        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new DeepslateAmethystOre(new BlockIdentifier(BlockTypeIds::newId()), "amethyst_ore", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:deepslate_amethyst_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new DeepslateCopperOre(new BlockIdentifier(BlockTypeIds::newId()), "copper_ore", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:deepslate_copper_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new DeepslateEmeraldOre(new BlockIdentifier(BlockTypeIds::newId()), "emerald_ore", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:deepslate_emerald_ore", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new DeepslateGoldOre(new BlockIdentifier(BlockTypeIds::newId()), "gold_ore", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:deepslate_gold_ore" ,null,  $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new DeepslatePlatinum(new BlockIdentifier(BlockTypeIds::newId()), "platinum_ore", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:deepslate_platinum_ore", null, $creativeInfoOre);


        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new AmethystBlock(new BlockIdentifier(BlockTypeIds::newId()), "amethyst_block", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:amethyst_block", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new CopperBlock(new BlockIdentifier(BlockTypeIds::newId()), "copper_block", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:copper_block", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new EmeraldBlock(new BlockIdentifier(BlockTypeIds::newId()), "emerald_block", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:emerald_block", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new GoldBlock(new BlockIdentifier(BlockTypeIds::newId()), "gold_block", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:gold_block", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new PlatinumBlock(new BlockIdentifier(BlockTypeIds::newId()), "platinum_block", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:platinum_block", null, $creativeInfoOre);


        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new BlockJump(new BlockIdentifier(BlockTypeIds::newId()), "platinum_block", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), "goldrush:jump_block", null, $creativeInfoOre);


        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new ObsidianEmerald(new BlockIdentifier(BlockTypeIds::newId()), "obsidian_emerald", new BlockTypeInfo(new BlockBreakInfo(VanillaBlocks::OBSIDIAN()->getBreakInfo()->getHardness(), BlockToolType::PICKAXE))), "goldrush:obsidian_emerald", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new ObsidianAmethyst(new BlockIdentifier(BlockTypeIds::newId()), "obsidian_amethyst", new BlockTypeInfo(new BlockBreakInfo(VanillaBlocks::OBSIDIAN()->getBreakInfo()->getHardness(), BlockToolType::PICKAXE))), "goldrush:obsidian_amethyst", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new ObsidianPlatinum(new BlockIdentifier(BlockTypeIds::newId()), "obsidian_platinum", new BlockTypeInfo(new BlockBreakInfo(VanillaBlocks::OBSIDIAN()->getBreakInfo()->getHardness(), BlockToolType::PICKAXE))), "goldrush:obsidian_platinum", null, $creativeInfoOre);



        $creativeWood = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_NATURE, CreativeInventoryInfo::GROUP_LOG);
        $creativePlanks = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, CreativeInventoryInfo::GROUP_PLANKS);
        $creativeLeave = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_NATURE, CreativeInventoryInfo::GROUP_LEAVES);
        $creativeOre = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_NATURE, CreativeInventoryInfo::GROUP_ORE);
        $creativeBlockOre = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_CONSTRUCTION);





        $cropsBlockVariants = [0 => Ids::RAISIN_CROPS_STAGE_0, 1 => Ids::RAISIN_CROPS_STAGE_1, 2 => Ids::RAISIN_CROPS_STAGE_2, 3 => Ids::RAISIN_CROPS_STAGE_3];
        foreach ($cropsBlockVariants as $cropsBlockVariant => $ids) {
            $cropsBlockMaterial = new Material(Material::TARGET_ALL, "raisin_crops_stage_" . $cropsBlockVariant, Material::RENDER_METHOD_ALPHA_TEST, false, false);
            $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.culture_1", new Vector3(-8, -1, -8), new Vector3(16, 12, 16), false);
            CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new RaisinCrops(new BlockIdentifier(BlockTypeIds::newId()), "raisin crops stage " . $cropsBlockVariant, new BlockTypeInfo(BlockBreakInfo::instant()), $cropsBlockVariant), $ids,  $cropsBlockModel, null);
        }

        $cropsBlockVariants = [0 => Ids::BERRY_BLUE_CROPS_STAGE_0, 1 => Ids::BERRY_BLUE_CROPS_STAGE_1, 2 => Ids::BERRY_BLUE_CROPS_STAGE_2, 3 => Ids::BERRY_BLUE_CROPS_STAGE_3];
        foreach ($cropsBlockVariants as $cropsBlockVariant => $ids) {
            $cropsBlockMaterial = new Material(Material::TARGET_ALL, "berry_blue_crops_stage_" . $cropsBlockVariant, Material::RENDER_METHOD_ALPHA_TEST, false, false);
            $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.culture_1", new Vector3(-8, -1, -8), new Vector3(16, 12, 16), false);

            CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new BerryBlue(new BlockIdentifier(BlockTypeIds::newId()), "berry blue crops stage " . $cropsBlockVariant, new BlockTypeInfo(BlockBreakInfo::instant()), $cropsBlockVariant), $ids,  $cropsBlockModel, null);
        }


        $cropsBlockVariants = [0 => Ids::BERRY_PINK_CROPS_STAGE_0, 1 => Ids::BERRY_PINK_CROPS_STAGE_1, 2 => Ids::BERRY_PINK_CROPS_STAGE_2, 3 => Ids::BERRY_PINK_CROPS_STAGE_3];
        foreach ($cropsBlockVariants as $cropsBlockVariant => $ids) {
            $cropsBlockMaterial = new Material(Material::TARGET_ALL, "berry_pink_crops_stage_" . $cropsBlockVariant, Material::RENDER_METHOD_ALPHA_TEST, false, false);
            $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.culture_1", new Vector3(-8, -1, -8), new Vector3(16, 12, 16), false);

            CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new BerryPink(new BlockIdentifier(BlockTypeIds::newId()), "berry pink crops stage " . $cropsBlockVariant, new BlockTypeInfo(BlockBreakInfo::instant()), $cropsBlockVariant), $ids,  $cropsBlockModel, null);
        }

        $cropsBlockVariants = [0 => Ids::BERRY_BLACK_CROPS_STAGE_0, 1 => Ids::BERRY_BLACK_CROPS_STAGE_1, 2 => Ids::BERRY_BLACK_CROPS_STAGE_2, 3 => Ids::BERRY_BLACK_CROPS_STAGE_3];
        foreach ($cropsBlockVariants as $cropsBlockVariant => $ids) {
            $cropsBlockMaterial = new Material(Material::TARGET_ALL, "berry_black_crops_stage_" . $cropsBlockVariant, Material::RENDER_METHOD_ALPHA_TEST, false, false);
            $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.culture_1", new Vector3(-8, -1, -8), new Vector3(16, 12, 16), false);

            CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new BerryBlack(new BlockIdentifier(BlockTypeIds::newId()), "berry black crops stage " . $cropsBlockVariant, new BlockTypeInfo(BlockBreakInfo::instant()), $cropsBlockVariant), $ids,  $cropsBlockModel, null);
        }


        $cropsBlockVariants = [0 => Ids::BERRY_YELLOW_CROPS_STAGE_0, 1 => Ids::BERRY_YELLOW_CROPS_STAGE_1, 2 => Ids::BERRY_YELLOW_CROPS_STAGE_2, 3 => Ids::BERRY_YELLOW_CROPS_STAGE_3];
        foreach ($cropsBlockVariants as $cropsBlockVariant => $ids) {
            $cropsBlockMaterial = new Material(Material::TARGET_ALL, "berry_yellow_crops_stage_" . $cropsBlockVariant, Material::RENDER_METHOD_ALPHA_TEST, false, false);
            $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.culture_1", new Vector3(-8, -1, -8), new Vector3(16, 12, 16), false);

            CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new BerryYellow(new BlockIdentifier(BlockTypeIds::newId()), "berry yellow crops stage " . $cropsBlockVariant, new BlockTypeInfo(BlockBreakInfo::instant()), $cropsBlockVariant), $ids,  $cropsBlockModel, null);
        }



        $cropsBlockVariants = [0 => Ids::WHEAT_OBSIDIAN_STAGE_0, 1 => Ids::WHEAT_OBSIDIAN_STAGE_1, 2 => Ids::WHEAT_OBSIDIAN_STAGE_2, 3 => Ids::WHEAT_OBSIDIAN_STAGE_3];
        foreach ($cropsBlockVariants as $cropsBlockVariant => $ids) {
            $cropsBlockMaterial = new Material(Material::TARGET_ALL, "wheat_obsidian_stage_" . $cropsBlockVariant, Material::RENDER_METHOD_ALPHA_TEST, false, false);
            $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.culture_1", new Vector3(-8, -1, -8), new Vector3(16, 12, 16), false);

            CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new ObsidianCrops(new BlockIdentifier(BlockTypeIds::newId()), "wheat obisdian stage " . $cropsBlockVariant, new BlockTypeInfo(BlockBreakInfo::instant()), $cropsBlockVariant), $ids,  $cropsBlockModel, null);
        }


        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "flower_percent", Material::RENDER_METHOD_ALPHA_TEST, false, false);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.flower", new Vector3(-3, -1, -3), new Vector3(7, 15, 7), false);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new FlowerPercent(new BlockIdentifier(BlockTypeIds::newId(), FlowerPercentTile::class), "flower percent", new BlockTypeInfo(BlockBreakInfo::instant())), 'goldrush:flower_percent',  $cropsBlockModel, $creativeLeave);


        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "distillerie", Material::RENDER_METHOD_ALPHA_TEST, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.distillerie", new Vector3(-4, 0, -4), new Vector3(8, 16, 8), true);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new Distillerie(new BlockIdentifier(BlockTypeIds::newId(), DistillerieTile::class), "distillerie", new BlockTypeInfo(BlockBreakInfo::pickaxe(VanillaBlocks::STONE()->getBreakInfo()->getHardness()))), 'goldrush:distillerie',  $cropsBlockModel, $creativeLeave);



        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "emerald_chest", Material::RENDER_METHOD_OPAQUE, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.chest.base", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new EmeraldChest(new BlockIdentifier(BlockTypeIds::newId(), EmeraldChestTile::class), "emerald chest", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), 'goldrush:emerald_chest',  $cropsBlockModel, $chestInv);

        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "emerald_chest_lock", Material::RENDER_METHOD_OPAQUE, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.chest.lock", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new EmeraldChestLocked(new BlockIdentifier(BlockTypeIds::newId(), EmeraldChestTile::class), "emerald chest lock", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), 'goldrush:emerald_chest_lock',  $cropsBlockModel, $chestInv);


        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "amethyst_chest", Material::RENDER_METHOD_OPAQUE, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.chest.base", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new AmethystChest(new BlockIdentifier(BlockTypeIds::newId(), AmethystChestTile::class), "amethyst chest", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), 'goldrush:amethyst_chest',  $cropsBlockModel, $chestInv);

        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "amethyst_chest_lock", Material::RENDER_METHOD_OPAQUE, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.chest.lock", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new AmethystChestLocked(new BlockIdentifier(BlockTypeIds::newId(), AmethystChestTile::class), "amethyst chest lock", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), 'goldrush:amethyst_chest_lock',  $cropsBlockModel, $chestInv);


        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "platinum_chest", Material::RENDER_METHOD_OPAQUE, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.chest.base", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new PlatinumChest(new BlockIdentifier(BlockTypeIds::newId(), PlatinumChestTile::class), "platinum chest", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), 'goldrush:platinum_chest',  $cropsBlockModel, $chestInv);


        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "platinum_chest_lock", Material::RENDER_METHOD_OPAQUE, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.chest.lock", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new PlatinumChestLocked(new BlockIdentifier(BlockTypeIds::newId(), PlatinumChestTile::class), "platinum chest locked", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), 'goldrush:platinum_chest_lock',  $cropsBlockModel, $chestInv);



        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new DirtCompressed(new BlockIdentifier(BlockTypeIds::newId()), "dirt compressed", new BlockTypeInfo(VanillaBlocks::DIRT()->getBreakInfo())), 'goldrush:dirt_compressed',  null, $creativeLeave);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new CobbleCompressed(new BlockIdentifier(BlockTypeIds::newId()), "cobble compressed", new BlockTypeInfo(VanillaBlocks::COBBLESTONE()->getBreakInfo())), 'goldrush:cobble_compressed',  null, $creativeLeave);


        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "luckyblock", Material::RENDER_METHOD_OPAQUE, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.luckyblock", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new Luckyblock(new BlockIdentifier(BlockTypeIds::newId()), "luckyblock", new BlockTypeInfo(new BlockBreakInfo(VanillaBlocks::ACACIA_LEAVES()->getBreakInfo()->getHardness(), BlockToolType::NONE))), "goldrush:luckyblock", $cropsBlockModel, $creativeInfoOre);



        $model = new Model([
            new Material(Material::TARGET_ALL, "barrel_custom"),
        ], "geometry.barrel", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new Barrel(new BlockIdentifier(BlockTypeIds::newId(), BarrelTile::class), "barrel", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::AXE))), "goldrush:barrel", $model, new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, CreativeInventoryInfo::GROUP_CHEST));

        $model = new Model([
            new Material(Material::TARGET_ALL, "easteregg", Material::RENDER_METHOD_OPAQUE, true, true),
        ], "geometry.easteregg", new Vector3(-4, -1, -4), new Vector3(8, 15, 8), true);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new EasterEgg(new BlockIdentifier(BlockTypeIds::newId(), BarrelTile::class), "easteregg", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::AXE))), "goldrush:easteregg", $model, $creativeInfoOre);


        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new AchedonHead(new BlockIdentifier(BlockTypeIds::newId(),MobHead::class), "achedon_head", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:achedon_head", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new FlolmHead(new BlockIdentifier(BlockTypeIds::newId(),MobHead::class), "flolm_head", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:flolm_head", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new GuridoHead(new BlockIdentifier(BlockTypeIds::newId(),MobHead::class), "gurido_head", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:gurido_head", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new kiganeHead(new BlockIdentifier(BlockTypeIds::newId(),MobHead::class), "kigane_head", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:kigane_head", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new KyruuHead(new BlockIdentifier(BlockTypeIds::newId(),MobHead::class), "kyruu_head", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:kyruu_head", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new OneupHead(new BlockIdentifier(BlockTypeIds::newId(),MobHead::class), "oneup_head", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:oneup_head", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new RefaltorHead(new BlockIdentifier(BlockTypeIds::newId(),MobHead::class), "refaltor_head", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:refaltor_head", null, $creativeInfoOre);
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new TheoHead(new BlockIdentifier(BlockTypeIds::newId(),MobHead::class), "theo_head", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:theo_head", null, $creativeInfoOre);


        RuntimeBlockStateRegistry::getInstance()->register(new MonsterSpawner(new BlockIdentifier(VanillaBlocks::MONSTER_SPAWNER()->getTypeId(), MobSpawnerTile::class), "Spawner", new BlockTypeInfo(VanillaBlocks::MONSTER_SPAWNER()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new FireCustom(new BlockIdentifier(VanillaBlocks::FIRE()->getTypeId()), "Fire", new BlockTypeInfo(VanillaBlocks::FIRE()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new AnvilCustom(new BlockIdentifier(VanillaBlocks::ANVIL()->getTypeId()), "Anvil", new BlockTypeInfo(VanillaBlocks::ANVIL()->getBreakInfo())));
        RuntimeBlockStateRegistry::getInstance()->register(new Farmland(new BlockIdentifier(VanillaBlocks::FARMLAND()->getTypeId()), "Farmland", new BlockTypeInfo(VanillaBlocks::FARMLAND()->getBreakInfo())));

        $cropsBlockMaterial = new Material(Material::TARGET_ALL, "socle", Material::RENDER_METHOD_OPAQUE, true, true);
        $cropsBlockModel = new Model([$cropsBlockMaterial], "geometry.socle", new Vector3(-7, -1, -7), new Vector3(14, 3, 14), true);

        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new TransparentPermutable(new BlockIdentifier(BlockTypeIds::newId()), "socle", new BlockTypeInfo(new BlockBreakInfo($hardnessDeepslate, BlockToolType::PICKAXE))), 'goldrush:socle',  $cropsBlockModel, $chestInv);


    }





    private function spectralsBlocks(): void {

        $creative = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_NATURE, CreativeInventoryInfo::NONE);


        $hardness = VanillaBlocks::DIAMOND()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralBlockOre(new BlockIdentifier(BlockTypeIds::newId()), "spectral_block", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:spectral_block", null, $creative);

        $hardness = VanillaBlocks::DIRT()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralDirt(new BlockIdentifier(BlockTypeIds::newId()), "spectral_dirt", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::SHOVEL))), "goldrush:spectral_dirt", null, $creative);


        $materialFarmland = new Material(Material::TARGET_ALL, "spectral_dirt_farmland", Material::RENDER_METHOD_ALPHA_TEST, true, true);
        $modelFarmlands = new Model([$materialFarmland], "geometry.farmland", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);
        $hardness = VanillaBlocks::DIRT()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralFarmland(new BlockIdentifier(BlockTypeIds::newId()), "spectral_dirt_farmland", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::SHOVEL))), "goldrush:spectral_dirt_farmland", $modelFarmlands, $creative);

        $materialFarmland = new Material(Material::TARGET_ALL, "spectral_dirt_farmland_wet", Material::RENDER_METHOD_ALPHA_TEST, true, true);
        $modelFarmlands = new Model([$materialFarmland], "geometry.farmland", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);

        $hardness = VanillaBlocks::DIRT()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralFarmlandWet(new BlockIdentifier(BlockTypeIds::newId()), "spectral_dirt_farmland_wet", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::SHOVEL))), "goldrush:spectral_dirt_farmland_wet", $modelFarmlands, $creative);

        $hardness = VanillaBlocks::GRASS()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralGrass(new BlockIdentifier(BlockTypeIds::newId()), "spectral_grass", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::SHOVEL))), "goldrush:spectral_grass", null, $creative);

        $materialFarmland = new Material(Material::TARGET_SIDES, "spectral_grass_path_side", Material::RENDER_METHOD_ALPHA_TEST, true, true);
        $down = new Material(Material::TARGET_DOWN, "spectral_dirt", Material::RENDER_METHOD_ALPHA_TEST, true, true);
        $up = new Material(Material::TARGET_UP, "spectral_grass_path_top", Material::RENDER_METHOD_ALPHA_TEST, true, true);
        $modelFarmlands = new Model([$materialFarmland, $down, $up], "geometry.farmland", new Vector3(-8, -1, -8), new Vector3(16, 16, 16), true);


        $hardness = VanillaBlocks::GRASS_PATH()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralGrassPath(new BlockIdentifier(BlockTypeIds::newId()), "spectral_grass_path", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::SHOVEL))), "goldrush:spectral_grass_path", $modelFarmlands, $creative);

        $materialFarmland = new Material(Material::TARGET_ALL, "spectral_leaves", Material::RENDER_METHOD_ALPHA_TEST, true, true);
        $modelFarmlands = new Model([$materialFarmland]);

        $hardness = VanillaBlocks::ACACIA_LEAVES()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralLeaves(new BlockIdentifier(BlockTypeIds::newId()), "spectral_leaves", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::SHEARS))), "goldrush:spectral_leaves", $modelFarmlands, $creative);

        $hardness = VanillaBlocks::OAK_LOG()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralLog(new BlockIdentifier(BlockTypeIds::newId()), "spectral_log", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::AXE))), "goldrush:spectral_log", null, $creative);

        $hardness = VanillaBlocks::DIAMOND_ORE()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralOre(new BlockIdentifier(BlockTypeIds::newId()), "spectral_ore", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:spectral_ore", null, $creative);

        $hardness = VanillaBlocks::ACACIA_PLANKS()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralPlanks(new BlockIdentifier(BlockTypeIds::newId()), "planks_spectral", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::AXE))), "goldrush:planks_spectral", null, $creative);

        $materialFarmland = new Material(Material::TARGET_ALL, "spectral_sapling", Material::RENDER_METHOD_ALPHA_TEST, true, true);
        $modelFarmlands = new Model([$materialFarmland], "geometry.spectral_sapling", new Vector3(-8, -1, -8), new Vector3(15, 15, 15), true);

        $hardness = VanillaBlocks::ACACIA_SAPLING()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralSaplingPlacer(new BlockIdentifier(BlockTypeIds::newId()), "spectral_sapling_placer", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::NONE))), "goldrush:spectral_sapling_placer", $modelFarmlands, $creative);

        $hardness = VanillaBlocks::STONE()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralStone(new BlockIdentifier(BlockTypeIds::newId()), "spectral_stone", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:spectral_stone", null, $creative);

        $hardness = VanillaBlocks::COBBLESTONE()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new SpectralCobblestone(new BlockIdentifier(BlockTypeIds::newId()), "spectral_cobblestone", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::PICKAXE))), "goldrush:spectral_cobblestone", null, $creative);

        $hardness = VanillaBlocks::OAK_LOG()->getBreakInfo()->getHardness();
        CustomiesBlockFactory::getInstance()->registerBlock(static fn() => new StrippedSpectralLog(new BlockIdentifier(BlockTypeIds::newId()), "stripped_spectral_log", new BlockTypeInfo(new BlockBreakInfo($hardness, BlockToolType::AXE))), "goldrush:stripped_spectral_log", null, $creative);
    }
}