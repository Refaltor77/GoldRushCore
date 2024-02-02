<?php

namespace core\items\tools;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Slider;
use core\api\gui\ChestInventory;
use core\messages\Messages;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class VoidStone extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_ITEMS,
            CreativeInventoryInfo::CATEGORY_ITEMS,
        );

        parent::__construct($identifier, "Void Stone");

        $this->initComponent('void_stone', $inventory);

        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Cet objet vous permettra de garder votre pierre et votre deepslate dans un même endroit.",
            "§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMUN",
        ]);

        $this->generateNBT();
    }


    public function generateNBT(): void
    {
        $nbt = $this->getNamedTag();
        if ($nbt->getString('is_created', 'none') === 'none') {
            $nbt->setString('is_created', uniqid());
            $nbt->setInt('cobblestone', 0);
            $nbt->setInt('deepslate', 0);
        }
    }

    public function setCobble(int $count = 1): void
    {
        $this->getNamedTag()->setInt('cobblestone', $count);
    }

    public function setDeepslate(int $count = 1): void
    {
        $this->getNamedTag()->setInt('deepslate', $count);
    }

    public function removeDeepslate(int $count): void
    {
        $this->getNamedTag()->setInt('deepslate', $this->getNamedTag()->getInt('deepslate', 0) - $count);
    }

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $this->generateNBT();
        $inv = new ChestInventory();
        $inv->setName("voidstone");


        $inv->setContents([
            VanillaBlocks::COBBLESTONE()->asItem()->setCustomName("Nombre: " . $this->getCobbleCount()),
            VanillaBlocks::COBBLED_DEEPSLATE()->asItem()->setCustomName("Nombre: " . $this->getDeepslateCount()),
        ]);


        $inv->setClickCallback(function (Player $player, Inventory $inventory, Item $target, Item $source, int $slot) use ($inv): void {
            $inv->transacCancel();
            $player->removeCurrentWindow();
            if (in_array($slot, [0, 1])) {
                switch ($slot) {
                    case 0:
                        if ($this->getCobbleCount() <= 0) {
                            $player->sendErrorSound();
                            $player->sendMessage(Messages::message("§cVous n'avez aucune pierre taillée dans votre voidstone."));
                            return;
                        }

                        $player->sendForm(new CustomForm("Retirer de la pierre taillée", [
                            new Slider("Montant", 1, $this->getCobbleCount())
                        ], function (Player $player, CustomFormResponse $response): void {
                            $sliderValue = (int)$response->getSlider()->getValue();


                            if ($sliderValue > $this->getCobbleCount()) {
                                $player->sendErrorSound();
                                $player->sendMessage(Messages::message("§cVousn ne pouvez pas retirer plus de pierre taillée que votre voidstone possède."));
                                $player->removeCurrentWindow();
                                return;
                            }

                            $item = VanillaBlocks::COBBLESTONE()->asItem();
                            $item->setCount($sliderValue);
                            if (!$player->getInventory()->canAddItem($item)) {
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                                $player->sendErrorSound();
                                return;
                            }

                            if (!$this->equalsExact($player->getInventory()->getItemInHand())) {
                                $player->sendErrorSound();
                                $player->sendMessage(Messages::message("§cVous devez tenir votre voidstone dans votre main."));
                                return;
                            }

                            $this->removeCobble($sliderValue);

                            $player->getInventory()->addItem($item);
                            $player->getInventory()->setItemInHand($this);
                        }));
                        break;
                    case 1:
                        if ($this->getDeepslateCount() <= 0) {
                            $player->sendErrorSound();
                            $player->sendMessage(Messages::message("§cVous n'avez aucune pierre des abîmes dans votre voidstone."));
                            return;
                        }

                        $player->sendForm(new CustomForm("Retirer de la pierre des abîmes", [
                            new Slider("Montant", 1, $this->getDeepslateCount())
                        ], function (Player $player, CustomFormResponse $response): void {
                            $sliderValue = $response->getSlider()->getValue();


                            if ($sliderValue > $this->getDeepslateCount()) {
                                $player->sendErrorSound();
                                $player->sendMessage(Messages::message("§cVousn ne pouvez pas retirer plus de pierre des abîmes que votre voidstone possède."));
                                return;
                            }

                            $item = VanillaBlocks::DEEPSLATE()->asItem();
                            $item->setCount($sliderValue);
                            if (!$player->getInventory()->canAddItem($item)) {
                                $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                                $player->sendErrorSound();
                                return;
                            }

                            if (!$this->equalsExact($player->getInventory()->getItemInHand())) {
                                $player->sendErrorSound();
                                $player->sendMessage(Messages::message("§cVous devez tenir votre voidstone dans votre main."));
                                return;
                            }

                            $this->removeDeepslate($sliderValue);

                            $player->getInventory()->addItem($item);
                            $player->getInventory()->setItemInHand($this);
                        }));
                        break;
                }
            } else $inv->transacCancel();
            $player->removeCurrentWindow();
        });
        $inv->send($player);
        return ItemUseResult::SUCCESS();
    }

    public function getCobbleCount(): int
    {
        return $this->getNamedTag()->getInt('cobblestone');
    }

    public function getDeepslateCount(): int
    {
        return $this->getNamedTag()->getInt('deepslate');
    }

    public function removeCobble(int $count): void
    {
        $this->getNamedTag()->setInt('cobblestone', $this->getNamedTag()->getInt('cobblestone', 0) - $count);
    }

    public function addItemInVoidStone(Item $item): void
    {
        if ($item->getTypeId() === VanillaBlocks::COBBLESTONE()->asItem()->getTypeId()) {
            $this->addCobble($item->getCount());
        }

        if ($item->getTypeId() === VanillaBlocks::COBBLED_DEEPSLATE()->asItem()->getTypeId()) {
            $this->addDeepslate($item->getCount());
        }
    }

    public function addCobble(int $count = 1): void
    {
        $this->getNamedTag()->setInt('cobblestone', $this->getNamedTag()->getInt('cobblestone', 0) + $count);
    }

    public function addDeepslate(int $count = 1): void
    {
        $this->getNamedTag()->setInt('deepslate', $this->getNamedTag()->getInt('deepslate', 0) + $count);
    }
}