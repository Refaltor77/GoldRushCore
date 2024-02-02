<?php

namespace core\entities;

use core\entities\boss\Ogre;
use core\entities\classements\TopDeath;
use core\entities\classements\TopEntity;
use core\entities\classements\TopFaction;
use core\entities\classements\TopGold;
use core\entities\classements\TopJobs;
use core\entities\classements\TopKill;
use core\entities\classements\TopMoney;
use core\entities\cosmetics\CosmeticStand;
use core\entities\cosmetics\Rideau;
use core\entities\dynamites\AmethystDynamite;
use core\entities\dynamites\BaseDynamite;
use core\entities\dynamites\EmeraldDynamite;
use core\entities\dynamites\PlatineDynamite;
use core\entities\dynamites\WaterDynamite;
use core\entities\horse\Horse;
use core\entities\horse\HorseAmethyst;
use core\entities\horse\HorseCopper;
use core\entities\horse\HorseEmerald;
use core\entities\horse\HorseGold;
use core\entities\horse\HorsePlatinum;
use core\entities\projectils\FreezePearlEntity;
use core\entities\vanilla\Chicken;
use core\entities\vanilla\Cow;
use core\entities\vanilla\Creeper;
use core\entities\vanilla\Enderman;
use core\entities\vanilla\Mouton;
use core\entities\vanilla\Pig;
use core\entities\vanilla\Skeleton;
use core\entities\vanilla\ZombieCustom;
use core\entities\xpBottle\XpBottleEntity;
use core\items\dynamites\PlatinumDynamite;
use customiesdevs\customies\entity\CustomiesEntityFactory;
use core\entities\BourseText;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\Zombie;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class EntityManager
{
    public function init()
    {

        CustomiesEntityFactory::getInstance()->registerEntity(BoxBlackGold::class, BoxBlackGold::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(BoxBoost::class, BoxBoost::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(BoxCommon::class, BoxCommon::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(BoxFortune::class, BoxFortune::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(BoxLegendary::class, BoxLegendary::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(BoxMythical::class, BoxMythical::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(BoxRare::class, BoxRare::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(AirDrops::class, AirDrops::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(Totem::class, Totem::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(BossSouls::class, BossSouls::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(GoldrushText::class, GoldrushText::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(KothText::class, KothText::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(BarText::class, BarText::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(Bank::class, Bank::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(Peste::class, Peste::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(TrollBoss::class, TrollBoss::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(DoorBox::class, DoorBox::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(Nexus::class, Nexus::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(Test::class, Test::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(CosmeticStand::class, CosmeticStand::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(Roulette::class, Roulette::getNetworkTypeId());

        CustomiesEntityFactory::getInstance()->registerEntity(HorseCopper::class, HorseCopper::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(HorseEmerald::class, HorseEmerald::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(HorseAmethyst::class, HorseAmethyst::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(HorsePlatinum::class, HorsePlatinum::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(HorseGold::class, HorseGold::getNetworkTypeId());
        CustomiesEntityFactory::getInstance()->registerEntity(Ogre::class, Ogre::getNetworkTypeId());

        CustomiesEntityFactory::getInstance()->registerEntity(Rideau::class, Rideau::getNetworkTypeId());


        CustomiesEntityFactory::getInstance()->registerEntity(EmeraldDynamite::class, EmeraldDynamite::getNetworkTypeId(), function (World $world, CompoundTag $nbt): EmeraldDynamite {
            return new EmeraldDynamite(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        });

        CustomiesEntityFactory::getInstance()->registerEntity(FreezePearlEntity::class, FreezePearlEntity::getNetworkTypeId(), function (World $world, CompoundTag $nbt): FreezePearlEntity {
            return new FreezePearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        });


        CustomiesEntityFactory::getInstance()->registerEntity(CosmeticStand::class, CosmeticStand::getNetworkTypeId(), function (World $world, CompoundTag $nbt): CosmeticStand {
            return new CosmeticStand(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        });

        CustomiesEntityFactory::getInstance()->registerEntity(AmethystDynamite::class, AmethystDynamite::getNetworkTypeId(), function (World $world, CompoundTag $nbt): AmethystDynamite {
            return new AmethystDynamite(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        });

        CustomiesEntityFactory::getInstance()->registerEntity(PlatineDynamite::class, PlatineDynamite::getNetworkTypeId(), function (World $world, CompoundTag $nbt): PlatineDynamite {
            return new PlatineDynamite(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        });


        CustomiesEntityFactory::getInstance()->registerEntity(BaseDynamite::class, BaseDynamite::getNetworkTypeId(), function (World $world, CompoundTag $nbt): BaseDynamite {
            return new BaseDynamite(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        });

        CustomiesEntityFactory::getInstance()->registerEntity(WaterDynamite::class, WaterDynamite::getNetworkTypeId(), function (World $world, CompoundTag $nbt): WaterDynamite {
            return new WaterDynamite(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        });


        EntityFactory::getInstance()->register(Slapper::class, function (World $world, CompoundTag $nbt): Slapper {
            return new Slapper(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), "", "", false,$nbt);
        }, ["minecraft:slapper"]);

        EntityFactory::getInstance()->register(SlapperPrime::class, function (World $world, CompoundTag $nbt): SlapperPrime {
        return new SlapperPrime(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), "", "", false,$nbt);
        }, ["minecraft:slapper_prime"]);

        EntityFactory::getInstance()->register(SlapperPrime2::class, function (World $world, CompoundTag $nbt): SlapperPrime2 {
            return new SlapperPrime2(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), "", "", false,$nbt);
        }, ["minecraft:slapper_prime2"]);

        EntityFactory::getInstance()->register(SlapperPrime3::class, function (World $world, CompoundTag $nbt): SlapperPrime3 {
            return new SlapperPrime3(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), "", "", false,$nbt);
        }, ["minecraft:slapper_prime3"]);


        EntityFactory::getInstance()->register(Creeper::class, function (World $world, CompoundTag $nbt): Creeper {
            return new Creeper(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [Creeper::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(Chicken::class, function (World $world, CompoundTag $nbt): Chicken {
            return new Chicken(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [Chicken::getNetworkTypeId()]);


        EntityFactory::getInstance()->register(ZombieCustom::class, function (World $world, CompoundTag $nbt): ZombieCustom {
            return new ZombieCustom(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [ZombieCustom::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(Cow::class, function (World $world, CompoundTag $nbt): Cow {
            return new Cow(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [Cow::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(Pig::class, function (World $world, CompoundTag $nbt): Pig {
            return new Pig(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [Pig::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(Skeleton::class, function (World $world, CompoundTag $nbt): Skeleton {
            return new Skeleton(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [Skeleton::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(Enderman::class, function (World $world, CompoundTag $nbt): Enderman {
            return new Enderman(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [Enderman::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(Mouton::class, function (World $world, CompoundTag $nbt): Mouton {
            return new Mouton(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [Mouton::getNetworkTypeId()]);


        EntityFactory::getInstance()->register(ItemEntitySafe::class, function (World $world, CompoundTag $nbt): ItemEntitySafe {
            $itemTag = $nbt->getCompoundTag("Item");
            if ($itemTag === null) {
                throw new SavedDataLoadingException("Expected \"Item\" NBT tag not found");
            }
            $item = Item::nbtDeserialize($itemTag);
            if ($item->isNull()) {
                throw new SavedDataLoadingException("Item is invalid");
            }
            return new ItemEntitySafe(EntityDataHelper::parseLocation($nbt, $world), $item, $nbt);
        }, ["goldrush:itemsafe"]);

        EntityFactory::getInstance()->register(XpBottleEntity::class, function (World $world, CompoundTag $nbt): XpBottleEntity {
            return new XpBottleEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ["minecraft:xp_bottle_entity"]);



        EntityFactory::getInstance()->register(Horse::class, function (World $world, CompoundTag $nbt): Horse {
            return new Horse(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["minecraft:horse_monture"]);


        EntityFactory::getInstance()->register(TopEntity::class, function (World $world, CompoundTag $nbt): TopEntity {
            return new TopEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt, "none");
        }, ["minecraft:top_entity"]);


        EntityFactory::getInstance()->register(TopDeath::class, function (World $world, CompoundTag $nbt): TopDeath {
            return new TopDeath(EntityDataHelper::parseLocation($nbt, $world), $nbt, "none");
        }, ["minecraft:TopDeath"]);
        EntityFactory::getInstance()->register(TopFaction::class, function (World $world, CompoundTag $nbt): TopFaction {
            return new TopFaction(EntityDataHelper::parseLocation($nbt, $world), $nbt, "none");
        }, ["minecraft:TopFaction"]);
        EntityFactory::getInstance()->register(TopGold::class, function (World $world, CompoundTag $nbt): TopGold {
            return new TopGold(EntityDataHelper::parseLocation($nbt, $world), $nbt, "none");
        }, ["minecraft:TopGold"]);
        EntityFactory::getInstance()->register(TopJobs::class, function (World $world, CompoundTag $nbt): TopJobs {
            return new TopJobs(EntityDataHelper::parseLocation($nbt, $world), $nbt, "none");
        }, ["minecraft:TopJobs"]);
        EntityFactory::getInstance()->register(TopKill::class, function (World $world, CompoundTag $nbt): TopKill {
            return new TopKill(EntityDataHelper::parseLocation($nbt, $world), $nbt, "none");
        }, ["minecraft:TopKill"]);
        EntityFactory::getInstance()->register(TopMoney::class, function (World $world, CompoundTag $nbt): TopMoney {
            return new TopMoney(EntityDataHelper::parseLocation($nbt, $world), $nbt, "none");
        }, ["minecraft:TopMoney"]);






        EntityFactory::getInstance()->register(Text::class, function (World $world, CompoundTag $nbt): Text {
            return new Text(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["minecraft:text"]);

        EntityFactory::getInstance()->register(BourseText::class, function (World $world, CompoundTag $nbt): BourseText {
            return new BourseText(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["minecraft:bourse_text"]);


        EntityFactory::getInstance()->register(XpAndDamage::class, function (World $world, CompoundTag $nbt): XpAndDamage {
            return new XpAndDamage(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["minecraft:top_entity"]);

        EntityFactory::getInstance()->register(CameraEntity::class,function(World $world,CompoundTag $nbt):CameraEntity{
            return new CameraEntity(EntityDataHelper::parseLocation($nbt,$world),$nbt);
        },["minecraft:tripod_camera"]);


        //EntityFactory::getInstance()->register(XpAndDamage::class, function (World $world, CompoundTag $nbt): XpAndDamage {
        //    return new XpAndDamage(EntityDataHelper::parseLocation($nbt, $world));
        //}, ["minecraft:notif"]);

        //EntityFactory::getInstance()->register(NpcShop::class, function (World $world, CompoundTag $nbt): NpcShop {
        //    return new NpcShop(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        //}, ["minecraft:npcshop"]);
    }
}