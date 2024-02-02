<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class EntityDemo extends Executor
{
    public function __construct(string $name = 'entity_demo', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('tuto_entity.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("/entity_demo <entityName>"));
            return;
        }
    }
}