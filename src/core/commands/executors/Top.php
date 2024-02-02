<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\tasks\Teleport;
use core\traits\SoundTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\world\Position;

class Top extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'top', string $description = "Remonté à la surface", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("top.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $position = $sender->getPosition();

        $y = $sender->getWorld()->getHighestBlockAt($position->getFloorX(), $position->getFloorZ());
        if (is_null($y)) {
            $this->sendErrorSound($sender);
            $sender->sendMessage(Messages::message("§cAucun block n'est au dessus de vous."));
            return;
        }


        $newPosition = new Position(
            $position->getX(),
            $y,
            $position->getZ(),
            $position->getWorld()
        );

        $teleport = new Teleport($sender, $newPosition);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask($teleport, 20);
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}