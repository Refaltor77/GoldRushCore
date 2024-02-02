<?php

namespace core\items;

use core\items\armors\others\HoodHelmet;
use core\items\backpacks\BackpackFarm;
use core\items\backpacks\BackpackFossil;
use core\items\backpacks\BackpackOre;
use core\items\bow\IronBow;
use core\items\box\BlackKey;
use core\items\box\BoostKey;
use core\items\box\CommonKey;
use core\items\box\FortuneKey;
use core\items\box\LegendaryKey;
use core\items\box\MythicalKey;
use core\items\box\RareKey;
use core\items\buckets\BucketCopper;
use core\items\buckets\BucketCopperLava;
use core\items\buckets\BucketEmptyCopper;
use core\items\buckets\BucketEmptyGold;
use core\items\buckets\BucketEmptyPlatinum;
use core\items\buckets\BucketGold;
use core\items\buckets\BucketGoldLava;
use core\items\buckets\BucketPlatinum;
use core\items\buckets\BucketPlatinumLava;
use core\items\cosmetics\CosmeticHead;
use core\items\crops\BerryBlack;
use core\items\crops\BerryBlue;
use core\items\crops\BerryPink;
use core\items\crops\BerryYellow;
use core\items\crops\FlowerPercent;
use core\items\crops\Raisin;
use core\items\crops\SeedsObsidian;
use core\items\dynamites\AmethystDynamite;
use core\items\dynamites\BaseDynamite;
use core\items\dynamites\DynamiteWater;
use core\items\dynamites\EmeraldDynamite;
use core\items\dynamites\PlatinumDynamite;
use core\items\egg\EggChicken;
use core\items\egg\EggCow;
use core\items\egg\EggCreeper;
use core\items\egg\EggEnderman;
use core\items\egg\EggMouton;
use core\items\egg\EggPig;
use core\items\egg\EggSkeleton;
use core\items\egg\EggZombie;
use core\items\foods\alcools\AlcoolForce;
use core\items\foods\alcools\AlcoolForcePuissant;
use core\items\foods\alcools\AlcoolHaste;
use core\items\foods\alcools\AlcoolHastePuissant;
use core\items\foods\alcools\AlcoolHeal;
use core\items\foods\alcools\AlcoolHealPuissant;
use core\items\foods\alcools\AlcoolPur;
use core\items\foods\alcools\AlcoolSpeed;
use core\items\foods\alcools\AlcoolSpeedPuissant;
use core\items\foods\alcools\BottleJobs;
use core\items\foods\alcools\EmptyBottle;
use core\items\foods\BoeufBourguignon;
use core\items\foods\RaisinMoisie;
use core\items\foods\SpectralRottenFlesh;
use core\items\fossils\FossilDiplodocus;
use core\items\fossils\FossilNodosaurus;
use core\items\fossils\FossilPterodactyle;
use core\items\fossils\Fossils;
use core\items\fossils\FossilsBrachiosaurus;
use core\items\fossils\FossilSpinosaure;
use core\items\fossils\FossilStegosaurus;
use core\items\fossils\FossilTriceratops;
use core\items\fossils\FossilTyrannosaureRex;
use core\items\fossils\FossilVelociraptor;
use core\items\horse\HorseArmorAmethyst;
use core\items\horse\HorseArmorCopper;
use core\items\horse\HorseArmorEmerald;
use core\items\horse\HorseArmorGold;
use core\items\horse\HorseArmorPlatinum;
use core\items\ingots\AmethystIngot;
use core\items\ingots\CopperIngots;
use core\items\ingots\EmeraldIngot;
use core\items\ingots\GoldIngot;
use core\items\ingots\PlatinumIngot;
use core\items\ingots\raws\CopperRaw;
use core\items\ingots\raws\GoldRaw;
use core\items\ingots\raws\PlatinumRaw;
use core\items\ingots\raws\RawSpectral;
use core\items\nuggets\CopperNugget;
use core\items\nuggets\GoldNugget;
use core\items\nuggets\PlatinumNugget;
use core\items\nuggets\SpectralNugget;
use core\items\others\BottleXp;
use core\items\others\CameraItem;
use core\items\others\keypad\Accept;
use core\items\others\keypad\Eight;
use core\items\others\keypad\Five;
use core\items\others\keypad\Four;
use core\items\others\keypad\Keypad;
use core\items\others\keypad\Nine;
use core\items\others\keypad\One;
use core\items\others\keypad\Refus;
use core\items\others\keypad\Seven;
use core\items\others\keypad\Six;
use core\items\others\keypad\Three;
use core\items\others\keypad\Two;
use core\items\others\keypad\Zero;
use core\items\others\MoneyBag;
use core\items\others\MoneyLiasse;
use core\items\others\Rtp;
use core\items\pearl\CustomEnderPearl;
use core\items\pearl\FreezePearl;
use core\items\powder\CopperPowder;
use core\items\powder\GoldPowder;
use core\items\powder\PlatinumPowder;
use core\items\powder\SulfurPowder;
use core\items\sapling\SpectralSapling;
use core\items\staff\Ban;
use core\items\staff\Eye;
use core\items\staff\Freeze;
use core\items\staff\HomeManage;
use core\items\staff\ListTp;
use core\items\staff\Mute;
use core\items\staff\RandomTp;
use core\items\staff\SeeInv;
use core\items\tools\FarmTools;
use core\items\tools\lumberjack\BoneAxe_1;
use core\items\tools\lumberjack\BoneAxe_2;
use core\items\tools\lumberjack\BoneAxe_3;
use core\items\tools\lumberjack\BoneAxe_4;
use core\items\tools\lumberjack\BoneAxe_5;
use core\items\tools\lumberjack\BoneAxe_6;
use core\items\tools\lumberjack\BoneAxe_7;
use core\items\tools\lumberjack\BoneAxe_8;
use core\items\tools\PickaxeSpawner;
use core\items\tools\spectral\SpectralAxe;
use core\items\tools\spectral\SpectralHoe;
use core\items\tools\spectral\SpectralPickaxe;
use core\items\tools\spectral\SpectralShovel;
use core\items\tools\spectral\SpectralSword;
use core\items\tools\VoidStone;
use core\items\ui\ArrowLeft;
use core\items\ui\ArrowRight;
use core\items\ui\Chest;
use core\items\ui\Interog;
use core\items\unclaimFinders\UnclaimFinderAmethyst;
use core\items\unclaimFinders\UnclaimFinderCopper;
use core\items\unclaimFinders\UnclaimFinderEmerald;
use core\items\unclaimFinders\UnclaimFinderGold;
use core\items\unclaimFinders\UnclaimFinderPlatine;
use core\settings\CosmeticsIds;
use core\settings\Cosmetiques;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\utils\TextFormat;

