<?php

namespace core\commands\executors\staff;

use core\async\Async;
use core\async\RequestAsync;
use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\services\Query;
use core\sql\SQL;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\utils\Internet;

class SeeMinage extends Executor
{
    public function __construct(string $name = 'seeminage', string $description = "Voir le nombre de joueurs dans le minage", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('seeminage.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {

    }
}