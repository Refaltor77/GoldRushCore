<?php

namespace core\commands\executors\secret;

use core\commands\Executor;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;
use pocketmine\world\Position;

class Tuto extends Executor
{
    public function __construct(string $name = 'tuto_1234', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $sender->teleport(new Position(259, 55, 319, Server::getInstance()->getWorldManager()->getWorldByName('demo')));
    }
}