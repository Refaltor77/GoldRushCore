<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class ShowStatus extends Executor
{
    public function __construct(string $name = 'showstatus', string $description = "Voir l'usage du serveur (cpu, ticks..)", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('showstatus.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if ($sender->showStatus) {
            $sender->showStatus = false;
            $sender->sendSuccessSound();
            $sender->sendMessage(Messages::message("§fStatus désactivé."));
        } else {
            $sender->showStatus = true;
            $sender->sendSuccessSound();
            $sender->sendMessage(Messages::message("§fStatus activé."));
        }
    }
}