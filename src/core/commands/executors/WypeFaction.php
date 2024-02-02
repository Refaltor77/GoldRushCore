<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class WypeFaction extends Executor
{
    public function __construct(string $name = "wype", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('wype.admin.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        foreach (Main::getInstance()->getFactionManager()->fastCacheFaction as $factionName => $values) {
            Main::getInstance()->getFactionManager()->fastCacheFaction[$factionName]['power'] = 0;
        }

        Main::getInstance()->getFactionManager()->saveAllDataAsync();
    }
}