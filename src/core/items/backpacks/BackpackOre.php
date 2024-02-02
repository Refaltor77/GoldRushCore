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
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class BackpackOre extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    use SoundTrait;


    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Sac du mineur';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('backpack_ore', $inventory);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le sac du mineur est une relique ancienne,\ntransmise de génération en génération. Il est réputé pour sa\nrobustesse et sa capacité à contenir une abondance de minerais",
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
            $tags->getInt("coal", 10000000) === 10000000 ||
            $tags->getInt("iron", 10000000) === 10000000 ||
            $tags->getInt("diamond", 10000000) === 10000000 ||
            $tags->getInt("redstone", 10000000) === 10000000 ||
            $tags->getInt("lapis", 10000000) === 10000000 ||
            $tags->getInt("copper", 10000000) === 10000000 ||
            $tags->getInt("emerald", 10000000) === 10000000 ||
            $tags->getInt("amethyst", 10000000) === 10000000 ||
            $tags->getInt("platine", 10000000) === 10000000 ||
            $tags->getInt("gold", 10000000) === 10000000 ||
            $tags->getInt("sulfur", 10000000) === 10000000 ||
            $tags->getInt("lapis", 10000000) === 10000000
        ) {
            $tags->setString("is_created", uniqid());

            //  set des items
            $tags->setInt("coal", 0);
            $tags->setInt("iron", 0);
            $tags->setInt("diamond", 0);
            $tags->setInt("redstone", 0);
            $tags->setInt("lapis", 0);
            $tags->setInt("copper", 0);
            $tags->setInt("emerald", 0);
            $tags->setInt("amethyst", 0);
            $tags->setInt("platine", 0);
            $tags->setInt("gold", 0);
            $tags->setInt("sulfur", 0);
            $tags->setInt("lapis", 0);
        }
    }

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $correctSlot = $player->getInventory()->getHeldItemIndex();


        $this->generateNBT();
        $tags = $this->getNamedTag();
        $coal = $tags->getInt("coal");
        $iron = $tags->getInt("iron");
        $diamond = $tags->getInt("diamond");
        $redstone = $tags->getInt("redstone");
        $copper = $tags->getInt("copper");
        $emerald = $tags->getInt("emerald");
        $amethyst = $tags->getInt("amethyst");
        $platine = $tags->getInt("platine");
        $gold = $tags->getInt("gold");
        $sulfur = $tags->getInt("sulfur");
        $lapis = $tags->getInt("lapis");


        $inventory = new ChestInventory();
        $inventory->setName("BACKPACK_ORE");
        $inventory->setItem(0, VanillaItems::COAL()->setCustomName("§6Quantité §f: §6" . $coal));
        $inventory->setItem(1, VanillaItems::RAW_IRON()->setCustomName("§6Quantité §f: §6" . $iron));
        $inventory->setItem(2, VanillaItems::DIAMOND()->setCustomName("§6Quantité §f: §6" . $diamond));
        $inventory->setItem(3, VanillaItems::REDSTONE_DUST()->setCustomName("§6Quantité §f: §6" . $redstone));
        $inventory->setItem(4, CustomiesItemFactory::getInstance()->get(Ids::COPPER_RAW)->setCustomName("§6Quantité §f: §6" . $copper));
        $inventory->setItem(5, CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)->setCustomName("§6Quantité §f: §6" . $emerald));
        $inventory->setItem(6, CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)->setCustomName("§6Quantité §f: §6" . $amethyst));
        $inventory->setItem(7, CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_RAW)->setCustomName("§6Quantité §f: §6" . $platine));
        $inventory->setItem(8, CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER)->setCustomName("§6Quantité §f: §6" . $gold));
        $inventory->setItem(9, CustomiesItemFactory::getInstance()->get(Ids::SULFUR_POWDER)->setCustomName("§6Quantité §f: §6" . $sulfur));
        $inventory->setItem(10, VanillaItems::LAPIS_LAZULI()->setCustomName("§6Quantité §f: §6" . $lapis));


        $inventory->setClickCallback(function (Player $player, Inventory $inventoryEvent, Item $target, Item $source, int $slot) use ($inventory, $correctSlot): void {

            $inventory->transacCancel();
            if (!in_array($slot, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
                return;
            }

            $resources = [
                "coal" => VanillaItems::COAL(),
                "iron" => VanillaItems::RAW_IRON(),
                "diamond" => VanillaItems::DIAMOND(),
                "redstone" => VanillaItems::REDSTONE_DUST(),
                "copper" => CustomiesItemFactory::getInstance()->get(Ids::COPPER_RAW),
                "emerald" => CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT),
                "amethyst" => CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT),
                "platine" => CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_RAW),
                "gold" => CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER),
                "sulfur" => CustomiesItemFactory::getInstance()->get(Ids::SULFUR_POWDER),
                "lapis" => VanillaItems::LAPIS_LAZULI(),
            ];


            $nameTranslate = [
                "coal" => "charbon(s)",
                "iron" => "fer(s)",
                "diamond" => "diamant(s)",
                "redstone" => "redstone(s)",
                "copper" => "cuivre(s)",
                "emerald" => "émeraude(s)",
                "amethyst" => "améthyste(s)",
                "platine" => "platine(s)",
                "gold" => "or",
                "sulfur" => "souffre(s)",
                "lapis" => "lapis"
            ];


            $uuid = $this->getNamedTag()->getString('is_created');


            $arrayKeys = array_keys($resources);
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

                if ($player->getInventory()->canAddItem($item->setCount((int)$quantity))) {
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
            "coal" => "charbon(s)",
            "iron" => "fer(s)",
            "diamond" => "diamant(s)",
            "redstone" => "redstone(s)",
            "copper" => "cuivre(s)",
            "emerald" => "émeraude(s)",
            "amethyst" => "améthyste(s)",
            "platine" => "platine(s)",
            "gold" => "or",
            "sulfur" => "souffre(s)",
            "lapis" => "lapis"
        ];


        switch ($item->getTypeId()) {
            case ItemTypeIds::LAPIS_LAZULI:
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("lapis",
                            $sac->getNamedTag()->getInt("lapis") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case ItemTypeIds::COAL:
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("coal",
                            $sac->getNamedTag()->getInt("coal") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case ItemTypeIds::RAW_IRON:
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("iron",
                            $sac->getNamedTag()->getInt("iron") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case ItemTypeIds::DIAMOND:
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("diamond",
                            $sac->getNamedTag()->getInt("diamond") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case ItemTypeIds::REDSTONE_DUST:
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("redstone",
                            $sac->getNamedTag()->getInt("redstone") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case CustomiesItemFactory::getInstance()->get(Ids::COPPER_RAW)->getTypeId():
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("copper",
                            $sac->getNamedTag()->getInt("copper") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT)->getTypeId():
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("emerald",
                            $sac->getNamedTag()->getInt("emerald") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_INGOT)->getTypeId():
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("amethyst",
                            $sac->getNamedTag()->getInt("amethyst") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_RAW)->getTypeId():
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("platine",
                            $sac->getNamedTag()->getInt("platine") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER)->getTypeId():
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("gold",
                            $sac->getNamedTag()->getInt("gold") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
            case CustomiesItemFactory::getInstance()->get(Ids::SULFUR_POWDER)->getTypeId():
                $sac = $player->getInventory()->getItem($slot);
                if ($sac instanceof BackpackOre) {
                    $newSac = $sac->setNamedTag(
                        $sac->getNamedTag()->setInt("sulfur",
                            $sac->getNamedTag()->getInt("sulfur") + $item->getCount()));
                    $player->getInventory()->setItem($slot, $newSac);
                }
                break;
        }
    }

}