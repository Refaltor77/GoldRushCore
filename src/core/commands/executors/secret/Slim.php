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

class Slim extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'slim', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $data = Main::getInstance()->storageData->getPlayerData($sender, "quest");
        if (in_array('slim', $data)) {
            $sender->sendMessage("§6[§fSlime le mineur§6] §fJe te remercie de tes services, mais je n'ai plus rien à te demander.");
            $this->sendErrorSound($sender);
            return;
        }


        $items = [
            CustomiesItemFactory::getInstance()->get(Ids::GOLD_NUGGET)->setCount(3)
        ];



        Utils::timeout(function () use ($sender, $items) : void {
            $sender->sendForm(new MenuForm("SIMPLE_QUEST_SLIM", "§6Bonjour " . $sender->getName() . " ! §7J'ai fait une découverte. Je crois qu'il y a de l'or dans ces terres. J'aurais besoin que vous me rapportiez 3 pépites d'or pour prouver mon instinct de chercheur d'or !", [
                new Button("§7Rapportez 3 pépites d'or à Slime le mineur afin qu'il puisse constituer une petite réserve d'or."),
                new Button("BTN_J'ai les pépites !", new Image("textures/renders/bois")),
                new Button("BTN_Partir chercher les pépites", new Image("textures/renders/steve"))
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
                            $arrayData[] = 'slim';
                            Main::getInstance()->storageData->setDataPlayer($player, "quest", $arrayData);
                            $this->sendSuccessSound($player);
                            $player->sendMessage("§6[§fSlime le mineur§6] §fAh, vous êtes un véritable chercheur d'or ! Merci, partenaire. Voici votre récompense.");
                            $player->sendMessage(Messages::message("§fUn objet vient d'être ajouté à ton inventaire de récompenses ! Fais §6/reward§f."));
                            $item = CustomiesItemFactory::getInstance()->get(Ids::MONEY)->setCount(100);
                            Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                            Main::getInstance()->jobsStorage->saveUserCache($player, true);
                        } else {
                            $this->sendErrorSound($player);
                            $player->sendMessage("§6[§fSlime le mineur§6] §fTu n'as pas les pépites que je t'ai demandées...");
                        }
                        break;
                    case 2:
                        $this->sendSuccessSound($player);
                        $player->sendMessage("§6[§fSlime le mineur§6] §fMerci pour ta gentillesse, on se revoit tout à l'heure !");
                        break;
                }
            }));
        }, 1);
    }
}