class ItemManager
{
    const CONVERT_COSMETIC_NAME = [
        CosmeticsIds::AUREOLE => "§6- §fAuréole §6d'ange §6-",
        CosmeticsIds::CK_BOB => "§6- §fBob " . TextFormat::BLACK . " Calvin §fKlein §6-",
        CosmeticsIds::BLUE_BOB => "§6- §fBob " . TextFormat::BLUE . " Bleu §6-",
        CosmeticsIds::BLACK_BOB => "§6- §fBob " . TextFormat::BLACK . " Noir §6-",
        CosmeticsIds::RED_BOB => "§6- §fBob " . TextFormat::RED . " Rouge §6-",
        CosmeticsIds::URAHARA_KEISUKE_BOB => "§6- §fBob " . TextFormat::RED . " Urahara Keisuke §6-",
        CosmeticsIds::GREEN_BOB => "§6- §fBob " . TextFormat::GREEN . " Vert §6-",
        CosmeticsIds::PURPLE_BOB => "§6- §fBob " . TextFormat::LIGHT_PURPLE . " Violet §6-",
        CosmeticsIds::LUIGI => "§6- §f" . TextFormat::GREEN . " Luigi §6-",
        CosmeticsIds::MARIO => "§6- §f" . TextFormat::RED . " Mario §6-",
        CosmeticsIds::NAPOLEON => "§6- §f Chapeau de Napoélon §6-",
        CosmeticsIds::PEACH => "§6- §f" . TextFormat::LIGHT_PURPLE . " Chapeau de Peach §6-",
        CosmeticsIds::WITCH => "§6- §f" . TextFormat::BLACK . " Chapeau §fde sorcière §6-",

        CosmeticsIds::BANDANA_BLACK => "§6- §fBandana " . TextFormat::BLACK . "Noir §6-",
        CosmeticsIds::BANDANA_BLUE => "§6- §fBandana " . TextFormat::BLUE . "Bleu §6-",
        CosmeticsIds::BANDANA_GOLD => "§6- §fBandana " . TextFormat::GOLD . "Or §6-",
        CosmeticsIds::BANDANA_GREEN => "§6- §fBandana " . TextFormat::GREEN . "Vert §6-",
        CosmeticsIds::BANDANA_PURPLE => "§6- §fBandana " . TextFormat::LIGHT_PURPLE . "Violet §6-",
        CosmeticsIds::BANDANA_RED => "§6- §fBandana " . TextFormat::RED . "Rouge §6-",
        CosmeticsIds::BANDANA_WHITE => "§6- §fBandana " . TextFormat::WHITE . "Blanc §6-",

        CosmeticsIds::BARBE_NOEL => "§6- §fBarbe " . TextFormat::RED . "Du père noël §6-",
        CosmeticsIds::BLACK_HAT => "§6- §fChapeau " . TextFormat::BLACK . "Noir §6-",
        CosmeticsIds::WHITE_HAT => "§6- §fChapeau " . TextFormat::WHITE . "Blanc §6-",
        CosmeticsIds::CASQUE_AUDIO => "§6- §fCasque " . TextFormat::GREEN . "Audio §6-",
        CosmeticsIds::CASQUETTE_JOTARO => "§6- §fCasquette de  " . TextFormat::LIGHT_PURPLE . "de Jotaro §6-",
        CosmeticsIds::CASQUETTE_MARIN => "§6- §fCasquette de  " . TextFormat::BLUE . "de Marin §6-",

        CosmeticsIds::LUNETTE_NOIR => "§6- §fLunette " . TextFormat::BLACK . "Noir §6-",
        CosmeticsIds::TROLL => "§6- §fTête du " . TextFormat::GREEN . "Troll §6-",

        CosmeticsIds::CORNE_CERF => "§6- §fCornes de " . TextFormat::GOLD . "Cerf §6-",
        CosmeticsIds::COWBOYHAT => "§6- §fChapeau du " . TextFormat::RED . "Cowboy §6-",
        CosmeticsIds::TODD => "§6- §fChapeau de " . TextFormat::RED . "Toad §6-",
    ];

