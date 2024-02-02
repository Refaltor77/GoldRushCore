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
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Liza extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'liza', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $data = Main::getInstance()->storageData->getPlayerData($sender, "quest");
        if (in_array('mary', $data)) {
            $sender->sendMessage("§6[§fMary la Fermière§6] §fJe te remercie de tes services, mais je n'ai plus rien à te demander.");
            $this->sendErrorSound($sender);
            return;
        }


        $items = [
            CustomiesItemFactory::getInstance()->get(Ids::RAISIN)->setCount(64)
        ];



        Utils::timeout(function () use ($sender, $items) : void {
            $sender->sendForm(new MenuForm("SIMPLE_QUEST_MARY", "§6Bonjour " . $sender->getName() . " ! §7Les corbeaux ont ravagé mon champ de raisins. Ils m'ont volé 64 vignes. Pouvez-vous les récupérer pour moi ?", [
                new Button("§7Récupérez 64 raisins volés par les corbeaux pour Mary la Fermière."),
                new Button("BTN_J'ai les raisins !", new Image("textures/renders/bois")),
                new Button("BTN_Partir chercher les raisins", new Image("textures/renders/steve"))
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
                            $arrayData[] = 'mary';
                            Main::getInstance()->storageData->setDataPlayer($player, "quest", $arrayData);
                            $this->sendSuccessSound($player);
                            $player->sendMessage("§6[§fMary la Fermière§6] §fMerci de tout cœur. Mes récoltes vous doivent la vie. Voici votre récompense");
                            $player->sendMessage(Messages::message("§fUn objet vient d'être ajouté à ton inventaire de récompenses ! Fais §6/reward§f."));
                            $item = CustomiesItemFactory::getInstance()->get(Ids::MONEY)->setCount(10);
                            Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                            Main::getInstance()->jobsStorage->saveUserCache($player, true);
                        } else {
                            $this->sendErrorSound($player);
                            $player->sendMessage("§6[§fMary la Fermière§6] §fTu n'as pas les raisins que je t'ai demandées...");
                        }
                        break;
                    case 2:
                        $this->sendSuccessSound($player);
                        $player->sendMessage("§6[§fMary la Fermière§6] §fMerci pour ta gentillesse, on se revoit tout à l'heure !");
                        break;
                }
            }));
        }, 10);
    }
}