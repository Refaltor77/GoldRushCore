<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\forms\TopForms;
use core\Main;
use core\managers\stats\StatsManager;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class TopJobs extends Executor
{
    public function __construct(string $name = "topjobs", string $description = "Voir le classement des joueurs les plus avancés dans les métiers", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        TopForms::sendTopJobs($sender);
    }
}