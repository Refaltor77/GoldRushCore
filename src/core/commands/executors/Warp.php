<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\tasks\Teleport;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class Warp extends Executor
{
    public function __construct(string $name = 'warp', string $description = "Se rendre a un warp", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $allWarps = Main::getInstance()->getWarpManager()->getAllWarpsForArgs();
            $msg = Messages::message("§fVoici les warps disponibles: ");
            $counts = count($allWarps);
            $i = 0;
            foreach ($allWarps as $name) {
                if (($i + 1) === $counts) {
                    $msg .= "§6" . $name . "§f\n";
                } else  $msg .= "§6" . $name . "§f,\n";
                $i++;
            }
            $sender->sendMessage($msg);
            return;
        }

        $api = Main::getInstance()->getWarpManager();
        if ($api->hasWarp(strval($args[0]))) {
            $pos = $api->getWarp(strval($args[0]));
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender, $pos), 20);
        } else $sender->sendMessage(Messages::message("§cLe warp §4" . $args[0] . " §cn'existe pas."));
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'warps', false, 'warps', Main::getInstance()->getWarpManager()->getAllWarpsForArgs());
        return parent::loadOptions($player);
    }
}