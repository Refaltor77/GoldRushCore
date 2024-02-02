<?php

namespace core\listeners\types\blocks;

use core\blocks\BlockHistoryData;
use core\blocks\blocks\MonsterSpawner;
use core\blocks\crops\ObsidianCrops;
use core\blocks\DurabilityObsidian;
use core\commands\executors\staff\Admin;
use core\items\backpacks\BackpackFarm;
use core\items\crops\SeedsObsidian;
use core\items\tools\PickaxeSpawner;
use core\managers\factions\FactionManager;
use core\settings\BlockIds;
use core\utils\Utils;
use corepvp\items\tools\amethyst\AmethystHammer;
use core\items\tools\FarmTools;
use core\items\tools\lumberjack\AbstractWoodenAxe;
use core\listeners\BaseEvent;
use core\Main;
use core\messages\Messages;
use core\settings\Ids;
use core\traits\SoundTrait;
use core\traits\UtilsTrait;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Fire;
use pocketmine\block\Crops;
use pocketmine\block\Opaque;
use pocketmine\block\Transparent;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Axe;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\handler\ResourcePacksPacketHandler;
use pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackStackEntry;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\sound\BlockBreakSound;

class BlockBreak extends BaseEvent
{
    use SoundTrait;

    public function BlockSpreadEvent(BlockSpreadEvent $event) {
        $block = $event->getBlock();
        if($block instanceof Fire){
            $event->cancel();
        }
    }


