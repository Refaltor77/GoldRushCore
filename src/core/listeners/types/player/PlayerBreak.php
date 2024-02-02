<?php

namespace core\listeners\types\player;

use core\blocks\blocks\MonsterSpawner;
use core\items\backpacks\BackpackFarm;
use core\items\backpacks\BackpackFossil;
use core\items\backpacks\BackpackOre;
use core\items\tools\PickaxeSpawner;
use core\items\tools\VoidStone;
use core\Main;
use core\managers\factions\FactionRank;
use core\messages\Messages;
use core\services\Query;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockToolType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\Server;

class PlayerBreak implements Listener
{

    use SoundTrait;

    const LEVELS = [
        2 => 5000,
        3 => 10000,
        4 => 20000,
        5 => 30000,
        6 => 40000,
        7 => 50000,
        8 => 60000,
        9 => 70000,
        10 => 80000,
        11 => 90000,
        12 => 100000,
        13 => 110000,
        14 => 120000,
    ];


    public function onQuery(QueryRegenerateEvent $even): void {

    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $drops = $event->getDrops();
        $itemInHand = $player->getInventory()->getItemInHand();



        foreach ($drops as $i => $item) {



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
                        unset($drops[$i]);
                        $player->removeCurrentWindow();
                        $itemInSac->addItemInSac($item, $player, $slot);
                        $this->sendPop($player);
                        break;
                    }
                }
            }

            if (in_array($item->getTypeId(), $ids)) {
                foreach ($player->getInventory()->getContents() as $slot => $itemInSac) {
                    if ($itemInSac instanceof BackpackFarm) {
                        $player->removeCurrentWindow();
                        $add = $itemInSac->addItemInSac($item, $player, $slot);
                        if ($add) {
                            unset($drops[$i]);
                            $this->sendPop($player);
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
                        unset($drops[$i]);
                        $player->removeCurrentWindow();
                        $itemInSac->addItemInSac($item, $player, $slot);
                        $this->sendPop($player);
                        break;
                    }
                }
            }





            if (!$player->getInventory()->canAddItem($item)) {
                if (Main::getInstance()->getSettingsManager()->getSetting($player, "inv")) {
                    $player->sendTitle("§cInventaire plein !");
                    $player->sendErrorSound();
                }
            }



            if(Main::getInstance()->getFactionManager()->isInClaim($block->getPosition()) && $player->hasPlayer) {
                $factionManager = Main::getInstance()->getFactionManager();
                $factionName = $factionManager->getFactionNameInClaim($block->getPosition());
                $playerFaction = $factionManager->getFactionName($player->getXuid());
                if($factionName === $playerFaction) {
                    $rank = $factionManager->getRankMember($player->getXuid(),$factionName);
                    if($rank !== FactionRank::RECRUE) {
                        if ($item->getTypeId() === VanillaBlocks::COBBLESTONE()->asItem()->getTypeId()) {
                            foreach ($player->getInventory()->getContents() as $slot => $itemInv) {
                                if ($itemInv instanceof VoidStone) {
                                    $itemInv->addItemInVoidStone($item);
                                    unset($drops[$i]);
                                    $player->getInventory()->setItem($slot, $itemInv);
                                    break;
                                }
                            }
                        }


                        if ($item->getTypeId() === VanillaBlocks::COBBLED_DEEPSLATE()->asItem()->getTypeId()) {
                            foreach ($player->getInventory()->getContents() as $slot => $itemInv) {
                                if ($itemInv instanceof VoidStone) {
                                    $itemInv->addItemInVoidStone($item);
                                    unset($drops[$i]);
                                    $player->getInventory()->setItem($slot, $itemInv);
                                    break;
                                }
                            }
                        }
                    } else {

                        if ($block instanceof MonsterSpawner && $player->getInventory()->getItemInHand() instanceof PickaxeSpawner) {

                        } else {

                            $player->sendMessage(Messages::message("§cLe grade recrue ne permet pas de casser dans les claims de votre faction."));
                            $event->cancel();
                        }
                    }
                } else {
                    if ($block instanceof MonsterSpawner && $player->getInventory()->getItemInHand() instanceof PickaxeSpawner) {
                    } else {
                        $event->cancel();
                    }
                }
            } else {
                if ($item->getTypeId() === VanillaBlocks::COBBLESTONE()->asItem()->getTypeId()) {
                    foreach ($player->getInventory()->getContents() as $slot => $itemInv) {
                        if ($itemInv instanceof VoidStone) {
                            $itemInv->addItemInVoidStone($item);
                            unset($drops[$i]);
                            $player->getInventory()->setItem($slot, $itemInv);
                            break;
                        }
                    }
                }


                if ($item->getTypeId() === VanillaBlocks::COBBLED_DEEPSLATE()->asItem()->getTypeId()) {
                    foreach ($player->getInventory()->getContents() as $slot => $itemInv) {
                        if ($itemInv instanceof VoidStone) {
                            $itemInv->addItemInVoidStone($item);
                            unset($drops[$i]);
                            $player->getInventory()->setItem($slot, $itemInv);
                            break;
                        }
                    }
                }
            }
        }
        $event->setDrops($drops);
    }


    public function onPlaceB(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();

        if(Main::getInstance()->getFactionManager()->isInClaim($block->getPosition()) && $player->hasPlayer) {
            $factionManager = Main::getInstance()->getFactionManager();
            $factionName = $factionManager->getFactionNameInClaim($block->getPosition());
            $playerFaction = $factionManager->getFactionName($player->getXuid());
            if($factionName === $playerFaction) {
                $rank = $factionManager->getRankMember($player->getXuid(),$factionName);
                if($rank !== FactionRank::RECRUE) {

                } else {
                    $player->sendMessage(Messages::message("§cLe grade recrue ne permet pas de casser dans les claims de votre faction."));
                    $event->cancel();
                }
            } else $event->cancel();
        }
    }


    private function getVoidStone(Player $player): ?int
    {
        foreach ($player->getInventory()->getContents() as $index => $item) {
            if ($item->getStateId() === CustomiesItemFactory::getInstance()->get(Ids::VOIDSTONE)->getStateId()) {
                return $index;
            }
        }
        return null;
    }

    private function getAmount(array $drops): int
    {
        $count = 0;
        foreach ($drops as $drop) {
            if ($drop->getStateId() === VanillaBlocks::COBBLESTONE()->getStateId()) {
                $count += $drop->getCount();
            }
        }
        return $count;
    }

}