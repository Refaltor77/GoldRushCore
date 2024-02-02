<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\managers\cosmetic\CosmeticManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class RemoveCosmeticWings extends Executor
{
    public function __construct(string $name = 'removecosmeticwings', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('cosmetics.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {

    }
}