<?php

namespace core\cinematic;

use core\api\camera\CameraSystem;
use core\api\camera\EaseTypes;
use core\api\camera\ShakeTypes;
use core\api\gui\ChestInventory;
use core\api\timings\TimingsSystem;
use core\entities\cosmetics\CosmeticStand;
use core\entities\cosmetics\Rideau;
use core\entities\DoorBox;
use core\Main;
use core\messages\Messages;
use core\settings\CosmeticsIds;
use core\settings\Cosmetiques;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\ExplodeSound;

class Cinematics
{
    public static function sendCinematicBossJumpSylvanar(Player $player): void {
        $camera = new CameraSystem($player);
        $camera->createTiming(function (CameraSystem $cameraSystem, int $seconds, Player $player): void {
            switch ($seconds) {
                case 0:
                    $cameraSystem->setCameraPosition(new Vector3(93, 35, 235), EaseTypes::LINEAR, 2, new Vector3(84, 32, 237));
                    break;
                case 4:
                    $cameraSystem->setCameraPosition($player->getPosition(), EaseTypes::LINEAR, 2, new Vector3(84, 32, 237));
                    break;
                case 6:
                    $cameraSystem->stopTiming();
                    break;
            }
        });
    }



    public static function sendCosmeticOpen(Player $player, Item $keyItem): void {

        if (!Main::getInstance()->getCosmeticManager()->hasCosmetiquesDispo($player)) {
            $player->sendMessage(Messages::message("§cOups ! Tu possède tout les cosmétiques de GoldRush, revient un autre jour :)"));
            $player->sendErrorSound();
            return;
        }


        $camera = new CameraSystem($player);
        $player->isInCinematic = true;
        $camera->createTiming(function (CameraSystem $cameraSystem, int $seconds, Player $player): void {

            $pos = [
                [28, 66, -10,],
                [28, 66, -8,],
                [28, 66, -6,],
                [28, 66, -4,],
                [28, 66, -2,],
                [28, 66, 0,],
                [28, 66, 2,],
                [28, 66, 4,],
                [28, 66, 6,],

                [23, 66, -10,],
                [23, 66, -8,],
                [23, 66, -6,],
                [23, 66, -4,],
                [23, 66, -2,],
                [23, 66, 0,],
                [23, 66, 2,],
                [23, 66, 4,],
                [23, 66, 6,],
            ];

            foreach ($pos as $array) {
                $pos = new Position($array[0], $array[1], $array[2], $player->getWorld());
                $player->getWorld()->addParticle($pos, new LavaParticle(), [$player]);
            }



            switch ($seconds) {
                case 0:
                    $player->sendTitle("cinematic", 1, 40, -1);
                    $cameraSystem->sendFade(1, 4, 1, DyeColor::BLACK());
                    $pk = new PlaySoundPacket();
                    $pk->soundName = "music.opening.cosmet";
                    $pk->pitch = 1;
                    $pk->x = $player->getPosition()->getX();
                    $pk->y = $player->getPosition()->getY();
                    $pk->z = $player->getPosition()->getZ();
                    $pk->volume = 30;
                    $player->getNetworkSession()->sendDataPacket($pk);
                    break;
                case 1:
                    $cameraSystem->setCameraPosition(new Vector3(26.5, 63.5, -12), EaseTypes::LINEAR, 1, new Vector3(26.5, 63.5, 12));
                    $player->setInvisible(true);
                    $player->teleport(new Position(14, 74, -8, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                    break;
                case 4:
                    $cameraSystem->setCameraPosition(new Vector3(26.5, 63.5, 5), EaseTypes::LINEAR, 15, new Vector3(26.5, 63.5, 12));
                    break;
                case 15:

                    $type = "head";
                    switch (mt_rand(0, 5)) {
                        case 0:
                            $type = "head";
                            break;
                        case 1:
                            $type = "head";
                            break;
                        case 2:
                            $type = "head";
                            break;
                        case 3:
                            $type = "head";
                            break;
                        case 4:
                            $type = "head";
                            break;
                        case 5:
                            $type = "head";
                            break;
                    }

                    $items = Cosmetiques::HEADS;
                    $chance=  mt_rand(0, 1100);
                    $selectedItem = null;
                    $chanceSelected = 1000;
                    foreach ($items as $item => $itemChance) {
                        if ($itemChance >= $chance) {
                            $cosmetNameTarget = str_replace('goldrush:', "", $selectedItem);
                            if (!Main::getInstance()->getCosmeticManager()->hasCosmetic($player, $cosmetNameTarget, "head")) {
                                $selectedItem = $item;
                                $chanceSelected = $itemChance;
                            }
                        }
                    }



                    // au cas ou il a preesque plus de cosmet a avoir
                    if (is_null($selectedItem)) {
                        $gen = Main::getInstance()->getCosmeticManager()->getCosmeticMiette($player);
                        $selectedItem = $gen[0];
                        $type = $gen[1];
                    }


                    $anim = "animation.cosmet.common";
                    if ($chanceSelected >= 800 && $chanceSelected <= 1000) {
                        $anim = "animation.cosmet.common";
                    } else if ($chanceSelected >= 500 && $chanceSelected <= 800) {
                        $anim = "animation.cosmet.rare";
                    } else if ($chanceSelected >= 100 && $chanceSelected <= 500) {
                        $anim = "animation.cosmet.epic";
                    } else if ($chanceSelected >= 0 && $chanceSelected <= 100) {
                        $anim = "animation.cosmet.legend";
                    }





                    if (!is_null($selectedItem)) {
                        $itemChoice = CustomiesItemFactory::getInstance()->get($selectedItem);
                        $cosmetName = str_replace('goldrush:', "", $selectedItem);
                        $entity = $player->getWorld()->getNearestEntity($player->getEyePos(), 35, Rideau::class);
                        if ($entity instanceof Rideau) {
                            $pk = AnimateEntityPacket::create($anim, "", "", 0, "", 0, [$entity->getId()]);
                            $player->getNetworkSession()->sendDataPacket($pk);
                        }
                        Main::getInstance()->getCosmeticManager()->addCosmetic($player->getXuid(), $cosmetName, $type);
                        CosmeticStand::setCosmeticStandForPlayer($itemChoice, $player);
                    }
                    break;
                case 20:

                    break;
                case 17 + 5:
                    $cameraSystem->setCameraPosition(new Vector3(26.5, 63.5, 9), EaseTypes::LINEAR, 1, new Vector3(26.5, 63.5, 12));
                    break;
            }
        });
    }



    public static function sendDoorOpenCinematic(Player $player, string $boxType, Item $keyItem): void {
        $camera = new CameraSystem($player);
        $player->isInCinematic = true;
        $camera->createTiming(function (CameraSystem $cameraSystem, int $seconds, Player $player) use ($boxType, $keyItem) : void {
            switch ($seconds) {
                case 0:
                    $player->sendTitle("cinematic", 1, 40, -1);
                    $cameraSystem->sendFade(1, 4, 1, DyeColor::BLACK());
                    break;
                case 1:
                    $cameraSystem->setCameraPosition(new Vector3(10.5, 65, 8), EaseTypes::LINEAR, 1, new Vector3(10.5, 65, -24));
                    $player->setInvisible(true);
                    $player->teleport(new Position(14, 74, -8, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                    break;
                case 4:
                    $cameraSystem->setCameraPosition(new Vector3(10.5, 65, -6), EaseTypes::LINEAR, 5, new Vector3(10.5, 65, -24));
                    break;
                case 9:
                    $entity = $player->getWorld()->getNearestEntity($player->getEyePos(), 35, DoorBox::class);
                    if ($entity instanceof DoorBox) {
                        $pk = AnimateEntityPacket::create("animation.door_box.open", "", "", 0, "", 0, [$entity->getId()]);
                        $player->getNetworkSession()->sendDataPacket($pk);
                    }
                    $cameraSystem->setCameraPosition(new Vector3(10.5, 63.5, -9), EaseTypes::LINEAR, 5, new Vector3(10.5, 65, -24));
                    $cameraSystem->addShakeCamera(0.1, 2, ShakeTypes::TYPE_POSITIONAL);

                    $pk = new PlaySoundPacket();
                    $pk->soundName = "music.door_box.open";
                    $pk->pitch = 1;
                    $pk->x = $player->getPosition()->getX();
                    $pk->y = $player->getPosition()->getY();
                    $pk->z = $player->getPosition()->getZ();
                    $pk->volume = 10;
                    $player->getNetworkSession()->sendDataPacket($pk);
                    break;
                case 11:
                    $cameraSystem->addShakeCamera(0.3, 1, ShakeTypes::TYPE_POSITIONAL);
                    break;
                case 12:
                    $cameraSystem->addShakeCamera(0.4, 1, ShakeTypes::TYPE_POSITIONAL);
                    break;
                case 13:
                    $cameraSystem->addShakeCamera(0.5, 3, ShakeTypes::TYPE_POSITIONAL);
                    break;
                case 16:
                    $cameraSystem->setCameraPosition(new Vector3(10.5, 63.5, -20), EaseTypes::LINEAR, 1, new Vector3(10.5, 65, -24));
                    break;
                case 17:
                    for ($i = 0; $i < 5; $i++) {
                        $player->getWorld()->addParticle(new Vector3(10.5, 63.5, -21), new ExplodeParticle(), [$player]);
                    }
                    $player->getWorld()->addParticle(new Vector3(10.5, 63.5, -20), new ExplodeParticle(), [$player]);
                    $player->getWorld()->addSound(new Vector3(10.5, 63.5, -21), new ExplodeSound(), [$player]);
                    $inv = new ChestInventory();
                    $itemsBox = Main::getInstance()->getBoxManager()->getItemsWithBox($boxType);
                    $items = array_values($itemsBox);
                    $inv->setContents($items);
                    $inv->setName("CSGO_BOX_" . strtoupper($boxType));
                    $inv->setViewOnly(true);
                    $inv->send($player);
                    $index = 0;
                    $timing = new TimingsSystem();
                    $player->timeBoxOpen = 0;
                    $chance = mt_rand(0, 1500);
                    $selectedItem = $items[0];
                    foreach ($itemsBox as $itemChance => $item) {
                        if ($itemChance >= $chance) {
                            $selectedItem = $item;
                        }
                    }

                    $player->sendTitle("break", 1, 1, -1);
                    $player->getInventory()->removeItem($keyItem->setCount(1));
                    Main::getInstance()->jobsStorage->addItemInStorage($player, $selectedItem);
                    Main::getInstance()->jobsStorage->saveUserCache($player);
                    $timing->createTiming(function (TimingsSystem $timingsSystem, int $second) use ($inv, $items, &$index, $player, $itemsBox, $cameraSystem, $keyItem, $selectedItem)  {
                        if (!$player->isConnected()){
                            $timingsSystem->stopTiming();
                            return;
                        }
                        if ($second < 40) {
                            for ($i = 0; $i < $inv->getSize(); $i++) {
                                $inv->setItem($i, VanillaItems::AIR());
                            }

                            for ($i = 0; $i < $inv->getSize(); $i++) {
                                $inv->setItem($i, $items[($index + $i) % count($items)]);
                            }

                            $inv->send($player);
                            $player->getWorld()->addSound($player->getEyePos(), new ClickSound(), [$player]);

                            $index = ($index + 1) % count($items);
                        } elseif ($second >= 40 && $second < 60) {
                            if ($player->timeBoxOpen >= 2) {
                                $player->timeBoxOpen = 0;
                                for ($i = 0; $i < $inv->getSize(); $i++) {
                                    $inv->setItem($i, VanillaItems::AIR());
                                }

                                for ($i = 0; $i < $inv->getSize(); $i++) {
                                    $inv->setItem($i, $items[($index + $i) % count($items)]);
                                }

                                $inv->send($player);
                                $player->getWorld()->addSound($player->getEyePos(), new ClickSound(), [$player]);

                                $index = ($index + 1) % count($items);
                            }
                        } elseif ($second >= 60 && $second <= 100) {
                            if ($player->timeBoxOpen >= 4) {
                                $player->timeBoxOpen = 0;
                                for ($i = 0; $i < $inv->getSize(); $i++) {
                                    $inv->setItem($i, VanillaItems::AIR());
                                }

                                for ($i = 0; $i < $inv->getSize(); $i++) {
                                    $inv->setItem($i, $items[($index + $i) % count($items)]);
                                }

                                if ($second === 82) {
                                    if ($selectedItem instanceof Item) {
                                        $inv->setItem(9, $selectedItem);
                                        $inv->setItem(26, $selectedItem);
                                        $items[9] = $selectedItem;
                                        $items[26] = $selectedItem;
                                    }
                                }

                                if ($second === 86) {
                                    if ($selectedItem instanceof Item) {
                                        $inv->setItem(8, $selectedItem);
                                        $inv->setItem(25, $selectedItem);
                                        $items[8] = $selectedItem;
                                        $items[25] = $selectedItem;
                                    }
                                }


                                if ($second === 90) {
                                    if ($selectedItem instanceof Item) {
                                        $inv->setItem(7, $selectedItem);
                                        $inv->setItem(24, $selectedItem);
                                        $items[7] = $selectedItem;
                                        $items[24] = $selectedItem;
                                    }
                                }

                                if ($second === 94) {
                                    if ($selectedItem instanceof Item) {
                                        $inv->setItem(6, $selectedItem);
                                        $inv->setItem(23, $selectedItem);
                                        $items[6] = $selectedItem;
                                        $items[23] = $selectedItem;
                                    }
                                }

                                if ($second === 98) {
                                    if ($selectedItem instanceof Item) {
                                        $inv->setItem(5, $selectedItem);
                                        $inv->setItem(22, $selectedItem);
                                        $items[5] = $selectedItem;
                                        $items[22] = $selectedItem;
                                    }
                                }


                                $inv->send($player);
                                $player->getWorld()->addSound($player->getEyePos(), new ClickSound(), [$player]);

                                $index = ($index + 1) % count($items);
                            }
                        } else {
                            if ($second === 105) {

                                $itemSet = $inv->getItem(5);
                                $inv->clearAll();
                                $player->sendSuccessSound();
                                if ($selectedItem instanceof Item) {
                                    $inv->setItem(5, $itemSet);
                                }

                            } elseif ($second === 130) {
                                $timingsSystem->stopTiming();
                                $player->sendSuccessSound();
                                $player->removeCurrentWindow();
                                $player->teleport(new Position(20, 85, 4, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                                $player->setInvisible(false);
                                $player->isInCinematic = false;
                                $player->sendMessage(Messages::message("§fVotre récompense a été ajoutée dans votre §6inventaire§f de récompenses. §l§6/reward"));
                                $cameraSystem->stopTiming();
                            }
                        }
                        $player->timeBoxOpen++;
                    }, 2);
                    break;
                case 40:
                    $player->isInCinematic = false;
                    $player->removeCurrentWindow();
                    $player->teleport(new Position(20, 85, 4, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                    $player->setInvisible(false);
                    $cameraSystem->stopTiming();
                    break;
            }
        });
    }
}