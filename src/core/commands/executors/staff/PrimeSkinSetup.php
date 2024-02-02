<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\commands\executors\Slapper;
use core\entities\SlapperPrime;
use core\entities\SlapperPrime2;
use core\entities\SlapperPrime3;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class PrimeSkinSetup extends Executor
{
    public function __construct(string $name = 'setup-prime', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('setup-prime.use');
    }


    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {

        if (!isset($args[0])) return;

        switch ($args[0]) {
            case "1":
                $slapper = new SlapperPrime($sender->getLocation(), $sender->getSkin());
                $slapper->spawnToAll();
                break;
            case "2":
                $slapper = new SlapperPrime2($sender->getLocation(), $sender->getSkin());
                $slapper->spawnToAll();
                break;
            case "3":
                $slapper = new SlapperPrime3($sender->getLocation(), $sender->getSkin());
                $slapper->spawnToAll();
                break;
        }
    }
}