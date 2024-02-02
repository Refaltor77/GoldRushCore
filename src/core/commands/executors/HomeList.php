<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class HomeList extends Executor
{
    public function __construct(string $name = 'homelist', string $description = "Voir vos homes", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        Main::getInstance()->getHomeManager()->getAllHomes($sender, function (Player $player, array $homelist): void {
            $arrayQueried = [];
            foreach ($homelist as $home) {
                $arrayQueried[] = $home;
            }


            $msg = Messages::message("§fVoici vos homes: ");
            $counts = count($arrayQueried);
            $i = 0;
            foreach ($arrayQueried as $name) {
                if (($i + 1) === $counts) {
                    $msg .= "§6" . $name . "§f\n";
                } else  $msg .= "§6" . $name . "§f,\n";
                $i++;
            }
            $player->sendMessage($msg);
        });
    }
}