<?php

namespace core\listeners\types\player;

use core\api\gui\ChestInventory;
use core\items\backpacks\BackpackFarm;
use core\items\backpacks\BackpackFossil;
use core\items\backpacks\BackpackOre;
use core\items\horse\HorseArmorAmethyst;
use core\items\horse\HorseArmorCopper;
use core\items\horse\HorseArmorEmerald;
use core\items\horse\HorseArmorGold;
use core\items\horse\HorseArmorPlatinum;
use core\items\staff\Ban;
use core\items\staff\Eye;
use core\items\staff\Freeze;
use core\items\staff\HomeManage;
use core\items\staff\Mute;
use core\items\staff\RandomTp;
use core\items\staff\SeeInv;
use core\listeners\BaseEvent;
use core\Main;
use core\managers\sync\SyncTypes;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ItemUseResult;
use pocketmine\Server;


class PlayerCollectItem extends BaseEvent
{
    use SoundTrait;

    public function onCollectItem(EntityItemPickupEvent $event): void {
        $player = $event->getEntity();
        $item = $event->getItem();


        if (in_array($item::class, [
            HorseArmorCopper::class,
            HorseArmorEmerald::class,
            HorseArmorAmethyst::class,
            HorseArmorPlatinum::class,
            HorseArmorGold::class
        ])) {
            if ($item->getNamedTag()->getString('xuid', 'none') !== $player->getXuid()) {
                $player->sendErrorSound();
                $player->sendMessage(Messages::message("§cCette monture ne vous appartient pas !"));
                $event->cancel();
            }
        }



        $itemEntity = $event->getOrigin();

        if ($player instanceof CustomPlayer) {
            if (Server::getInstance()->isOp($player->getName()) || !Main::getInstance()->getStaffManager()->isInStaffMode($player)) {

                $class = [
                    Ban::class,
                    Eye::class,
                    Freeze::class,
                    HomeManage::class,
                    Mute::class,
                    RandomTp::class,
                    SeeInv::class
                ];


                if (in_array($event->getItem()::class, $class)) {
                    $event->cancel();
                }
            }

            $item = $event->getItem();

            $ids = [
                ItemTypeIds::WHEAT,
                ItemTypeIds::CARROT,
                ItemTypeIds::MELON,
                ItemTypeIds::POTATO,
                VanillaBlocks::PUMPKIN()->asItem()->getTypeId(),
                VanillaBlocks::SUGARCANE()->asItem()->getTypeId(),
                VanillaBlocks::CACTUS()->asItem()->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::RAISIN)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::BERRY_PINK)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::BERRY_YELLOW)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLACK)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLUE)->getTypeId(),
            ];




            $ores = [
                ItemTypeIds::LAPIS_LAZULI,
                ItemTypeIds::COAL,
                ItemTypeIds::RAW_IRON,
                ItemTypeIds::DIAMOND,
                ItemTypeIds::REDSTONE_DUST,
                CustomiesItemFactory::getInstance()->get(Ids::COPPER_RAW)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_RAW)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::SULFUR_POWDER)->getTypeId(),
            ];



            if (in_array($item->getTypeId(), $ores)) {
                foreach ($player->getInventory()->getContents() as $slot => $itemInSac) {
                    if ($itemInSac instanceof BackpackOre) {
                        $itemInSac->addItemInSac($item, $player, $slot);
                        $event->getOrigin()->flagForDespawn();
                        $this->sendPop($player);
                        $event->cancel();
                        break;
                    }
                }
            }

            if (in_array($item->getTypeId(), $ids)) {
                foreach ($player->getInventory()->getContents() as $slot => $itemInSac) {
                    if ($itemInSac instanceof BackpackFarm) {
                        $add = $itemInSac->addItemInSac($item, $player, $slot);
                        if ($add) {
                            $event->getOrigin()->flagForDespawn();
                            $this->sendPop($player);
                            $event->cancel();
                        }
                        break;
                    }
                }
            }



            $fossils = [
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_DIPLODOCUS)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_NODOSAURUS)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_PTERODACTYLE)->getTypeId() ,
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_BRACHIOSAURUS)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_SPINOSAURE)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_STEGOSAURUS)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TRICERATOPS)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TYRANNOSAURE)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_VELOCIRAPTOR)->getTypeId(),
                CustomiesItemFactory::getInstance()->get(Ids::FOSSIL)->getTypeId(),
            ];

            if (in_array($item->getTypeId(), $fossils)) {
                foreach ($player->getInventory()->getContents() as $slot => $itemInSac) {
                    if ($itemInSac instanceof BackpackFossil) {
                        $itemInSac->addItemInSac($item, $player, $slot);
                        $event->getOrigin()->flagForDespawn();
                        $this->sendPop($player);
                        $event->cancel();
                        break;
                    }
                }
            }
        }
        Main::getInstance()->getDatabaseSyncManager()->addPlayerQueue($player, SyncTypes::INVENTORY);
    }
}