<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use core\tasks\Teleport;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;
use pocketmine\world\Position;

class Minage extends Executor
{
    public function __construct(string $name = 'minage', string $description = "Se rendre au minage", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {


        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($sender, new Position(
            0, 150, 0, Server::getInstance()->getWorldManager()->getWorldByName('world')
        ), null, true), 20);
    }
}