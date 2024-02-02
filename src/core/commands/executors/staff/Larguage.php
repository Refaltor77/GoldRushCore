<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\entities\AirDrops;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Larguage extends Executor
{
    public function __construct(string $name = "larguage", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('larguage.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $pos = AirDrops::getRandomPos();
        $airdrops = new AirDrops($pos);
        $airdrops->spawnToAll();
    }
}