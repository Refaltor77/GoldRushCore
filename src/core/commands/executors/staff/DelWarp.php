<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class DelWarp extends Executor
{
    public function __construct(string $name = 'delwarp', string $description = "", ?string $usageMessage = null, array $aliases = [])
    {
        $this->setPermission('warp.use');
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§cVous devez selectionner un warp."));
            return;
        }

        $api = Main::getInstance()->getWarpManager();
        if (!$api->hasWarp(strval($args[0]))) {
            $sender->sendMessage(Messages::message("§cLe warp §4" . $args[0] . " §cn'existe pas."));
        } else {
            $api->deleteWarp(strval($args[0]));
            $sender->sendMessage(Messages::message("§aLe warp §f" . $args[0] . " §aà été supprimé avec succée !"));
        }
    }


    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Warps', false, 'Warps', Main::getInstance()->getWarpManager()->getAllWarpsForArgs());
        return parent::loadOptions($player);
    }
}