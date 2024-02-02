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

class Richard extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'richard', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $data = Main::getInstance()->storageData->getPlayerData($sender, "quest");
        if (in_array('richard', $data)) {
            $sender->sendMessage("§6[§fRichard§6] §fJe te remercie de tes services, mais je n'ai plus rien à te demander.");
            $this->sendErrorSound($sender);
            return;
        }

        $items = [
            VanillaBlocks::ACACIA_LOG()->asItem()->setCount(64),
            VanillaBlocks::BIRCH_LOG()->asItem()->setCount(64),
            VanillaBlocks::CHERRY_LOG()->asItem()->setCount(64),
            VanillaBlocks::DARK_OAK_LOG()->asItem()->setCount(64),
            VanillaBlocks::JUNGLE_LOG()->asItem()->setCount(64),
            VanillaBlocks::MANGROVE_LOG()->asItem()->setCount(64),
            VanillaBlocks::OAK_LOG()->asItem()->setCount(64),
            VanillaBlocks::SPRUCE_LOG()->asItem()->setCount(64),
        ];

        $sender->sendForm(new MenuForm("SIMPLE_QUEST_Monsieur Richard", "§6Bonjour " . $sender->getName() . " ! §7J'ai besoin que tu me rendes un petit service... Ramène-moi 64 bûches de bois pour construire ma cabane, je te serais très reconnaissant !", [
            new Button("§7Ramène 64 bûches de bois à Monsieur Richard pour qu'il puisse construire sa petite cabane !"),
            new Button("BTN_J'ai les items !", new Image("textures/renders/bois")),
            new Button("BTN_Partir chercher les items", new Image("textures/renders/steve"))
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
                        $arrayData[] = 'richard';
                        Main::getInstance()->storageData->setDataPlayer($player, "quest", $arrayData);
                        $this->sendSuccessSound($player);
                        $player->sendMessage("§6[§fRichard§6] §fMerci beaucoup ! Je te donne une clé rare !");
                        $player->sendMessage(Messages::message("§fUn objet vient d'être ajouté à ton inventaire de récompenses ! Fais §6/reward§f."));
                        $item = CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE);
                        Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                        Main::getInstance()->jobsStorage->saveUserCache($player, true);
                    } else {
                        $this->sendErrorSound($player);
                        $player->sendMessage("§6[§fRichard§6] §fTu n'as pas les bûches que je t'ai demandées...");
                    }
                    break;
                case 2:
                    $this->sendSuccessSound($player);
                    $player->sendMessage("§6[§fRichard§6] §fFais attention à ne pas te planter une écharde lors de ta découpe !");
                    break;
            }
        }));
    }
}