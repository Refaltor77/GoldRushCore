<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class RemoveWarp extends Executor
{
    public function __construct(string $name = 'removewarp', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("warp.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§cVous devez définir un nom."));
            return;
        }

        $api = Main::getInstance()->getWarpManager();
        if (!$api->hasWarp(strval($args[0]))) {
            $sender->sendMessage(Messages::message("§cLe warp §4" . $args[0] . " §cn'existe pas sur le serveur."));
        } else {
            $api->deleteWarp(strval($args[0]));
            $sender->sendMessage(Messages::message("§aLe warp §f" . $args[0] . " §aà été delete avec succée !"));
        }
    }



    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'warps', false, 'warps', Main::getInstance()->getWarpManager()->getAllWarpsForArgs());
        return parent::loadOptions($player);
    }
}