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
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class BackpackFossil extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    use SoundTrait;


    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Sac de l'archéologue";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('backpack_fossil', $inventory);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le sac de l'archéologue est une relique ancienne,\ntransmise de génération en génération. Il est réputé pour sa\nrobustesse et sa capacité à contenir une abondance de fossils",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);

        $this->generateNBT();
    }


    public function generateNBT(): void
    {
        $tags = $this->getNamedTag();
        if (
            $tags->getString("is_created", "null") === "null" ||
            $tags->getInt("fossil_diplodocus", 10000000000) === 10000000000 ||
            $tags->getInt("fossil_nodosaurus", 10000000000) === 10000000000 ||
            $tags->getInt("fossil_pterodactyle", 10000000000) === 10000000000 ||
            $tags->getInt("fossils", 10000000000) === 10000000000 ||
            $tags->getInt("fossil_spinosaure", 10000000000) === 10000000000 ||
            $tags->getInt("fossil_stegosaurus", 10000000000) === 10000000000 ||
            $tags->getInt("fossil_triceratops", 10000000000) === 10000000000 ||
            $tags->getInt("fossil_tyrannosaure_rex", 10000000000) === 10000000000 ||
            $tags->getInt("fossil_velociraptor", 10000000000) === 10000000000
        ) {
            $tags->setString("is_created", uniqid());

            //  set des items
            $tags->setInt("fossil_diplodocus", 0);
            $tags->setInt("fossil_nodosaurus", 0);
            $tags->setInt("fossil_pterodactyle", 0);
            $tags->setInt("fossils", 0);
            $tags->setInt("fossil_brachiosaurus", 0);
            $tags->setInt("fossil_spinosaure", 0);
            $tags->setInt("fossil_stegosaurus", 0);
            $tags->setInt("fossil_triceratops", 0);
            $tags->setInt("fossil_tyrannosaure_rex", 0);
            $tags->setInt("fossil_velociraptor", 0);
        }
    }

    public function resetSac(): void
    {
        $tags = $this->getNamedTag();
        $tags->setInt("fossil_diplodocus", 0);
        $tags->setInt("fossil_nodosaurus", 0);
        $tags->setInt("fossil_pterodactyle", 0);
        $tags->setInt("fossils", 0);
        $tags->setInt("fossil_brachiosaurus", 0);
        $tags->setInt("fossil_spinosaure", 0);
        $tags->setInt("fossil_stegosaurus", 0);
        $tags->setInt("fossil_triceratops", 0);
        $tags->setInt("fossil_tyrannosaure_rex", 0);
        $tags->setInt("fossil_velociraptor", 0);
    }


    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $correctSlot = $player->getInventory()->getHeldItemIndex();


        $this->generateNBT();

        $tags = $this->getNamedTag();
        $fossil_diplodocus = $tags->getInt("fossil_diplodocus");
        $fossil_nodosaurus = $tags->getInt("fossil_nodosaurus");
        $fossil_pterodactyle = $tags->getInt("fossil_pterodactyle");
        $fossils = $tags->getInt("fossils");
        $fossil_brachiosaurus = $tags->getInt("fossil_brachiosaurus");
        $fossil_spinosaure = $tags->getInt("fossil_spinosaure");
        $fossil_stegosaurus = $tags->getInt("fossil_stegosaurus");
        $fossil_triceratops = $tags->getInt("fossil_triceratops");
        $fossil_tyrannosaure_rex = $tags->getInt("fossil_tyrannosaure_rex");
        $fossil_velociraptor = $tags->getInt("fossil_velociraptor");


        $inventory = new ChestInventory();
        $inventory->setName("BACKPACK_FOSSIL");
        $inventory->setItem(0, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_DIPLODOCUS)->setCustomName("§6Quantité §f: §6" . $fossil_diplodocus));
        $inventory->setItem(1, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_NODOSAURUS)->setCustomName("§6Quantité §f: §6" . $fossil_nodosaurus));
        $inventory->setItem(2, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_PTERODACTYLE)->setCustomName("§6Quantité §f: §6" . $fossil_pterodactyle));
        $inventory->setItem(3, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL)->setCustomName("§6Quantité §f: §6" . $fossils));
        $inventory->setItem(4, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_BRACHIOSAURUS)->setCustomName("§6Quantité §f: §6" . $fossil_brachiosaurus));
        $inventory->setItem(5, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_SPINOSAURE)->setCustomName("§6Quantité §f: §6" . $fossil_spinosaure));
        $inventory->setItem(6, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_STEGOSAURUS)->setCustomName("§6Quantité §f: §6" . $fossil_stegosaurus));
        $inventory->setItem(7, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TRICERATOPS)->setCustomName("§6Quantité §f: §6" . $fossil_triceratops));
        $inventory->setItem(8, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TYRANNOSAURE)->setCustomName("§6Quantité §f: §6" . $fossil_tyrannosaure_rex));
        $inventory->setItem(9, CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_VELOCIRAPTOR)->setCustomName("§6Quantité §f: §6" . $fossil_velociraptor));


        $inventory->setClickCallback(function (Player $player, Inventory $inventoryEvent, Item $target, Item $source, int $slot) use ($inventory, $correctSlot): void {

            if (!in_array($slot, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11])) {
                return;
            }

            $resources = [
                "fossil_diplodocus" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_DIPLODOCUS),
                "fossil_nodosaurus" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_NODOSAURUS),
                "fossil_pterodactyle" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_PTERODACTYLE),
                "fossils" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL),
                "fossil_brachiosaurus" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_BRACHIOSAURUS),
                "fossil_spinosaure" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_SPINOSAURE),
                "fossil_stegosaurus" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_STEGOSAURUS),
                "fossil_triceratops" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TRICERATOPS),
                "fossil_tyrannosaure_rex" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TYRANNOSAURE),
                "fossil_velociraptor" => CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_VELOCIRAPTOR),
            ];


            $nameTranslate = [
                "fossil_diplodocus" => "Fossil de diplodocus",
                "fossil_nodosaurus" => "Fossil de nodosaurus",
                "fossil_pterodactyle" => "Fossil de pterodactyle",
                "fossils" => "Fossil",
                "fossil_brachiosaurus" => "Fossil de brachiosaurus",
                "fossil_spinosaure" => "Fossil de spinosaure",
                "fossil_stegosaurus" => "Fossil de stegosaurus",
                "fossil_triceratops" => "Fossil de triceratops",
                "fossil_tyrannosaure_rex" => "Fossil de tyrannosaure rex",
                "fossil_velociraptor" => "Fossil de velociraptor",
            ];


            $uuid = $this->getNamedTag()->getString('is_created');


            $arrayKeys = array_keys($resources);

            if (!isset($arrayKeys[$slot])) {
                $inventory->transacCancel();
                return;
            }

            $nameItem = $arrayKeys[$slot];
            $item = $resources[$nameItem];
            $nameUi = $nameTranslate[$nameItem];

            if ($this->getNamedTag()->getInt($nameItem) <= 0) {
                $player->sendMessage(Messages::message("§cTu n'a pas de $nameUi à récupérer"));
                $player->removeCurrentWindow();
                $this->sendErrorSound($player);
                $inventory->transacCancel();
                return;
            }
            $form = new CustomForm("Retirer du $nameUi de votre sac", [
                new Slider("Quantité", 1, $this->getNamedTag()->getInt($nameItem)),
            ], function (Player $player, CustomFormResponse $response) use ($correctSlot, $inventory, $uuid, $item, $nameItem, $nameUi): void {
                $quantity = $response->getSlider()->getValue();


                if ($correctSlot !== $player->getInventory()->getHeldItemIndex() ||
                    $player->getInventory()->getItem($correctSlot)->getNamedTag()->getString('is_created', 'null') !== $uuid
                ) {
                    $player->sendMessage(Messages::message("§cVous n'avez pas le sac dans la main !"));
                    $inventory->transacCancel();
                    return;
                }

                if ($player->getInventory()->canAddItem($item->setCount($quantity))) {
                    $player->getInventory()->addItem($item);
                    $tag = $this->getNamedTag()->setInt($nameItem, $this->getNamedTag()->getInt($nameItem) - $quantity);
                    $item = $this->setNamedTag($tag);
                    $player->getInventory()->setItemInHand($item);
                    $this->sendSuccessSound($player);
                    $player->sendMessage(Messages::message("Vous avez retiré §6" . $quantity . "§f $nameUi"));
                    $player->removeCurrentWindow();
                } else {
                    $player->sendMessage(Messages::message("§cTu n'a pas assez de place dans ton inventaire."));
                    $player->removeCurrentWindow();
                }
            });
            $player->removeCurrentWindow();
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $form): void {
                $player->sendForm($form);
            }), 10);

            $inventory->transacCancel();
        });


        $inventory->send($player);


        return parent::onClickAir($player, $directionVector, $returnedItems);
    }


    public function addItemInSac(Item $item, Player $player, int $slot): void
    {
        $player->removeCurrentWindow();
        $this->generateNBT();


        $nameTranslate = [
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_DIPLODOCUS)->getTypeId() => "fossil_diplodocus",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_NODOSAURUS)->getTypeId() => "fossil_nodosaurus",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_PTERODACTYLE)->getTypeId() => "fossil_pterodactyle",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_BRACHIOSAURUS)->getTypeId() => "fossil_brachiosaurus",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_SPINOSAURE)->getTypeId() => "fossil_spinosaure",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_STEGOSAURUS)->getTypeId() => "fossil_stegosaurus",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TRICERATOPS)->getTypeId() => "fossil_triceratops",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_TYRANNOSAURE)->getTypeId() => "fossil_tyrannosaure_rex",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL_VELOCIRAPTOR)->getTypeId() => "fossil_velociraptor",
            CustomiesItemFactory::getInstance()->get(Ids::FOSSIL)->getTypeId() => "fossils",
        ];

        $itemType = $item->getTypeId();

        if (isset($nameTranslate[$itemType])) {
            $sac = $player->getInventory()->getItem($slot);
            $translatedName = $nameTranslate[$itemType];

            if ($sac instanceof BackpackFossil) {
                $namedTag = $sac->getNamedTag();
                $currentCount = $namedTag->getInt($translatedName);
                $newCount = $currentCount + $item->getCount();
                $namedTag->setInt($translatedName, $newCount);
                $newSac = $sac->setNamedTag($namedTag);
                $player->getInventory()->setItem($slot, $newSac);
            }
        }
    }
}