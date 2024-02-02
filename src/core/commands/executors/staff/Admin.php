<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class Admin extends Executor
{
    public function __construct(string $name = 'admin', string $description = "Passer en mode admin", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('admin.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if ($sender->isOp()) {
            if ($sender->hasPlayer) {
                $sender->hasPlayer = false;
                $sender->sendMessage(Messages::message("§fVous êtes passer en mode admin, les cooldown sont désormais retiré."));
            } else {
                $sender->hasPlayer = true;
                $sender->sendMessage(Messages::message("§fVous êtes passer en mode joueur, les cooldown sont désormais activé."));
            }
        }
    }
}