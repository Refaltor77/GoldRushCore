<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class WarpCreate extends Executor
{
    public function __construct(string $name = 'addwarp', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
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
        if ($api->hasWarp(strval($args[0]))) {
            $sender->sendMessage(Messages::message("§cLe warp §4" . $args[0] . " §cexiste déjà sur le serveur."));
        } else {
            $api->createWarp($sender->getPosition(), strval($args[0]));
            $sender->sendMessage(Messages::message("§aLe warp §f" . $args[0] . " §aà été crée avec succée !"));
        }
    }
}