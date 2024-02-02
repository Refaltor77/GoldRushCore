<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\tasks\ClearlaggTask;
use core\traits\SoundTrait;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class ClearlaggTime extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'clearlagtime', string $description = "Savoir le prochain clearlagg", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $this->sendSuccessSound($sender);
        $time = ClearlaggTask::$timee;
        $sender->sendMessage(Messages::message("§fProchain clearlagg dans : " . $time . " seconde§6(§fs§6)"));
    }
}