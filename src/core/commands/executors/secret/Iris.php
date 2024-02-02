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
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Iris extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'iris', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $data = Main::getInstance()->storageData->getPlayerData($sender, "quest");
        if (in_array('iris', $data)) {
            $sender->sendMessage("§6[§fIris la vagabonde de Lotus§6] §fJe te remercie de tes services, mais je n'ai plus rien à te demander.");
            $this->sendErrorSound($sender);
            return;
        }


        $items = [
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_INGOT)->setCount(64),
            VanillaBlocks::SUGARCANE()->asItem()->setCount(128)
        ];



        Utils::timeout(function () use ($sender, $items) : void {
            $sender->sendForm(new MenuForm("SIMPLE_QUEST_Iris la vagabonde de Lotus", "Ho alors comme ça m'a tenu vous intrigue ! Je ne suis pas d'ici je suis originaire de la ville de Lotus au sud de la vôtre, on m'a dit que votre ville est connue pour votre mine très étrange et très intéressante. Puis-je vous demandez un service . Pouvez-vous m'apporter les matériaux nécessaires pour bien m'installer dans votre ville ?", [
                new Button("§7Rapporte 64 Lingots de cuivre et 128 Cannes à sucre pour que Iris la vagabonde de Lotus puissent s'installer ici."),
                new Button("BTN_J'ai la marchandise !", new Image("textures/renders/bois")),
                new Button("BTN_Partir chercher la marchandise", new Image("textures/renders/steve"))
            ], function (Player $player, Button $button) use ($items) : void {


                switch ($button->getValue()) {
                    case 1:
                        $found = false;
                        $foundCount = 0;
                        foreach ($items as $item) {
                            if ($player->getInventory()->contains($item)) {
                                $foundCount++;
                            }
                        }
                        if ($foundCount >= count($items)) $found = true;


                        if ($found) {
                            foreach ($items as $item) {
                                $player->getInventory()->removeItem($item);
                            }

                            $arrayData = Main::getInstance()->storageData->getPlayerData($player, "quest");
                            $arrayData[] = 'iris';
                            Main::getInstance()->storageData->setDataPlayer($player, "quest", $arrayData);
                            $this->sendSuccessSound($player);
                            $player->sendMessage("§6[§fIris la vagabonde de Lotus§6] §fTe revoila ! Merci, partenaire. Voici votre récompense.");
                            $player->sendMessage(Messages::message("§fUn objet vient d'être ajouté à ton inventaire de récompenses ! Fais §6/reward§f."));
                            $item = CustomiesItemFactory::getInstance()->get(Ids::MONEY)->setCount(20);
                            Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                            $item = CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE)->setCount(1);
                            Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                            Main::getInstance()->jobsStorage->saveUserCache($player, true);
                        } else {
                            $this->sendErrorSound($player);
                            $player->sendMessage("§6[§fIris la vagabonde de Lotus§6] §fTu n'as pas la marchandise que je t'ai demandées...");
                        }
                        break;
                    case 2:
                        $this->sendSuccessSound($player);
                        $player->sendMessage("§6[§fIris la vagabonde de Lotus§6] §fMerci pour ta gentillesse, on se revoit tout à l'heure !");
                        break;
                }
            }));
        }, 1);
    }
}