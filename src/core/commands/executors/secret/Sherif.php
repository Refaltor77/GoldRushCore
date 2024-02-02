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
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Sherif extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'sherif', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $data = Main::getInstance()->storageData->getPlayerData($sender, "quest");
        if (in_array('sherif', $data)) {
            $sender->sendMessage("§6[§fsherif§6] §fJe te remercie de tes services, mais je n'ai plus rien à te demander.");
            $this->sendErrorSound($sender);
            return;
        }


        $items = [
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE)->setCount(64)
        ];

        $sender->sendForm(new MenuForm("SIMPLE_QUEST_SHERIF", "§6Bonjour " . $sender->getName() . " ! §7J'ai besoin d'un petit service... Avec l'équipe d'archéologues qui étudie les fossiles, nous avons découvert une grotte avec des reliques préhistoriques, le souci, c'est qu'elle est condamnée.", [
            new Button("§7Rapporte 64 dynamites en émeraude pour que le shérif Tucker puisse faire exploser la grotte où se trouvent les reliques préhistoriques."),
            new Button("BTN_J'ai les dynamites !", new Image("textures/renders/bois")),
            new Button("BTN_Partir chercher les dynamites", new Image("textures/renders/steve"))
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
                        $arrayData[] = 'sherif';
                        Main::getInstance()->storageData->setDataPlayer($player, "quest", $arrayData);
                        $this->sendSuccessSound($player);
                        $player->sendMessage("§6[§fShérif Tucker§6] §fMerci beaucoup ! Je te donne 10 000$ et une clé rare");
                        $player->sendMessage(Messages::message("§fUn objet vient d'être ajouté à ton inventaire de récompenses ! Fais §6/reward§f."));
                        $item = CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE)->setCount(1);
                        Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                        $item = CustomiesItemFactory::getInstance()->get(Ids::MONEY)->setCount(10);
                        Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                        Main::getInstance()->jobsStorage->saveUserCache($player, true);
                    } else {
                        $this->sendErrorSound($player);
                        $player->sendMessage("§6[§fShérif Tucker§6] §fTu n'as pas les dynamites que je t'ai demandées...");
                    }
                    break;
                case 2:
                    $this->sendSuccessSound($player);
                    $player->sendMessage("§6[§fShérif Tucker§6] §fAllons faire avancer la science ensemble !");
                    break;
            }
        }));
    }
}