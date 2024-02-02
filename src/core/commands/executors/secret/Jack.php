<?php

namespace core\commands\executors\secret;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\entities\Slapper;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\traits\SoundTrait;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\item\enchantment\SharpnessEnchantment;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Jack extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'jack', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $data = Main::getInstance()->storageData->getPlayerData($sender, "quest");
        if (in_array('jack', $data)) {
            $sender->sendMessage("§6[§fJack§6] §fJe te remercie de tes services, mais je n'ai plus rien à te demander.");
            $this->sendErrorSound($sender);
            return;
        }


        $items = [
            CustomiesItemFactory::getInstance()->get(Ids::SPECTRAL_HELMET)
        ];


        Utils::timeout(function () use ($sender, $items) : void {
            $sender->sendForm(new MenuForm("SIMPLE_QUEST_JACK", "§6Bonjour " . $sender->getName() . " ! §7J'ai besoin que tu me rendes un petit service... Mon rêve serait de combattre la légende du vengeur des âmes, et pour cela, j'ai besoin d'un casque qui puisse me rendre invisible...", [
                new Button("§7Ramène un casque spectral pour Jack, afin qu'il puisse se faufiler dans la grotte du vengeur des âmes."),
                new Button("BTN_J'ai le casque !", new Image("textures/renders/bois")),
                new Button("BTN_Partir chercher le casque", new Image("textures/renders/steve"))
            ], function (Player $player, Button $button) use ($items) : void {
                switch ($button->getValue()) {
                    case 1:
                        $found = false;
                        foreach ($items as $item) {
                            if ($player->getInventory()->contains($item)) {
                                $found = true;
                                $player->getInventory()->removeItem($item);
                                break;
                            }
                        }


                        if ($found) {
                            $arrayData = Main::getInstance()->storageData->getPlayerData($player, "quest");
                            $arrayData[] = 'jack';
                            Main::getInstance()->storageData->setDataPlayer($player, "quest", $arrayData);
                            $this->sendSuccessSound($player);
                            $player->sendMessage("§6[§fJack§6] §fMerci beaucoup ! Je te donne 3 poudres d'or");
                            $player->sendMessage(Messages::message("§fUn objet vient d'être ajouté à ton inventaire de récompenses ! Fais §6/reward§f."));
                            $item = CustomiesItemFactory::getInstance()->get(Ids::GOLD_POWDER)->setCount(3);
                            Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                            $item = CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE)->setCount(2);
                            Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                            Main::getInstance()->jobsStorage->saveUserCache($player, true);
                        } else {
                            $this->sendErrorSound($player);
                            $player->sendMessage("§6[§fJack§6] §fTu n'as pas le casque que je t'ai demandées...");
                        }
                        break;
                    case 2:
                        $this->sendSuccessSound($player);
                        $player->sendMessage("§6[§fJack§6] §fFais attention, le casque ne se récupère pas n'importe où... je te donne les coordonnées où se trouve le casque, du moins les rumeurs disent qu'il se trouve en (§6x§f: 6989 §6z§f:-954).");
                        break;
                }
            }));
        }, 1);
    }
}