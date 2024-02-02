<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use core\tasks\Teleport;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;
use pocketmine\world\Position;

class Spawn extends Executor
{
    public function __construct(string $name = 'spawn', string $description = "Se rendre au spawn", ?string $usageMessage = null, array $aliases = [
        'hub'
    ], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $pos = new Position(7, 137, 90, Server::getInstance()->getWorldManager()->getDefaultWorld());
        $task = new Teleport($sender, $pos);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, 20);
    }
}