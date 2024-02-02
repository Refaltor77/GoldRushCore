<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class Banlist extends Executor
{
    public function __construct(string $name = "banlistg", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('banlist.use');
    }


    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $bans = Main::getInstance()->getSanctionManager()->getAllNameBanForArgsNoAssoc();

        $msg = "§6Liste des joueurs bans §f: ";
        foreach ($bans as $index => $listName) {
            $msg .= "§c" . $listName . " §f| ";
        }

        $sender->sendMessage($msg);
    }
}