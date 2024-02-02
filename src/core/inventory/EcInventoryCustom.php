<?php

namespace core\inventory;

use core\api\form\ModalForm;
use core\items\backpacks\BackpackFarm;
use core\items\backpacks\BackpackFossil;
use core\items\backpacks\BackpackOre;
use core\Main;
use core\managers\enderChest\EnderChestSlot;
use core\messages\Messages;
use pocketmine\block\inventory\AnimatedBlockInventoryTrait;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\tile\EnderChest;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\DelegateInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerEnderInventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\EnderChestCloseSound;
use pocketmine\world\sound\EnderChestOpenSound;
use pocketmine\world\sound\Sound;
use tedo0627\inventoryui\CustomInventory;


class EcInventoryCustom extends CustomInventory
{
    public function __construct(int $size = 5, string $title = "EC", ?int $verticalLength = null, ?Player $who = null)
    {
        parent::__construct($size, $title, $verticalLength);
        $ec = $who->getEnderInventory();

        $itemRestricted = VanillaBlocks::BARRIER()->asItem();
        $slots = Main::getInstance()->getEnderChestManager()->getSlots($who);
        foreach ($slots as $index => $isRestricted) {
            $itemRestricted->setNamedTag($itemRestricted->getNamedTag()->setInt('price', EnderChestSlot::PRICE[$index])->setInt('index', $index));
            if (!$isRestricted) {
                $this->setItem((int)$index, $itemRestricted->setLore(
                    ["", "§c" . EnderChestSlot::PRICE[$index] . "$", "", "§f----------", "§o§7Tape sur l'item", "§o§7pour acheter le slot", "§f----------"])->setCustomName("§4- §fSlot à vendre §4-"));
            }
        }

        $i = 0;
        while ($i !== 5) {
            if (!$ec->getItem($i)->isNull()) {
                $this->setItem($i, $ec->getItem($i));
            }
            $i++;
        }


    }



    public function click(Player $player, int $slot, Item $source, Item $target): bool
    {
        if (!in_array($slot, [0, 1, 2, 3, 4])) {
            return true;
        }

        $nbt = $source->getNamedTag();

        $blacklistClass = [
            BackpackFarm::class,
            BackpackOre::class,
            BackpackFossil::class
        ];


        if (in_array($source::class, $blacklistClass)) return true;
        if (in_array($target::class, $blacklistClass)) return true;



        $itemRestricted = VanillaBlocks::BARRIER()->asItem();
        if ($nbt->getInt("price", 404) !== 404) {
            $price = $nbt->getInt('price', 404);
            $index = $nbt->getInt('index', 404);
            if ($price !== 404 && $index !== 404) {
                $player->removeCurrentWindow();
                $player->sendForm(new ModalForm(
                    "§6- §fAcheter le slot §6-",
                    "§fÊtes vous sur de vouloir acheter ce slot ?\n\nPrix §8: §6$price" . "$",
                    function (Player $player, bool $bool) use ($price, $index): void {
                        $money = Main::getInstance()->getEconomyManager();
                        if ($bool) {
                            Main::getInstance()->getEconomyManager()->getMoneySQL($player,
                                function (Player $player, int $money) use($price, $index) : void {
                                    if ($money >= $price) {
                                        $player->sendSuccessSound();
                                        Main::getInstance()->getEconomyManager()->removeMoney($player, $price);
                                        Main::getInstance()->getEnderChestManager()->setSlot($index, true, $player);
                                        $player->getEnderInventory()->removeItem($this->getItem($index));
                                    } else {
                                        $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent."));
                                        $player->sendErrorSound();
                                    }
                                });
                        }
                    }
                ));
            }
        }

        if ($source->getTypeId() === $itemRestricted->getTypeId()) {
          return true;
        } elseif ($target->getTypeId() === $itemRestricted->getTypeId()) {
            return true;
        }
        return parent::click($player, $slot, $source, $target);
    }


    public function onClose(Player $who): void
    {
        $who->getEnderInventory()->clearAll();
        foreach ($this->getContents() as $slot => $item) {
            $who->getEnderInventory()->setItem($slot, $item);
        }
        Main::getInstance()->getInventoryManager()->saveInventory($who, true, false);
        parent::onClose($who);
    }
}
