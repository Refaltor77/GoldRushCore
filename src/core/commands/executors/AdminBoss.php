<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\entities\BossSouls;
use core\entities\Peste;
use core\entities\TrollBoss;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class AdminBoss extends Executor
{
    public function __construct(string $name = 'adminboss', string $description = "Faire spawn des boss", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('adminboss.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0]) && !isset($args[1])) {
            $sender->sendMessage(Messages::message("§c/adminboss <create> <ame:sylvanar:troll>"));
            return;
        }

        switch ($args[0]) {
            case 'create':
                $entity = match ($args[1]) {
                    "ame" => new BossSouls($sender->getLocation()),
                    "sylvanar" => new Peste($sender->getLocation()),
                    "troll" => new TrollBoss($sender->getLocation()),
                    default => null
                };
                if (!is_null($entity)) {
                    $entity->spawnToAll();
                }
                break;
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, "create");
        $this->addOptionEnum(1, "Faire spawn un boss", true, "Boss", [
            "ame", "sylvanar", "troll"
        ]);
        return parent::loadOptions($player);
    }
}