    public function breakBlockEnchantHandler(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $item = $event->getItem();



        if ($player->hasPlayer) {
            if (Main::getInstance()->getFactionManager()->isInClaim($block->getPosition())) {
                if (Main::getInstance()->getFactionManager()->getFactionNameInClaim($block->getPosition()) !==
                    Main::getInstance()->getFactionManager()->getFactionName($player->getXuid())
                ) {

                    if ($block instanceof MonsterSpawner && $player->getInventory()->getItemInHand() instanceof PickaxeSpawner) {

                    } else $event->cancel();
                } else {
                    if (Main::getInstance()->getFactionManager()->isInFaction($player->getXuid())) {
                        if (Main::getInstance()->getFactionManager()->getRankMember($player->getXuid(),
                                Main::getInstance()->getFactionManager()->getFactionName($player->getXuid()))
                            === FactionManager::RECRUE) {
                            if ($block instanceof MonsterSpawner && $player->getInventory()->getItemInHand() instanceof PickaxeSpawner) {

                            } else $event->cancel();
                        }
                    }
                }
            }
        }



        $drops = $event->getDrops();

        foreach ($drops as $itemDrop) {
            if (Main::XUID_SERVER === "XUID-GOLDRUSH-SERVER-GAME2") {
                Main::getInstance()->getBlockBreakManager()->addItemBreak($player, $itemDrop);
            }
        }


        if ($item instanceof Axe || $item instanceof Pickaxe || $item instanceof Shovel) {
            if (method_exists(get_class($item), "getTextureString")) {
                if ($item->hasEnchantment(VanillaEnchantments::EFFICIENCY())) {
                    $lvl = $item->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY());
                    $explode = explode("-", $item->identifierString);
                    if (isset($explode[1])) {
                        $lvlItem = $explode[1];
                        if ($lvlItem != $lvl) {
                            $newItem = CustomiesItemFactory::getInstance()->get("goldrush:" . $item->getTextureString() . "_efficiency-" . $lvl);
                            if ($newItem instanceof Durable) {
                                $newItem->setDamage($item->getDamage());
                                foreach ($item->getEnchantments() as $enchantment) {
                                    $newItem->addEnchantment($enchantment);
                                }
                                $player->getInventory()->setItemInHand($newItem);
                            }
                        }
                    } else {
                        $newItem = CustomiesItemFactory::getInstance()->get($item->identifierString . "_efficiency-" . $lvl);
                        if ($newItem instanceof Durable) {
                            $newItem->setDamage($item->getDamage());
                            foreach ($item->getEnchantments() as $enchantment) {
                                $newItem->addEnchantment($enchantment);
                            }
                            $player->getInventory()->setItemInHand($newItem);
                        }
                    }
                }
            }
        }
    }


    public function onBreakBlock(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if (!Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($block->getPosition())['break']) {
            return;
        }

        if ($block instanceof Crops && $player->getInventory()->getItemInHand() instanceof Axe) {
            $event->cancel();
        }


        $solids = [
            BlockTypeIds::STONE,
        ];

        if (in_array($block->getTypeId(), $solids)) {
            Main::getInstance()->getTopLuckManager()->addSolid($player);
        }

        $itemInHand = $player->getInventory()->getItemInHand();

        if ($itemInHand instanceof AmethystHammer) {
                if ($itemInHand->hasEnchantment(VanillaEnchantments::EFFICIENCY())) {
                    $itemInHand->removeEnchantment(VanillaEnchantments::EFFICIENCY());
                    $player->getInventory()->setItemInHand($itemInHand);
                }
        }


        if ($itemInHand instanceof AbstractWoodenAxe) {
            $tag = $itemInHand->getNamedTag()->getTag("lumberjack");
            if ($tag instanceof CompoundTag) {
                $break = $tag->getInt('block_break');
                $lvl = $tag->getInt('level');

                if ($lvl === 8) return;

                $xpRequis = AbstractWoodenAxe::ALL_LVL[$lvl + 1];


                if ($block->getBreakInfo()->getToolType() === BlockToolType::AXE) {
                    if ($break + 1 >= $xpRequis) {
                        $id = AbstractWoodenAxe::LVL_ITEM[$lvl + 1];
                        $item = CustomiesItemFactory::getInstance()->get($id);
                        if ($item instanceof Item) {
                            $lore = [
                                "§6---",
                                "§l§eDescription:§r§f La hache du bûcheron a été créée\ndans une forge mystique.\nUn nom revient souvent dans la\nlégende : §6Sylvanar.",
                                "§6---",
                                "§e§lNiveau: §r§f" . $item->getLvl() . " ",
                                "§eXP: §f" . $item->calculBarXp() . " ",
                                "§6---",
                                "§eRareté: §f§l§eCOMMUN"
                            ];


                            $item->setLore($lore);

                            $this->sendSuccessSound($player);
                            $player->sendMessage(Messages::message("§fBravo ! Votre hache du bûcheron est passée au niveau §6§l" . $item->getLvl()));
                            $player->getInventory()->setItemInHand($item);
                        }
                    } else {
                        $lore = [
                            "§6---",
                            "§l§eDescription:§r§f La hache du bûcheron a été créée\ndans une forge mystique.\nUn nom revient souvent dans la\nlégende : §6Sylvanar.",
                            "§6---",
                            "§e§lNiveau: §r§f" . $itemInHand->getLvl() . " ",
                            "§eXP: §f" . $itemInHand->calculBarXp() . " ",
                            "§6---",
                            "§eRareté: §f§l§eCOMMUN"
                        ];


                        $itemInHand->setLore($lore);
                        $tag->setInt("block_break", $break + 1);
                        $itemInHand->getNamedTag()->setTag("lumberjack", $tag);
                        $player->getInventory()->setItemInHand($itemInHand);
                    }
                }
            }
        }



        if (!$event->isCancelled()) {
            if (Main::getInstance()->getFactionManager()->isInFaction($player->getXuid())) {
                $itemQuest = Main::getInstance()->getFactionManager()->getItemQuest();

                foreach ($event->getDrops() as $itemDrop) {
                    if ($itemQuest->equals($itemDrop, false, false)) {
                        Main::getInstance()->getFactionManager()->addItemQuestFaction($player, $itemDrop->getCount());
                    }
                }
            }
        }
    }
}