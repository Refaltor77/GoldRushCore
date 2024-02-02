<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class ClearlaggForce extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'clearlaggforce', string $description = "Forcer un clearlagg", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('clearlagg.force.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $this->sendSuccessSound($sender);
        Main::getInstance()->clearlagg->time = 10;
        $sender->sendMessage(Messages::message("§fVous venez de forcer le clearlagg."));
    }

    public function onRunConsoleCommandSender(ConsoleCommandSender $sender, string $commandLabel, array $args)
    {

        parent::onRunConsoleCommandSender($sender, $commandLabel, $args);
    }
}