<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\entities\Nexus;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;
use pocketmine\world\Position;

class CloseNexus extends Executor
{
    public function __construct(string $name = 'stop-nexus', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('nexus.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!Nexus::$isRunning) {
            $sender->sendMessage(Messages::message("§cAucun nexus en cours."));
            return;
        }

        $entity = Server::getInstance()->getWorldManager()->getDefaultWorld()->getNearestEntity(
            Nexus::getSpawnPosition(), 10, Nexus::class
        );

        if ($entity instanceof Nexus) {
            $sender->sendMessage(Messages::message("§fNexus terminé !"));
            $entity->flagForDespawn();
        }
    }
}