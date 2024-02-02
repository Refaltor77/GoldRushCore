<?php

namespace core\commands\executors\secret;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\world\sound\XpLevelUpSound;

class Forgeron extends Executor
{
    use SoundTrait;

    public function __construct(string $name = '1234567891_forgeron', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $item = $sender->getInventory()->getItemInHand();
        if ($item->getTypeId() === ItemTypeIds::PAPER) {
            if ($item->getNamedTag()->getString('item', 'none') !== 'none') {
                switch ($item->getNamedTag()->getString('item')) {
                    case 'voidstone':
                        $itemAdd = CustomiesItemFactory::getInstance()->get(Ids::VOIDSTONE);
                        if ($sender->getInventory()->canAddItem($itemAdd)) {
                            $sender->getInventory()->addItem($itemAdd);
                            $sender->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
                            $this->sendSuccessSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] §fVoici §6la voidstone§f, créée dans les fours les plus chauds, une vague ancestrale de magie par nos ancêtres a créé l'infini en un objet.");
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] §cTu n'a pas assez de place dans ton inventaire §4" . $sender->getName());
                        }
                        break;
                    case 'backpack_ore':
                        $itemAdd = CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_ORE);
                        if ($sender->getInventory()->canAddItem($itemAdd)) {
                            $sender->getInventory()->addItem($itemAdd);
                            $sender->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
                            $this->sendSuccessSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] §fVoici §6le sac du mineur§f, un sac ayant appartenu à un humain transformé en démon. Il séjourne toujours dans la nature... fais très attention dans la jungle.");
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] §cTu n'a pas assez de place dans ton inventaire §4" . $sender->getName());
                        }
                        break;
                    case 'backpack_farm':
                        $itemAdd = CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_FARM);
                        if ($sender->getInventory()->canAddItem($itemAdd)) {
                            $sender->getInventory()->addItem($itemAdd);
                            $sender->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
                            $this->sendSuccessSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] Voici §6le sac du fermier§f, ayant appartenu à mon père, je le vois très souvent dans mon §6miroir§f.");
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] §cTu n'a pas assez de place dans ton inventaire §4" . $sender->getName());
                        }
                        break;
                    case 'bucheron_axe':
                        $itemAdd = CustomiesItemFactory::getInstance()->get(Ids::BONE_AXE_1);
                        if ($sender->getInventory()->canAddItem($itemAdd)) {
                            $sender->getInventory()->addItem($itemAdd);
                            $sender->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
                            $this->sendSuccessSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] Voici la hache du bûcheron, elle a appartenu au démon le plus puissant que notre monde ait porté, il paraît qu'il est toujours parmi nous.");
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] §cTu n'a pas assez de place dans ton inventaire §4" . $sender->getName());
                        }
                        break;
                    case 'backpack_fossil':
                        $itemAdd = CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_FOSSIL);
                        if ($sender->getInventory()->canAddItem($itemAdd)) {
                            $sender->getInventory()->addItem($itemAdd);
                            $sender->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
                            $this->sendSuccessSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] Voici le sac de fossils, il est très utile pour éviter de s'encombrer avec une tonne de vestige.");
                        } else {
                            $this->sendErrorSound($sender);
                            $sender->sendMessage("§6[§fFORGERON§6] §cTu n'a pas assez de place dans ton inventaire §4" . $sender->getName());
                        }
                        break;
                }
                return;
            }
        }


        $buttons = [];
        $item = [];
        $money = [];

        $craft = Main::getInstance()->getCraftManager();
        if ($craft->isCraftUnlocked($sender, CustomiesItemFactory::getInstance()->get(Ids::VOIDSTONE)->getTextureString())) {
            $buttons[] = new Button('§fVoidstone - 30 000§6$', new Image('textures/items/void_stone'));
            $item[] = CustomiesItemFactory::getInstance()->get(Ids::VOIDSTONE);
            $money[] = 30000;
        }

        if ($craft->isCraftUnlocked($sender, CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_FARM)->getTextureString())) {
            $buttons[] = new Button('§fSac du fermier - 60 000§6$', new Image('textures/items/backpack_farm'));
            $item[] = CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_FARM);
            $money[] = 60000;
        }

        if ($craft->isCraftUnlocked($sender, CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_ORE)->getTextureString())) {
            $buttons[] = new Button('§fSac du mineur - 60 000§6$', new Image('textures/items/backpack_ore'));
            $item[] = CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_ORE);
            $money[] = 60000;
        }

        if ($craft->isCraftUnlocked($sender, CustomiesItemFactory::getInstance()->get(Ids::BONE_AXE_1)->getTextureString())) {
            $buttons[] = new Button('§fHache du bûcheron - 30 000§6$', new Image('textures/items/bone_axe_0'));
            $item[] = CustomiesItemFactory::getInstance()->get(Ids::BONE_AXE_1);
            $money[] = 30000;
        }

        if ($craft->isCraftUnlocked($sender, CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_FOSSIL)->getTextureString())) {
            $buttons[] = new Button("§fSac de l'archéologue - 60 000§6$", new Image('textures/items/backpack_fossil'));
            $item[] = CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_FOSSIL);
            $money[] = 60000;
        }


        if (empty($buttons))  {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§cVous n'avez rien débloqué encore."));
            return;
        }



        $sender->sendForm(new MenuForm("§6- §fShop du Forgeron §6-", "§fTu n'as pas reçu de ticket pour obtenir un objet spécial ? Pas de souci, je peux te les vendre dans ma boutique !", $buttons, function (Player $player, Button $button) use ($item, $money) : void {
            $item = $item[$button->getValue()];
            $money = $money[$button->getValue()];

            if ($player->getInventory()->canAddItem($item)) {
                Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $moneyPlayer) use ($item, $money) : void {
                    if ($moneyPlayer < $money) {
                        $this->sendErrorSound($player);
                        $player->sendMessage("§6[§fFORGERON§6] §cTu n'a pas assez d'argent !");
                        return;
                    }

                    $player->sendMessage("§6[§fFORGERON§6] §fVoici pour toi §6" . $player->getName() . " §f!");
                    $player->getInventory()->addItem($item);
                    Main::getInstance()->getEconomyManager()->removeMoney($player, $money);
                });
            } else {
                $this->sendErrorSound($player);
                $player->sendMessage("§6[§fFORGERON§6] §cTu n'a pas assez de place dans ton inventaire §4" . $player->getName());
            }
        }));
    }
}