    public function init(): void
    {


        CustomiesItemFactory::getInstance()->registerItem(AmethystIngot::class, Ids::AMETHYST_INGOT, "Cristal d'améthyste");
        CustomiesItemFactory::getInstance()->registerItem(CopperIngots::class, Ids::COPPER_INGOT, "Lingot de cuivre");
        CustomiesItemFactory::getInstance()->registerItem(EmeraldIngot::class, Ids::EMERALD_INGOT, "Cristal d'émeraude");
        CustomiesItemFactory::getInstance()->registerItem(GoldIngot::class, Ids::GOLD_INGOT, "Lingot d'or");
        CustomiesItemFactory::getInstance()->registerItem(PlatinumIngot::class, Ids::PLATINUM_INGOT, "Lingot de platine");


        //CustomiesItemFactory::getInstance()->registerItem(AmethystNugget::class, Ids::AMETHYST_NUGGET, "Pépite d'améthyste");
        CustomiesItemFactory::getInstance()->registerItem(CopperNugget::class, Ids::COPPER_NUGGET, "Pépite de cuivre");
        // CustomiesItemFactory::getInstance()->registerItem(EmeraldNugget::class, Ids::EMERALD_NUGGET, "Pépite d'émeraude");
        CustomiesItemFactory::getInstance()->registerItem(GoldNugget::class, Ids::GOLD_NUGGET, "Pépite d'or");
        CustomiesItemFactory::getInstance()->registerItem(PlatinumNugget::class, Ids::PLATINUM_NUGGET, "Pépite de platine");

        //CustomiesItemFactory::getInstance()->registerItem(AmethystPowder::class, Ids::AMETHYST_POWDER, "Poudre d'améthyste");
        CustomiesItemFactory::getInstance()->registerItem(CopperPowder::class, Ids::COPPER_POWDER, "Poudre de cuivre");
        // CustomiesItemFactory::getInstance()->registerItem(EmeraldPowder::class, Ids::EMERALD_POWDER, "Poudre d'émeraude");
        CustomiesItemFactory::getInstance()->registerItem(GoldPowder::class, Ids::GOLD_POWDER, "Poudre d'or");
        CustomiesItemFactory::getInstance()->registerItem(PlatinumPowder::class, Ids::PLATINUM_POWDER, "Poudre de platine");


        //CustomiesItemFactory::getInstance()->registerItem(AmethystRaw::class, Ids::AMETHYST_RAW, "Améthyste brute");
        CustomiesItemFactory::getInstance()->registerItem(CopperRaw::class, Ids::COPPER_RAW, "Cuivre brute");
        // CustomiesItemFactory::getInstance()->registerItem(EmeraldRaw::class, Ids::EMERALD_RAW, "Émeraude brute");
        CustomiesItemFactory::getInstance()->registerItem(GoldRaw::class, Ids::GOLD_RAW, "Or brute");
        CustomiesItemFactory::getInstance()->registerItem(PlatinumRaw::class, Ids::PLATINUM_RAW, "Platine brute");

        CustomiesItemFactory::getInstance()->registerItem(Fossils::class, Ids::FOSSIL, "Fossil");
        CustomiesItemFactory::getInstance()->registerItem(FossilDiplodocus::class, Ids::FOSSIL_DIPLODOCUS, "Fossil de diplodocus");
        CustomiesItemFactory::getInstance()->registerItem(FossilNodosaurus::class, Ids::FOSSIL_NODOSAURUS, "Fossil de nodosaurus");
        CustomiesItemFactory::getInstance()->registerItem(FossilPterodactyle::class, Ids::FOSSIL_PTERODACTYLE, "Fossil de ptérodactyle");
        CustomiesItemFactory::getInstance()->registerItem(FossilsBrachiosaurus::class, Ids::FOSSIL_BRACHIOSAURUS, "Fossil de brachiosaurus");
        CustomiesItemFactory::getInstance()->registerItem(FossilSpinosaure::class, Ids::FOSSIL_SPINOSAURE, "Fossil de spinosaure");
        CustomiesItemFactory::getInstance()->registerItem(FossilStegosaurus::class, Ids::FOSSIL_STEGOSAURUS, "Fossil de stegosaurus");
        CustomiesItemFactory::getInstance()->registerItem(FossilTriceratops::class, Ids::FOSSIL_TRICERATOPS, "Fossil de triceratops");
        CustomiesItemFactory::getInstance()->registerItem(FossilTyrannosaureRex::class, Ids::FOSSIL_TYRANNOSAURE, "Fossil de tyrannosaure rex");
        CustomiesItemFactory::getInstance()->registerItem(FossilVelociraptor::class, Ids::FOSSIL_VELOCIRAPTOR, "Fossil de velociraptor");

        CustomiesItemFactory::getInstance()->registerItem(CommonKey::class, Ids::KEY_COMMON, "Clé commune");
        CustomiesItemFactory::getInstance()->registerItem(RareKey::class, Ids::KEY_RARE, "Clé rare");
        CustomiesItemFactory::getInstance()->registerItem(BoostKey::class, Ids::KEY_BOOST, "Clé boost");
        CustomiesItemFactory::getInstance()->registerItem(BlackKey::class, Ids::KEY_BLACK_KEY, "Clé black gold");
        CustomiesItemFactory::getInstance()->registerItem(FortuneKey::class, Ids::KEY_FORTUNE, "Clé fortune");
        CustomiesItemFactory::getInstance()->registerItem(LegendaryKey::class, Ids::KEY_LEGENDARY, "Clé légendaire");
        CustomiesItemFactory::getInstance()->registerItem(MythicalKey::class, Ids::KEY_MYTHICAL, "Clé mythique");

        CustomiesItemFactory::getInstance()->registerItem(BackpackFarm::class, Ids::BACKPACK_FARM, "Sac du fermier");
        CustomiesItemFactory::getInstance()->registerItem(BackpackOre::class, Ids::BACKPACK_ORE, "Sac du mineur");
        CustomiesItemFactory::getInstance()->registerItem(BackpackFossil::class, Ids::BACKPACK_FOSSIL, "Sac de l'archéologue");


        CustomiesItemFactory::getInstance()->registerItem(Raisin::class, Ids::RAISIN, "Raisin");
        CustomiesItemFactory::getInstance()->registerItem(BerryBlue::class, Ids::BERRY_BLUE, "Baie bleue");
        CustomiesItemFactory::getInstance()->registerItem(BerryPink::class, Ids::BERRY_PINK, "Baie rose");
        CustomiesItemFactory::getInstance()->registerItem(BerryBlack::class, Ids::BERRY_BLACK, "Baie noir");
        CustomiesItemFactory::getInstance()->registerItem(BerryYellow::class, Ids::BERRY_YELLOW, "Baie jaune");

        CustomiesItemFactory::getInstance()->registerItem(RaisinMoisie::class, Ids::RAISIN_MOISI, "Raisin mûr");
        CustomiesItemFactory::getInstance()->registerItem(SeedsObsidian::class, Ids::SEEDS_WHEAT_OBSIDIAN, "Raisin mûr");

        CustomiesItemFactory::getInstance()->registerItem(EmptyBottle::class, Ids::EMPTY_BOTTLE, "Bouteille vide");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolPur::class, Ids::ALCOOL_PUR, "Alcool de vitesse");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolSpeed::class, Ids::ALCOOL_SPEED, "Alcool de vitesse");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolHeal::class, Ids::ALCOOL_HEAL, "Alcool de soin");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolHaste::class, Ids::ALCOOL_HASTE, "Alcool du mineur");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolForce::class, Ids::ALCOOL_FORCE, "Alcool de force");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolSpeedPuissant::class, Ids::ALCOOL_PUISSANT_SPEED, "Alcool de vitesse puissant");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolHealPuissant::class, Ids::ALCOOL_PUISSANT_HEAL, "Alcool de soin puissant");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolHastePuissant::class, Ids::ALCOOL_PUISSANT_HASTE, "Alcool du mineur puissant");
        CustomiesItemFactory::getInstance()->registerItem(AlcoolForcePuissant::class, Ids::ALCOOL_PUISSANT_FORCE, "Alcool de force puissant");
        CustomiesItemFactory::getInstance()->registerItem(BottleXp::class, Ids::BOTTLE_XP, "Bouteille d'expérience");

        CustomiesItemFactory::getInstance()->registerItem(HorseArmorCopper::class, Ids::HORSE_ARMOR_COPPER, "Monture en cuivre");
        CustomiesItemFactory::getInstance()->registerItem(HorseArmorEmerald::class, Ids::HORSE_ARMOR_EMERALD, "Monture en émeraude");
        CustomiesItemFactory::getInstance()->registerItem(HorseArmorAmethyst::class, Ids::HORSE_ARMOR_AMETHYST, "Monture en améthyste");
        CustomiesItemFactory::getInstance()->registerItem(HorseArmorPlatinum::class, Ids::HORSE_ARMOR_PLATINUM, "Monture en platine");
        CustomiesItemFactory::getInstance()->registerItem(HorseArmorGold::class, Ids::HORSE_ARMOR_GOLD, "Monture en or");

        CustomiesItemFactory::getInstance()->registerItem(VoidStone::class, Ids::VOIDSTONE, "Fiole d'expérience");
        CustomiesItemFactory::getInstance()->registerItem(IronBow::class, Ids::IRON_BOW, "Arc en fer");

        CustomiesItemFactory::getInstance()->registerItem(EmeraldDynamite::class, Ids::EMERALD_DYNAMITE, "Dynamite en émeraude");
        CustomiesItemFactory::getInstance()->registerItem(AmethystDynamite::class, Ids::AMETHYST_DYNAMITE, "Dynamite en améthyste");
        CustomiesItemFactory::getInstance()->registerItem(PlatinumDynamite::class, Ids::PLATINUM_DYNAMITE, "Dynamite en platine");
        CustomiesItemFactory::getInstance()->registerItem(BaseDynamite::class, Ids::BASE_DYNAMITE, "Dynamite");
        CustomiesItemFactory::getInstance()->registerItem(DynamiteWater::class, Ids::WATER_DYNAMITE, "Dynamite anti-eau");


        CustomiesItemFactory::getInstance()->registerItem(SulfurPowder::class, Ids::SULFUR_POWDER, "Poudre de soufre");

        CustomiesItemFactory::getInstance()->registerItem(BoeufBourguignon::class, Ids::BOEUF_BOURGUIGNON, "Boeuf bourguignon préparé par §l§eTeamPanda9457");


        CustomiesItemFactory::getInstance()->registerItem(UnclaimFinderCopper::class, Ids::UNCLAIM_FINDER_COPPER, "Unclaim finder en cuivre");
        CustomiesItemFactory::getInstance()->registerItem(UnclaimFinderEmerald::class, Ids::UNCLAIM_FINDER_EMERALD, "Unclaim finder en émeraude");
        CustomiesItemFactory::getInstance()->registerItem(UnclaimFinderAmethyst::class, Ids::UNCLAIM_FINDER_AMETHYST, "Unclaim finder en améthyste");
        CustomiesItemFactory::getInstance()->registerItem(UnclaimFinderPlatine::class, Ids::UNCLAIM_FINDER_PLATINUM, "Unclaim finder en platine");
        CustomiesItemFactory::getInstance()->registerItem(UnclaimFinderGold::class, Ids::UNCLAIM_FINDER_GOLD, "Unclaim finder en or");


        CustomiesItemFactory::getInstance()->registerItem(EggZombie::class, Ids::EGG_ZOMBIE, "Oeuf de zombie");
        CustomiesItemFactory::getInstance()->registerItem(EggCreeper::class, Ids::EGG_CREEPER, "Oeuf de creeper");
        CustomiesItemFactory::getInstance()->registerItem(EggSkeleton::class, Ids::EGG_SKELETON, "Oeuf de squelette");
        CustomiesItemFactory::getInstance()->registerItem(EggChicken::class, Ids::EGG_CHICKEN, "Oeuf de poulet");
        CustomiesItemFactory::getInstance()->registerItem(EggPig::class, Ids::EGG_COCHON, "Oeuf de cochon");
        CustomiesItemFactory::getInstance()->registerItem(EggCow::class, Ids::EGG_COW, "Oeuf de vache");
        CustomiesItemFactory::getInstance()->registerItem(EggEnderman::class, Ids::EGG_ENDERMAN, "Oeuf d'Enderman");
        CustomiesItemFactory::getInstance()->registerItem(EggMouton::class, Ids::EGG_MUTTON, "Oeuf de Mouton");

        CustomiesItemFactory::getInstance()->registerItem(BucketEmptyCopper::class, Ids::BUCKET_COPPER_EMPTY, "Seau en cuivre");
        CustomiesItemFactory::getInstance()->registerItem(BucketCopper::class, Ids::BUCKET_COPPER_WATER, "Seau en cuivre remplie d'eau");
        CustomiesItemFactory::getInstance()->registerItem(BucketCopperLava::class, Ids::BUCKET_COPPER_LAVA, "Seau en cuivre remplie de lave");

        CustomiesItemFactory::getInstance()->registerItem(BucketEmptyPlatinum::class, Ids::BUCKET_PLATINUM_EMPTY, "Seau en platine");
        CustomiesItemFactory::getInstance()->registerItem(BucketPlatinum::class, Ids::BUCKET_PLATINUM_WATER, "Seau en platine remplie d'eau");
        CustomiesItemFactory::getInstance()->registerItem(BucketPlatinumLava::class, Ids::BUCKET_PLATINUM_LAVA, "Seau en platine remplie de lave");


        CustomiesItemFactory::getInstance()->registerItem(BucketEmptyGold::class, Ids::BUCKET_GOLD_EMPTY, "Seau en or");
        CustomiesItemFactory::getInstance()->registerItem(BucketGold::class, Ids::BUCKET_GOLD_WATER, "Seau en or remplie d'eau");
        CustomiesItemFactory::getInstance()->registerItem(BucketGoldLava::class, Ids::BUCKET_GOLD_LAVA, "Seau en or remplie de lave");

        CustomiesItemFactory::getInstance()->registerItem(HoodHelmet::class, Ids::HOOD_HELMET, "Hood helmet");

        CustomiesItemFactory::getInstance()->registerItem(MoneyBag::class, Ids::MONEY_BAG, "Sac d'argent");
        CustomiesItemFactory::getInstance()->registerItem(MoneyLiasse::class, Ids::MONEY, "Liasse de 1000§6$");
        CustomiesItemFactory::getInstance()->registerItem(FlowerPercent::class, Ids::FLOWER_PERCENT, "Fleur de Camouflage");

        CustomiesItemFactory::getInstance()->registerItem(BoneAxe_1::class, Ids::BONE_AXE_1, "Hache en os");
        CustomiesItemFactory::getInstance()->registerItem(BoneAxe_2::class, Ids::BONE_AXE_2, "Hache en os");
        CustomiesItemFactory::getInstance()->registerItem(BoneAxe_3::class, Ids::BONE_AXE_3, "Hache en os");
        CustomiesItemFactory::getInstance()->registerItem(BoneAxe_4::class, Ids::BONE_AXE_4, "Hache en os");
        CustomiesItemFactory::getInstance()->registerItem(BoneAxe_5::class, Ids::BONE_AXE_5, "Hache en os");
        CustomiesItemFactory::getInstance()->registerItem(BoneAxe_6::class, Ids::BONE_AXE_6, "Hache en os");
        CustomiesItemFactory::getInstance()->registerItem(BoneAxe_7::class, Ids::BONE_AXE_7, "Hache en os");
        CustomiesItemFactory::getInstance()->registerItem(BoneAxe_8::class, Ids::BONE_AXE_8, "Hache en os");

        CustomiesItemFactory::getInstance()->registerItem(FarmTools::class, Ids::FARMTOOLS, "Farmtools");

        CustomiesItemFactory::getInstance()->registerItem(Ban::class, Ids::BAN, "Farmtools");
        CustomiesItemFactory::getInstance()->registerItem(Eye::class, Ids::EYE, "Farmtools");
        CustomiesItemFactory::getInstance()->registerItem(Freeze::class, Ids::FREEZE, "Farmtools");
        CustomiesItemFactory::getInstance()->registerItem(Mute::class, Ids::MUTE, "Farmtools");
        CustomiesItemFactory::getInstance()->registerItem(CameraItem::class, Ids::CAMERA, "Appareil photo");

        CustomiesItemFactory::getInstance()->registerItem(Rtp::class, Ids::RTP, "Ticket de RTP");
        CustomiesItemFactory::getInstance()->registerItem(HomeManage::class, Ids::HOME_MANAGE, "Home Manage");
        CustomiesItemFactory::getInstance()->registerItem(RandomTp::class, Ids::RANDOM_TP, "Random TP");
        CustomiesItemFactory::getInstance()->registerItem(SeeInv::class, Ids::SEE_INV, "See Inventory");


        CustomiesItemFactory::getInstance()->registerItem(Zero::class, Ids::ZERO, "Zéro");
        CustomiesItemFactory::getInstance()->registerItem(One::class, Ids::ONE, "Un");
        CustomiesItemFactory::getInstance()->registerItem(Two::class, Ids::TWO, "Deux");
        CustomiesItemFactory::getInstance()->registerItem(Three::class, Ids::THREE, "Trois");
        CustomiesItemFactory::getInstance()->registerItem(Four::class, Ids::FOUR, "Quatre");
        CustomiesItemFactory::getInstance()->registerItem(Five::class, Ids::FIVE, "Cinq");
        CustomiesItemFactory::getInstance()->registerItem(Six::class, Ids::SIX, "Six");
        CustomiesItemFactory::getInstance()->registerItem(Seven::class, Ids::SEVEN, "Sept");
        CustomiesItemFactory::getInstance()->registerItem(Eight::class, Ids::EIGHT, "Huit");
        CustomiesItemFactory::getInstance()->registerItem(Nine::class, Ids::NINE, "Neuf");
        CustomiesItemFactory::getInstance()->registerItem(Keypad::class, Ids::KEYPAD, "Keypad");
        CustomiesItemFactory::getInstance()->registerItem(Accept::class, Ids::ACCEPT, "Accepter");
        CustomiesItemFactory::getInstance()->registerItem(Refus::class, Ids::REFUS, "Supprimer");
        CustomiesItemFactory::getInstance()->registerItem(ListTp::class, Ids::TP_LIST, "Tp liste");

        CustomiesItemFactory::getInstance()->registerItem(FreezePearl::class, Ids::FREEZE_PEARL, "Freeze Pearl");
        CustomiesItemFactory::getInstance()->registerItem(CustomEnderPearl::class, Ids::ENDER_PEARL, "Ender Pearl Custom");


        CustomiesItemFactory::getInstance()->registerItem(BottleJobs::class, Ids::BOTTLE_JOBS, "Bouteille des métiers");


        CustomiesItemFactory::getInstance()->registerItem(ArrowLeft::class, Ids::ARROW_LEFT, "Page d'avant");
        CustomiesItemFactory::getInstance()->registerItem(ArrowRight::class, Ids::ARROW_RIGHT, "Page d'après");
        CustomiesItemFactory::getInstance()->registerItem(Interog::class, Ids::INTEROG, "Comment ça marche ?");
        CustomiesItemFactory::getInstance()->registerItem(Chest::class, Ids::CHEST, "Vos items");


        CustomiesItemFactory::getInstance()->registerItem(PickaxeSpawner::class, Ids::PICKAXE_SPAWNER, "Pioche à spawner");





        # spectrals
        CustomiesItemFactory::getInstance()->registerItem(SpectralAxe::class, Ids::SPECTRAL_AXE, "Hache spectral");
        CustomiesItemFactory::getInstance()->registerItem(SpectralHoe::class, Ids::SPECTRAL_HOE, "Houe spectral");
        CustomiesItemFactory::getInstance()->registerItem(SpectralPickaxe::class, Ids::SPECTRAL_PICKAXE, "Pioche spectral");
        CustomiesItemFactory::getInstance()->registerItem(SpectralShovel::class, Ids::SPECTRAL_SHOVEL, "Pelle spectral");
        CustomiesItemFactory::getInstance()->registerItem(SpectralSword::class, Ids::SPECTRAL_SWORD, "Épée spectral");

        CustomiesItemFactory::getInstance()->registerItem(SpectralNugget::class, Ids::SPECTRAL_NUGGET, "Pépite spectral");
        CustomiesItemFactory::getInstance()->registerItem(SpectralRottenFlesh::class, Ids::SPECTRAL_ROTTEN_FLESH, "Chair de zombie spectral");
        CustomiesItemFactory::getInstance()->registerItem(RawSpectral::class, Ids::RAW_SPECTRAL, "Minerais spectral brute");
        CustomiesItemFactory::getInstance()->registerItem(SpectralSapling::class, Ids::SPECTRAL_SAPLING, "Pousse d'arbre spectral");

// ... et ainsi de suite pour les autres classes.


        // cosmetics heads
        $ids = new \ReflectionClass(CosmeticsIds::class);
        $constants = $ids->getConstants();
        foreach ($constants as $constant => $id) {

            /*
             * 1000 800 : common
             * 800 500 : rare
             * 500 100 : epic
             * 100 0 : legend
             */

            $rare = Cosmetiques::ALL[$id] ?? 0;
            $texture = "";
            if ($rare >= 800 && $rare <= 1000) {
                $texture = "hat_common";
            } else if ($rare >= 500 && $rare <= 800) {
                $texture = "hat_rare";
            } else if ($rare >= 100 && $rare <= 500) {
                $texture = "hat_epic";
            } else if ($rare >= 0 && $rare <= 100) {
                $texture = "hat_legendary";
            }


            CustomiesItemFactory::getInstance()->registerCosmetic(CosmeticHead::class, $id, self::CONVERT_COSMETIC_NAME[$id] ?? "§6- §f" . str_replace(["goldrush:", "_"], ["", " "], $id) . "§6-", $texture);
        }
    }
}