<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\entities\Nexus;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class StartNexus extends Executor
{
    public function __construct(string $name = 'start-nexus', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('nexus.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (Nexus::$isRunning) {
            $sender->sendMessage(Messages::message("§cUn nexus est déjà en cours de jeu."));
            return;
        }

        $nexus = new Nexus(Nexus::getSpawnPosition());
        $nexus->spawnToAll();
    }
}