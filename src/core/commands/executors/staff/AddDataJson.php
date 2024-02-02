<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class AddDataJson extends Executor
{
    public function __construct(string $name = 'adddatajson', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        //
    }

    public function onRunConsoleCommandSender(ConsoleCommandSender $sender, string $commandLabel, array $args)
    {
        $data = json_decode($args[1], true);
        Main::getInstance()->getTebexManager()->addData($args[0], $data);
        (new LogEvent("Purshace tebex : " . $args[0] . " | data : " . $args[1], "TEBEX"));
    }
}