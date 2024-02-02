<?php

namespace core\items\backpacks;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Slider;
use core\api\gui\ChestInventory;
use core\Main;
use core\messages\Messages;
use core\settings\Ids;
use core\traits\SoundTrait;
use core\utils\Utils;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class BackpackFarm extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    use SoundTrait;


    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Sac du fermier';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('backpack_farm', $inventory);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le sac du fermier est une relique ancienne,\ntransmise de génération en génération. Il est réputé pour sa\nrobustesse et sa capacité à contenir une abondance de récoltes\net d'outils de ferme.",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);

        $this->generateNBT();
    }


    public function generateNBT(): void
    {
        $nbt = $this->getNamedTag();
        if ($nbt->getString('is_created', 'none') === 'none') {
            $nbt->setString('is_created', uniqid());
            $nbt->setString('item', '');
            $nbt->setInt('count', 0);
        }
    }

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $item = $this->getItemInStock();


        $count = $this->getCountCustom();
        $itemSac = $this->getItemInStock();


        $inventory = new ChestInventory();
        $inventory->setName("BACKPACK_FARM");

        if (!$this->getItemInStock()->isNull()) $inventory->setItem(0, $itemSac->setCustomName("§6Quantité §f: §6" . $count));


        $correctSlot = $player->getInventory()->getHeldItemIndex();


        $inventory->setClickCallback(function (Player $player, Inventory $inventoryEvent, Item $target, Item $source, int $slot) use ($inventory, $count, $itemSac, $correctSlot): void {

            if (!in_array($slot, [0])) {
                $inventory->transacCancel();
                return;
            }

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


            if (in_array($source->getTypeId(), $ids) && $this->getItemInStock()->isNull() && $slot === 0) {
                $this->setupItem($source);
                $itemInHand = $player->getInventory()->getItemInHand();
                $itemCorrect = $player->getInventory()->getItem($player->getInventory()->getHeldItemIndex());
                if ($itemInHand->equals($itemCorrect)) {
                    $player->getInventory()->setItemInHand($this);
                }
                $inventory->reloadTransac();
                Utils::timeout(function () use ($source, $player): void {
                    $player->getInventory()->removeItem($source);
                }, 1);
                $player->removeCurrentWindow();
                return;
            }


            $uuid = $this->getNamedTag()->getString('is_created');
            if ($correctSlot !== $player->getInventory()->getHeldItemIndex() ||
                $player->getInventory()->getItem($correctSlot)->getNamedTag()->getString('is_created', 'null') !== $uuid
            ) {
                $player->sendMessage(Messages::message("§cVous n'avez pas le sac dans la main !"));
                $inventory->transacCancel();
                return;
            }

            if ($this->getCountCustom() <= 0) {
                $player->sendMessage(Messages::message("§cVotre sac est vide !"));
                $inventory->transacCancel();
                $player->sendErrorSound();
                return;
            }


            $form = new CustomForm("Retirer '" . $itemSac->getVanillaName() . "' de votre sac", [
                new Slider("Quantité", 1, $count),
            ], function (Player $player, CustomFormResponse $response) use ($inventory, $uuid, $itemSac, $correctSlot): void {
                $quantity = (int)$response->getSlider()->getValue();


                if ($correctSlot !== $player->getInventory()->getHeldItemIndex() ||
                    $player->getInventory()->getItem($correctSlot)->getNamedTag()->getString('is_created', 'null') !== $uuid
                ) {
                    $player->sendMessage(Messages::message("§cVous n'avez pas le sac dans la main !"));
                    $inventory->transacCancel();
                    return;
                }

                if ($player->getInventory()->canAddItem($itemSac->setCount($quantity))) {
                    $player->getInventory()->addItem($itemSac->clearCustomName());
                    $this->removeCount($quantity);
                    if ($this->getCountCustom() <= 0) {
                        $this->resetItem();
                    }
                    $player->getInventory()->setItemInHand($this);
                    $this->sendSuccessSound($player);
                    $player->sendMessage(Messages::message("Vous avez retiré §6" . $quantity . "§f '" . $itemSac->getVanillaName() . "'"));
                    $player->removeCurrentWindow();
                } else {
                    $player->sendMessage(Messages::message("§cTu n'a pas assez de place dans ton inventaire."));
                    $player->removeCurrentWindow();
                }
            });
            $inventory->transacCancel();
            $player->removeCurrentWindow();
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $form): void {
                $player->sendForm($form);
            }), 10);
        });


        $inventory->send($player);
        return parent::onClickAir($player, $directionVector, $returnedItems);
    }

    public function getItemInStock(): Item
    {
        $item = $this->getNamedTag()->getString('item');
        if ($item === '') return VanillaItems::AIR();
        return Utils::unserializeItem($item);
    }

    public function getCountCustom(): int
    {
        return $this->getNamedTag()->getInt('count', 0);
    }

    public function setupItem(Item $item): void
    {
        $item2 = clone $item;
        $this->getNamedTag()->setString('item', Utils::serilizeItem($item2->setCount(1)));
        $this->getNamedTag()->setInt('count', $item->getCount());
    }

    public function removeCount(int $count): void
    {
        $this->getNamedTag()->setInt('count', $this->getNamedTag()->getInt('count', 0) - $count);
    }

    public function resetItem(): void
    {
        $this->getNamedTag()->setString('item', '');
        $this->getNamedTag()->setInt('count', 0);
    }

    public function addItemInSac(Item $item, Player $player, int $slot): bool
    {
        $player->removeCurrentWindow();
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
        if ($this->getItemInStock()->getTypeId() === $item->getTypeId()) {
            $this->addCount($item->getCount());
            $player->getInventory()->setItem($slot, $this);
            return true;
        } elseif ($this->getItemInStock()->isNull() && in_array($item->getTypeId(), $ids)) {
            $this->setupItem($item);
            $player->getInventory()->setItem($slot, $this);
            return true;
        }
        return false;
    }

    public function addCount(int $count = 1): void
    {
        $this->getNamedTag()->setInt('count', $this->getNamedTag()->getInt('count', 0) + $count);
    }
}