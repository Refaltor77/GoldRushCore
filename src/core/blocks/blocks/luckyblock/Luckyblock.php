<?php

namespace core\blocks\blocks\luckyblock;

use core\blocks\tiles\AmethystChestTile;
use core\blocks\tiles\EmeraldChestTile;
use core\Main;
use core\player\CustomPlayer;
use core\settings\BlockIds;
use core\settings\Ids;
use core\traits\ParticleTrait;
use core\traits\SoundTrait;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class Luckyblock extends AbstractLuckyBlock
{
    use SoundTrait;
    use ParticleTrait;

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if (!$player instanceof CustomPlayer) return false;


        $chance= mt_rand(0, 1000);

        if ($chance >= 950 && $chance <= 1000) {
            $this->sendVillagerHappyParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_BLOCK));

            }), 20);
        } elseif ($chance >= 900 && $chance <= 950) {
            $this->sendExplodeParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            $this->reject(2, $player);
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)->setCount(16));
        } elseif ($chance >= 850 && $chance <= 900) {
            $this->sendVillagerHappyParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), CustomiesBlockFactory::getInstance()->get(BlockIds::AMETHYST_BLOCK));

            }), 10);

        } elseif ($chance >= 800 && $chance <= 850) {
            $this->sendVillagerHappyParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_CHEST));
                $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
                if ($tile instanceof EmeraldChestTile) {
                    $tile->getInventory()->setContents([
                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HELMET)
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_CHESTPLATE)
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_LEGGINGS)
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_BOOTS)
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)),
                        CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SWORD)
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3))
                            ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2)),
                    ]);
                }
            }), 10);
        } elseif ($chance >= 750 && $chance <= 800) {
            $this->sendExplodeParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            $this->reject(2, $player);
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(BlockIds::LUCKYBLOCK)->setCount(2));
        }elseif ($chance >= 700 && $chance <= 750) {
            $this->sendExplodeParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            $this->reject(2, $player);
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::BOTTLE_XP)->setCount(64));
        }elseif ($chance >= 650 && $chance <= 700) {
            $this->sendExplodeParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            $this->reject(2, $player);
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::FLOWER_PERCENT)->setCount(1));
        }elseif ($chance >= 600 && $chance <= 650) {
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE)->setCount(2));
            $this->sendZeus($player);
            $this->reject(2, $player);
            $player->setOnFire(5);
        }elseif ($chance >= 550 && $chance <= 600) {
            $this->sendVillagerHappyParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_CHEST));
                $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
                if ($tile instanceof EmeraldChestTile) {
                    $tile->getInventory()->setContents([
                        VanillaItems::PAPER()->setCustomName("troll"),
                        VanillaItems::DIAMOND()->setCount(64)
                    ]);
                }
            }), 10);
        }elseif ($chance >= 500 && $chance <= 550) {
            $this->sendVillagerHappyParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            $this->reject(2, $player);

            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE)->setCount(4));
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::BASE_DYNAMITE)->setCount(4));
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_INGOT)->setCount(16));
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)->setCount(4));
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), VanillaBlocks::WATER());
            }), 10);
        }elseif ($chance >= 450 && $chance <= 500) {
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::MONEY)->setCount(5));
            $this->sendVillagerHappyParticle($player);
        }elseif ($chance >= 400 && $chance <= 450) {
            $this->sendExplodeParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(BlockIds::LUCKYBLOCK)->setCount(4));
        } elseif ($chance >= 350 && $chance <= 400) {
            $player->getNetworkSession()->onChatMessage("Vous voulez que je drop les co de ma base ? Si oui me mp !");
        } elseif ($chance >= 300 && $chance <= 350) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HELMET)->setCount(1));
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD)->setCount(1));
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE)->setCount(16));
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), VanillaBlocks::WATER());
            }), 10);
        }elseif ($chance >= 250 && $chance <= 300) {
            $this->sendVillagerHappyParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), CustomiesBlockFactory::getInstance()->get(BlockIds::EMERALD_CHEST));
                $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
                if ($tile instanceof EmeraldChestTile) {
                    $tile->getInventory()->setContents([
                        CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLACK)->setCount(1),
                        CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLUE)->setCount(1),
                        CustomiesItemFactory::getInstance()->get(Ids::BERRY_YELLOW)->setCount(1),
                        CustomiesItemFactory::getInstance()->get(Ids::BERRY_PINK)->setCount(1),
                        CustomiesItemFactory::getInstance()->get(Ids::RAISIN)->setCount(1),
                        CustomiesItemFactory::getInstance()->get(Ids::EMPTY_BOTTLE)->setCount(5),
                    ]);
                }
            }), 10);
        }elseif ($chance >= 200 && $chance <= 250) {
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL)->setCount(4));
            $this->sendZeus($player);
            $this->reject(4, $player);
            $player->setOnFire(5);
        }elseif ($chance >= 150 && $chance <= 200) {
            $this->sendVillagerHappyParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
                $this->getPosition()->getWorld()->setBlock($this->getPosition(), CustomiesBlockFactory::getInstance()->get(BlockIds::PLATINUM_BLOCK));
            }), 10);
        }elseif ($chance >= 100 && $chance <= 150) {
            $this->sendExplodeParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            $this->reject(2, $player);
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::MONEY)->setCount(10));
        } else {
            $this->sendExplodeParticle($player, $this->getPosition());
            $this->sendSuccessSound($player);
            $this->reject(2, $player);
            $this->getPosition()->getWorld()->dropItem($this->getPosition(), CustomiesItemFactory::getInstance()->get(Ids::MONEY)->setCount(3));
        }

        return parent::onBreak($item, $player, $returnedItems);
    }



    private function reject(float $size = 4, ?Player $player = null)
    {
        $explosionSize = 6;

        if ($player instanceof Player) {
            $player->setMotion($player->getPosition()->add(0, 3, 0));
            $entityPos = $player->getPosition();
            $distance = $entityPos->distance($this->getPosition()) / $explosionSize;
            $motion = $entityPos->subtractVector($this->getPosition())->normalize();
            $impact = (1 - $distance) * $size;
            $player->setMotion($motion->multiply($impact));
            $this->sendExplodeParticle($player, $this->getPosition());
        }
    }


    public function sendZeus(Player $player): void {
        $pk = new AddActorPacket();
        $pk->type = "minecraft:lightning_bolt";
        $pk->actorUniqueId = 1001001;
        $pk->actorRuntimeId = 1001001;
        $pk->syncedProperties = new PropertySyncData([], []);
        $pk->metadata = [];
        $pk->motion = null;
        $pk->yaw = $player->getLocation()->getYaw();
        $pk->pitch = $player->getLocation()->getPitch();
        $pk->position = new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ());

        $sound = new PlaySoundPacket();
        $sound->soundName = "ambient.weather.thunder";
        $sound->x = $player->getPosition()->getX();
        $sound->y = $player->getPosition()->getY();
        $sound->z = $player->getPosition()->getZ();
        $sound->volume = 100;
        $sound->pitch = 1000;
    }
}