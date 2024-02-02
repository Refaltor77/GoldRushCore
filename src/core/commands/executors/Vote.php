<?php

namespace core\commands\executors;

use core\async\VoteAsync;
use core\commands\Executor;
use core\Main;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class Vote extends Executor
{
    public function __construct(string $name = 'vote', string $description = "Voter pour GoldRush", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(new VoteAsync($sender->getName()));
    }
}