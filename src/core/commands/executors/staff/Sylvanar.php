<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\entities\Peste;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Sylvanar extends Executor
{
    public function __construct(string $name = 'sylvanar', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('sylvanar.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) return;

        switch ($args[0]) {
            case 'spawn_jump':
                $boss = new Peste($sender->getLocation());
                $boss->isFloating = true;
                $boss->spawnToAll();
                break;
            case 'spawn':
                $boss = new Peste($sender->getLocation());
                $boss->spawnToAll();
                break;
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, "spawn_jump");
        $this->addSubCommand(1, "spawn");
        return parent::loadOptions($player);
    }
}