<?php

namespace core\listeners\types\inventory;

use core\api\form\ModalForm;
use core\api\gui\ChestInventory;
use core\items\backpacks\BackpackFarm;
use core\items\backpacks\BackpackOre;
use core\items\horse\HorseArmorAmethyst;
use core\items\horse\HorseArmorCopper;
use core\items\horse\HorseArmorEmerald;
use core\items\horse\HorseArmorGold;
use core\items\horse\HorseArmorPlatinum;
use core\listeners\BaseEvent;
use core\Main;
use core\managers\enderChest\EnderChestSlot;
use core\managers\sync\SyncTypes;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\block\inventory\EnderChestInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class InventoryOpen extends BaseEvent
{
    use SoundTrait;

    public function onOpen(InventoryOpenEvent $event): void
    {
        $inventory = $event->getInventory();
        if ($inventory instanceof EnderChestInventory) {
            $itemRestricted = VanillaBlocks::BARRIER()->asItem();
            $itemRestricted->setNamedTag($itemRestricted->getNamedTag()->setString('restricted', 'true'));
            $slots = Main::getInstance()->getEnderChestManager()->getSlots($event->getPlayer());
            foreach ($slots as $index => $isRestricted) {
                $itemRestricted->setNamedTag($itemRestricted->getNamedTag()->setInt('price', EnderChestSlot::PRICE[$index])->setInt('index', $index));
                if (!$isRestricted) {
                    $inventory->setItem((int)$index, $itemRestricted->setLore(
                        ["", "§c" . EnderChestSlot::PRICE[$index] . "$", "", "§f----------", "§o§7Tape sur l'item", "§o§7pour acheter le slot", "§f----------"])->setCustomName("§4- §fSlot à vendre §4-"));
                }
            }
        }
    }

    public function onEnderchestTransaction(InventoryTransactionEvent $e): void
    {
        $transactions = $e->getTransaction()->getActions();

        foreach ($transactions as $transaction) {
            $item = $transaction->getSourceItem();
            $target = $transaction->getTargetItem();
            $nbt = ($item->getNamedTag() ?? new CompoundTag());


            $classMontures = [
                HorseArmorCopper::class,
                HorseArmorEmerald::class,
                HorseArmorAmethyst::class,
                HorseArmorPlatinum::class,
                HorseArmorGold::class
            ];


            $player = $e->getTransaction()->getSource();
            if (in_array($transaction->getTargetItem()::class, $classMontures)) {
                if ($transaction->getTargetItem()->getNamedTag()->getString('xuid', 'none') !== $player->getXuid()) {
                    $player->sendErrorSound();
                    $player->sendMessage(Messages::message("§cCette monture ne vous appartient pas !"));
                    $e->cancel();
                }
            }

            if (in_array($transaction->getSourceItem()::class, $classMontures)) {
                if ($transaction->getSourceItem()->getNamedTag()->getString('xuid', 'none') !== $player->getXuid()) {
                    $player->sendErrorSound();
                    $player->sendMessage(Messages::message("§cCette monture ne vous appartient pas !"));
                    $e->cancel();
                }
            }




            foreach ($e->getTransaction()->getInventories() as $inv) {
                $players = $inv->getViewers();
                foreach ($players as $player) {
                    if ($player instanceof CustomPlayer) {
                        if ($inv instanceof PlayerOffHandInventory) {
                            $inv->setContents([]);
                            $player->sendMessage(Messages::message("§cCosmétique retiré."));
                            $e->cancel();
                        }
                        if (Main::getInstance()->getStaffManager()->isInStaffMode($player)) {
                            $e->cancel();
                        } else {
                            Main::getInstance()->getDatabaseSyncManager()->addPlayerQueue($player, SyncTypes::INVENTORY);
                        }
                    }
                }


                if ($inv instanceof EnderChestInventory) {
                    if ($transaction->getTargetItem() instanceof BackpackOre || $transaction->getSourceItem() instanceof BackpackFarm) {
                        $e->cancel();
                    }

                    if ($nbt->getString("restricted", "null") !== "null") {
                        $price = $nbt->getInt('price', 404);
                        $index = $nbt->getInt('index', 404);
                        if ($price !== 404 && $index !== 404) {
                            $e->getTransaction()->getSource()->getNetworkSession()->getInvManager()->onCurrentWindowRemove();
                            $e->getTransaction()->getSource()->sendForm(new ModalForm(
                                "§6- §fAcheter le slot §6-",
                                "§fÊtes vous sur de vouloir acheter ce slot ?\n\nPrix §8: §6$price" . "$",
                                function (Player $player, bool $bool) use ($price, $index): void {
                                    $money = Main::getInstance()->getEconomyManager();
                                    if ($bool) {
                                        Main::getInstance()->getEconomyManager()->getMoneySQL($player,
                                            function (Player $player, int $money) use($price, $index) : void {
                                                if ($money >= $price) {
                                                    $this->sendSuccessSound($player);
                                                    Main::getInstance()->getEconomyManager()->removeMoney($player, $price);
                                                    Main::getInstance()->getEnderChestManager()->setSlot($index, true, $player);
                                                    $player->getEnderInventory()->setItem($index, VanillaBlocks::AIR()->asItem());
                                                } else {
                                                    $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent."));
                                                    $this->sendErrorSound($player);
                                                }
                                        });
                                    }
                                }
                            ));
                        }
                        $e->cancel();
                    }
                }
            }
        }
    }
}