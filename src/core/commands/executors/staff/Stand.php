<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\entities\cosmetics\CosmeticStand;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Stand extends Executor
{
    public function __construct(string $name = 'stand', string $description = "Ajouter un manequin", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('stand.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/stand <§4add:remove§c>"));
            return;
        }

        switch (strtolower($args[0])) {
            case 'add':
                $entity = new CosmeticStand($sender->getLocation());
                $entity->spawnToAll();
                break;
            case 'remove':
                $entityFind = $sender->getWorld()->getNearestEntity($sender->getPosition(), 10, CosmeticStand::class);
                if ($entityFind instanceof CosmeticStand) {
                    $entityFind->flagForDespawn();
                } else $sender->sendMessage(Messages::message("§cAucun cosmetic stand trouvé."));
                break;
            default:
                    $sender->sendMessage(Messages::message("§c/stand <§4add:remove§c>"));
                break;
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, "add");
        $this->addSubCommand(1, "remove");
        return parent::loadOptions($player);
    }
}