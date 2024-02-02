<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\tasks\SeeChunkTask;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class SeeChunk extends Executor
{
    public function __construct(string $name = 'seechunk', string $description = "Voir les chunks", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (isset(SeeChunkTask::$seeChunk[$sender->getXuid()])) {
            unset(SeeChunkTask::$seeChunk[$sender->getXuid()]);
            $sender->sendMessage(Messages::message("vous ne voyez desormait plus les chunks"));
        } else {
            SeeChunkTask::$seeChunk[$sender->getXuid()] = $sender;
            $sender->sendMessage(Messages::message("vous voyez desormait les chunks"));
        }
    }
}