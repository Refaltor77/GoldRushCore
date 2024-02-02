<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Label;
use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\world\Position;

class EasterEgg extends Executor
{

    public function __construct(string $name = "easteregg", string $description = "voir ses easter eggs", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $easterEggManager = Main::getInstance()->getEasterEggManager();
        $playerEasterEgg = $easterEggManager->getPlayerEasterEggs($sender->getXuid());
        $easterEggs = $easterEggManager->getAllEasterEgg();

        $data = [];
        $data[] = new Label("§r§aVous avez trouvé §b" . count($playerEasterEgg) . " §r§aeasteregg(s) !");

        foreach ($easterEggs as $item) {
            $etat = $easterEggManager->playerHasEasterEgg($sender->getXuid(), $easterEggManager->getEasterEgg(new Position((int)$item['x'], (int)$item['y'], (int)$item['z'], $sender->getWorld()))['id']) ? "§a✔" : "§c✘";
            $data[] = new Label("§feasteregg n°" . $item['id'] + 1 . " : " . $etat);
        }

        $sender->sendForm(new CustomForm("§l§aEasterEggs",
            $data,
            function (Player $player, CustomFormResponse $response): void {

            }));
    }
}