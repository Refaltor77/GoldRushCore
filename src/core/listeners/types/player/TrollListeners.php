<?php

namespace core\listeners\types\player;

use core\commands\executors\staff\Troll;
use core\listeners\BaseEvent;
use core\Main;
use core\messages\Messages;
use pocketmine\block\Grass;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Armor;
use pocketmine\item\Fertilizer;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\sound\BellRingSound;
use pocketmine\world\sound\BlazeShootSound;
use pocketmine\world\sound\EndermanTeleportSound;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\sound\GhastShootSound;

class TrollListeners extends BaseEvent
{
    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();
        $particle = new HappyVillagerParticle();
        $getBlockPosition = $block->getPosition();
        $getWorld = $getBlockPosition->getWorld();
        $rightClickBlock = PlayerInteractEvent::RIGHT_CLICK_BLOCK;
        if ($block instanceof Grass) {
            if ($event->getItem() instanceof Fertilizer) {
                if ($event->getAction() === $rightClickBlock) {
                    do {
                        $z1 = mt_rand(5, 20) / 10;
                        $y1 = mt_rand(11, 21) / 10;
                        $vector = $getBlockPosition->add(0.5, $y1, $z1);
                        $getWorld->addParticle($vector, $particle);

                        $x1 = mt_rand(-20, 5) / 10;
                        $z2 = mt_rand(5, 20) / 10;
                        $y2 = mt_rand(11, 21) / 10;
                        $vector = $getBlockPosition->add($x1, $y2, $z2);
                        $getWorld->addParticle($vector, $particle);

                        $x3 = mt_rand(-20, 5) / 10;
                        $y3 = mt_rand(11, 21) / 10;
                        $vector = $getBlockPosition->add($x3, $y3, 0.5);
                        $getWorld->addParticle($vector, $particle);

                        $x4 = mt_rand(-20, 5) / 10;
                        $z3 = mt_rand(-20, 5) / 10;
                        $y4 = mt_rand(11, 21) / 10;
                        $vector = $getBlockPosition->add($x4, $y4, $z3);
                        $getWorld->addParticle($vector, $particle);

                        $z4 = mt_rand(-20, 5) / 10;
                        $y5 = mt_rand(11, 21) / 10;
                        $vector = $getBlockPosition->add(0.5, $y5, $z4);
                        $getWorld->addParticle($vector, $particle);

                        $x5 = mt_rand(5, 20) / 10;
                        $z5 = mt_rand(-20, 5) / 10;
                        $y6 = mt_rand(11, 21) / 10;
                        $vector = $getBlockPosition->add($x5, $y6, $z5);
                        $getWorld->addParticle($vector, $particle);

                        $x6 = mt_rand(5, 20) / 10;
                        $y7 = mt_rand(11, 21) / 10;
                        $vector = $getBlockPosition->add($x6, $y7, 0.5);
                        $getWorld->addParticle($vector, $particle);

                        $x7 = mt_rand(5, 20) / 10;
                        $z6 = mt_rand(5, 20) / 10;
                        $y8 = mt_rand(11, 21) / 10;
                        $vector = $getBlockPosition->add($x7, $y8, $z6);
                        $getWorld->addParticle($vector, $particle);
                        /** @phpstan-ignore-next-line */
                    } while (false);
                }
            }
        }
        if ($event->getItem() instanceof (VanillaItems::REDSTONE_DUST())) {
            if ($event->getAction() == $rightClickBlock) {
                if (isset(Troll::$cache[$event->getPlayer()->getXuid()])) {
                    $inv = $event->getPlayer()->getInventory();
                    if ($event->getItem()->getName() == "§c<==") {
                        if (Troll::$cache[$event->getPlayer()->getXuid()] == 1) {
                            return;
                        } else {
                            Troll::$cache[$event->getPlayer()->getXuid()]--;
                            if (Troll::$cache[$event->getPlayer()->getXuid()] == 2) {
                                $inv->setItem(1, VanillaItems::EMERALD()->setCustomName("§oFake op"));
                                $inv->setItem(2, VanillaItems::DIAMOND()->setCustomName("§oFake restart"));
                                $inv->setItem(3, VanillaItems::LEATHER()->setCustomName("§oFlip"));
                                $inv->setItem(4, VanillaBlocks::BEDROCK()->asItem()->setCustomName("§oNo pick up"));
                                $inv->setItem(5, VanillaBlocks::COBBLESTONE()->asItem()->setCustomName("§oCobble is life"));
                                $inv->setItem(6, VanillaItems::BLAZE_ROD()->setCustomName("§oSwap position"));
                            } elseif (Troll::$cache[$event->getPlayer()->getXuid()] == 1) {
                                $inv->setItem(1, VanillaItems::EMERALD()->setCustomName("§oDrop inventory"));
                                $inv->setItem(2, VanillaBlocks::TNT()->asItem()->setCustomName("§oBoom"));
                                $inv->setItem(3, VanillaBlocks::FIRE()->asItem()->setCustomName("§oBurn"));
                                $inv->setItem(4, VanillaItems::PAPER()->setCustomName("§oLigthning"));
                                $inv->setItem(5, VanillaItems::HEART_OF_THE_SEA()->setCustomName("§oNoob"));
                                $inv->setItem(6, VanillaBlocks::BARRIER()->asItem()->setCustomName("§oNo break block"));
                                $inv->setItem(7, VanillaItems::DIAMOND_PICKAXE()->setCustomName("§oFake ban"));
                            }
                        }
                    } elseif ($event->getItem()->getName() == "§c==>") {
                        if (Troll::$cache[$event->getPlayer()->getXuid()] == 3) {
                            return;
                        } else {
                            Troll::$cache[$event->getPlayer()->getXuid()]++;
                            if (Troll::$cache[$event->getPlayer()->getXuid()] == 3) {
                                $inv->setItem(1, VanillaItems::PAPER()->setCustomName("§oFake drop coordinates"));
                                $inv->setItem(2, VanillaItems::BONE()->setCustomName("§oHaunt"));
                                $inv->setItem(3, VanillaItems::CARROT()->setCustomName("§oPush"));
                                $inv->setItem(4, VanillaBlocks::BELL()->asItem()->setCustomName("§oFake or"));
                                $inv->removeItem($inv->getItem(5));
                                $inv->removeItem($inv->getItem(6));
                                $inv->removeItem($inv->getItem(7));
                            } elseif (Troll::$cache[$event->getPlayer()->getXuid()] == 2) {
                                $inv->setItem(1, VanillaItems::EMERALD()->setCustomName("§oFake op"));
                                $inv->setItem(2, VanillaItems::DIAMOND()->setCustomName("§oFake restart"));
                                $inv->setItem(3, VanillaItems::LEATHER()->setCustomName("§oFlip"));
                                $inv->setItem(4, VanillaBlocks::BEDROCK()->asItem()->setCustomName("§oNo pick up"));
                                $inv->setItem(5, VanillaBlocks::COBBLESTONE()->asItem()->setCustomName("§oCobble is life"));
                                $inv->setItem(6, VanillaItems::BLAZE_ROD()->setCustomName("§oSwap position"));
                                $inv->setItem(7, VanillaBlocks::PUMPKIN()->asItem()->setCustomName("§oPumpkin Head"));
                            }
                        }
                    }
                }
            }

        }
    }

    public function onDamageByEntity(EntityDamageByEntityEvent $event)
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        if ($damager instanceof Player) {
            if ($entity instanceof Player) {
                if (isset(Troll::$cache[$damager->getXuid()])) {
                    $event->cancel();
                    switch ($damager->getInventory()->getItemInHand()->getName()) {
                        case "§oDrop inventory":
                            array_map(fn($item) => $entity->getWorld()->dropItem($entity->getPosition()->add(1.5, 0.5, 1.5), $item), $entity->getInventory()->getContents());
                            $entity->getInventory()->clearAll();
                            break;
                        case "§oBoom":
                            $entity->broadcastSound(new ExplodeSound(), [$entity]);
                            $entity->getWorld()->addParticle($entity->getPosition(), new ExplodeParticle());
                            break;
                        case "§oBurn":
                            $entity->getWorld()->setBlock($entity->getPosition(), VanillaBlocks::FIRE());
                            break;
                        case "§oLigthning":
                            $sound = new PlaySoundPacket();
                            $sound->x = $entity->getPosition()->x;
                            $sound->y = $entity->getPosition()->y;
                            $sound->z = $entity->getPosition()->z;
                            $sound->volume = 100;
                            $sound->pitch = 2;
                            $sound->soundName = "ambient.weather.thunder";
                            $entity->getNetworkSession()->sendDataPacket($sound);
                            $ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_CUSTOM, 5);
                            $entity->attack($ev);
                            $entity->getPosition()->getWorld()->setBlock($entity->getPosition()->asVector3(), VanillaBlocks::FIRE());
                            break;
                        case "§oNoob":
                            Server::getInstance()->broadcastMessage(Messages::message($entity->getName() . " est un gros noob"));
                            break;
                        case "§oNo break block":
                            if (isset(Main::getInstance()->troll["break"][$entity->getXuid()])) {
                                unset(Main::getInstance()->troll["break"][$entity->getXuid()]);
                            } else {
                                Main::getInstance()->troll["break"][$entity->getXuid()] = true;
                            }
                            break;
                        case "§oNo pick up":
                            if (isset(Main::getInstance()->troll["pick"][$entity->getXuid()])) {
                                unset(Main::getInstance()->troll["pick"][$entity->getXuid()]);
                            } else {
                                Main::getInstance()->troll["pick"][$entity->getXuid()] = true;
                            }
                            break;
                        case "§oFake ban":
                            $rand = mt_rand(1, 2);
                            if ($rand == 1) {
                                $reason = "SpeedHack";
                            } else {
                                $reason = "Fly";
                            }
                            $timestamp = 10000;
                            $message = "§6-----\n\n";
                            $message .= "§6[§6GoldRush Ban§6]\n";
                            $message .= "§fJoueur: §6{" . $entity->getName() . "}\n";
                            $message .= "§fRaison: §c$reason\n";
                            $message .= "§fDate de fin: §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)) . "\n";
                            $message .= "\n§6-----";
                            $entity->kick($message, "fake ban troll");
                            break;
                        case "§oFake op":
                            $entity->sendMessage("§7Vous avez obtenu les privilèges d'opérateur\n§o[CONSOLE: Attribution des droits d'opérateur à " . $entity->getName() . "]");
                            break;
                        case "§oFake restart":
                            $entity->sendMessage(messages::message("§eLe serveur redemarrera dans 30 secondes"));
                            break;
                        case "§oFlip":
                            $targetPosition = $entity->getPosition();
                            $entity->teleport(new Vector3($targetPosition->x, $targetPosition->y, $targetPosition->z));
                            break;
                        case "§oCobble is life":
                            for ($index = 0; $index < $entity->getInventory()->getSize(); $index++) {
                                if ($entity->getInventory()->getItem($index)->getTypeId() == VanillaBlocks::AIR()->getTypeId()) {
                                    $entity->getInventory()->setItem($index, VanillaBlocks::COBBLESTONE()->asItem()->setCount(64));
                                }
                            }
                            break;
                        case "§oSwap position":
                            $targetPos = $entity->getPosition();
                            $playerPos = $damager->getPosition();
                            $damager->teleport(new Vector3($playerPos->x, $playerPos->y, $playerPos->z));
                            $entity->teleport(new Vector3($targetPos->x, $targetPos->y, $targetPos->z));
                            break;
                        case "§oFake drop coordinates":
                            $entity->sendMessage(messages::message("Les coordonnées de " . $entity->getName() . " sont " . intval($entity->getPosition()->getX()) . ":" . intval($entity->getPosition()->getZ())));
                            break;
                        case "§oHaunt":
                            $rand = mt_rand(1, 3);
                            if ($rand == 1) $sound = new EndermanTeleportSound();
                            if ($rand == 2) $sound = new GhastShootSound();
                            if ($rand == 3) $sound = new BlazeShootSound();
                            $entity->broadcastSound($sound);
                            break;
                        case "§oPush";
                            $entity->setMotion(new Vector3(mt_rand(-1, 1), 1, mt_rand(-1, 1)));
                            break;
                        case "§oPumpkin Head":
                            if ($entity->getArmorInventory()->getHelmet() instanceof Armor) {
                                $helmet = $entity->getArmorInventory()->getHelmet();
                                if ($entity->getInventory()->canAddItem($helmet)) {
                                    $entity->getInventory()->addItem($helmet);
                                } else {
                                    $entity->getWorld()->dropItem($entity->getPosition(), $helmet);
                                }
                                $entity->getArmorInventory()->setHelmet(VanillaBlocks::PUMPKIN()->asItem());
                            }
                            $entity->getArmorInventory()->setHelmet(VanillaBlocks::PUMPKIN()->asItem());
                            break;
                        case "§oFake or":
                            $entity->getWorld()->addSound($entity->getEyePos(), new BellRingSound());
                            break;
                    }
                }
            }
        }
    }

    public function onPickUp(EntityItemPickupEvent $event)
    {
        $entity = $event->getEntity();
        $item = $event->getItem();
        $inventory = $event->getInventory();
        if ($entity instanceof Player) {
            if (isset(Main::getInstance()->troll["pick"][$entity->getXuid()])) {
                $event->cancel();
            }
        }
    }

    public function onPlaceBlock(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();
        $pos = $block->getPosition();
        if (isset(Troll::$cache[$player->getXuid()])) {
            $event->cancel();
        }
        $limit = 11000;
        $rankAuthorised = ["MODO+","RESPONSABLE","ADMIN"];
        if(!Server::getInstance()->isOp($player->getName()) && !in_array(Main::getInstance()->getRankManager()->getRanks($player->getXuid()),$rankAuthorised)){
            if($pos->getFloorX() > $limit || $pos->getFloorZ() > $limit || $pos->getFloorX() < -$limit || $pos->getFloorZ() < -$limit){
                $event->cancel();
            }
        }
    }

    public function onDrop(PlayerDropItemEvent $event)
    {
        $player = $event->getPlayer();
        if (isset(Troll::$cache[$player->getXuid()])) {
            $event->cancel();
        }
    }
}